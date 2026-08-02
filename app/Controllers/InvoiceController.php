<?php

namespace App\Controllers;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class InvoiceController extends BaseController
{
    public function index()
    {
        $invoices = (new \App\Models\InvoiceModel())
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('invoices/index', [
            'invoices' => $invoices
        ]);
    }

    public function create()
    {
        $customers = (new \App\Models\CustomerModel())->findAll();

        return view('invoices/create', [
            'customers' => $customers
        ]);
    }

    public function store()
    {
        $invoiceModel = new \App\Models\InvoiceModel();

        $invoiceNo = 'INV' . date('ymdHis');

        $totalAmount = (float)$this->request->getPost('total_amount');
        $vatPercent  = (float)$this->request->getPost('vat_percent');
        $extraCharge = (float)$this->request->getPost('extra_charge');

        $vatAmount = ($totalAmount * $vatPercent) / 100;
        $grandTotal = $totalAmount + $vatAmount + $extraCharge;

        $invoiceModel->insert([
            'invoice_no'      => $invoiceNo,
            'customer_id'     => $this->request->getPost('customer_id'),
            'invoice_date'    => $this->request->getPost('invoice_date'),
            'due_date'        => $this->request->getPost('due_date'),
            'total_amount'    => $totalAmount,
            'vat_percent'     => $vatPercent,
            'vat_amount'      => $vatAmount,
            'extra_charge'    => $extraCharge,
            'grand_total'     => $grandTotal,
            'paid_amount'     => 0,
            'balance_amount'  => $grandTotal,
            'invoice_status'  => 'UNPAID'
        ]);

        return redirect()->to('/invoices')->with('success', 'Invoice berhasil dibuat');
    }

    public function generate()
    {
        $db = \Config\Database::connect();
        $shipmentModel = new \App\Models\ShipmentModel();
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoiceItemModel = new \App\Models\InvoiceItemModel();

        $customerId  = $this->request->getPost('customer_id');
        $dateFrom    = $this->request->getPost('date_from');
        $dateTo      = $this->request->getPost('date_to');

        $vatPercent  = (float)$this->request->getPost('vat_percent');
        $extraCharge = (float)$this->request->getPost('extra_charge');

        $shipments = $shipmentModel
            ->where('customer_id', $customerId)
            ->where('is_invoiced', 0)
            ->where('DATE(created_at) >=', $dateFrom)
            ->where('DATE(created_at) <=', $dateTo)
            ->findAll();

        if (empty($shipments)) {
            return redirect()
    ->to(site_url('invoices/create'))
    ->withInput()
    ->with(
        'error',
        'Tidak ada shipment yang bisa ditagih'
    );
        }

        $totalAmount = array_sum(array_column($shipments, 'total_charge'));
        $vatAmount   = ($totalAmount * $vatPercent) / 100;
        $grandTotal  = $totalAmount + $vatAmount + $extraCharge;

        $invoiceNo = 'INV' . date('ymdHis');

        $db->transStart();

        $invoiceId = $invoiceModel->insert([
            'invoice_no'      => $invoiceNo,
            'customer_id'     => $customerId,
            'invoice_date'    => date('Y-m-d'),
            'due_date'        => date('Y-m-d', strtotime('+30 days')),
            'total_amount'    => $totalAmount,
            'vat_percent'     => $vatPercent,
            'vat_amount'      => $vatAmount,
            'extra_charge'    => $extraCharge,
            'grand_total'     => $grandTotal,
            'paid_amount'     => 0,
            'balance_amount'  => $grandTotal,
            'invoice_status'  => 'UNPAID'
        ], true);

        if (!$invoiceId) {
            $db->transRollback();
            return redirect()
    ->to(site_url('invoices/create'))
    ->withInput()
    ->with(
        'error',
        'Gagal membuat invoice'
    );
        }

        foreach ($shipments as $shipment) {
            $invoiceItemModel->insert([
                'invoice_id'  => $invoiceId,
                'shipment_id' => $shipment['id'],
                'amount'      => $shipment['total_charge']
            ]);

            $shipmentModel->update($shipment['id'], [
                'invoice_id'  => $invoiceId,
                'is_invoiced' => 1
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
    ->to(site_url('invoices/create'))
    ->withInput()
    ->with(
        'error',
        'Gagal membuat invoice, proses dibatalkan'
    );
        }

        return redirect()->to('/invoices')->with('success', 'Invoice berhasil dibuat');
    }

    public function show($id)
    {
        $db = \Config\Database::connect();

        $invoice = $db->table('invoices')
            ->select('
                invoices.*,
                customers.customer_name,
                customers.phone,
                customers.email,
                customers.address,
                customers.tax_number
            ')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->where('invoices.id', $id)
            ->get()
            ->getRowArray();

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Invoice tidak ditemukan');
        }

        $items = $db->table('invoice_items')
            ->select('
                invoice_items.*,
                shipments.awb_no,
                shipments.shipment_date,
                shipments.created_at AS shipment_created_at,
                shipments.actual_weight,
                shipments.chargeable_weight,
                customer_addresses.contact_name AS receiver_name,
                countries.country_name,
                package_types.package_type_name,
                COALESCE(
                    NULLIF(shipments.chargeable_weight, 0),
                    NULLIF(shipments.actual_weight, 0),
                    0
                ) AS invoice_weight
            ')
            ->join('shipments', 'shipments.id = invoice_items.shipment_id', 'left')
            ->join('customer_addresses', 'customer_addresses.id = shipments.receiver_address_id', 'left')
            ->join('countries', 'countries.id = shipments.country_id', 'left')
            ->join('package_types', 'package_types.id = shipments.package_type_id', 'left')
            ->where('invoice_items.invoice_id', $id)
            ->get()
            ->getResultArray() ?: [];

        // Ambil company_id dinamis dari invoice, default ke 1 jika kosong
        $companyId = (int) ($invoice['company_id'] ?? 0);
        if ($companyId <= 0) {
            $companyId = 1;
        }

        // Ambil data perusahaan sesuai ID
        $company = $db
            ->table('companies')
            ->where('id', $companyId)
            ->get()
            ->getRowArray() ?: [];

        // Ambil rekening bank utama perusahaan
        $bankAccount = $db
            ->table('company_bank_accounts')
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray() ?: [];

        // ✅ Ditambahkan bankAccount, variabel lain tetap dipertahankan
        return view('invoices/show', [
            'invoice'      => $invoice,
            'items'        => $items,
            'company'      => $company,
            'bankAccount'  => $bankAccount
        ]);
    }

    public function pdf($id)
    {
        helper('terbilang');
        $db = \Config\Database::connect();

        $invoice = $db->table('invoices')
            ->select('
                invoices.*,
                customers.customer_name,
                customers.phone,
                customers.address
            ')
            ->join('customers', 'customers.id = invoices.customer_id', 'left')
            ->where('invoices.id', $id)
            ->get()
            ->getRowArray();

        $items = $db->table('invoice_items')
            ->select('
                invoice_items.*,
                shipments.awb_no,
                shipments.shipment_date,
                shipments.created_at AS shipment_created_at,
                shipments.actual_weight,
                shipments.chargeable_weight,
                customer_addresses.contact_name AS receiver_name,
                countries.country_name,
                package_types.package_type_name,
                COALESCE(
                    NULLIF(shipments.chargeable_weight, 0),
                    NULLIF(shipments.actual_weight, 0),
                    0
                ) AS invoice_weight
            ')
            ->join('shipments', 'shipments.id = invoice_items.shipment_id', 'left')
            ->join('customer_addresses', 'customer_addresses.id = shipments.receiver_address_id', 'left')
            ->join('countries', 'countries.id = shipments.country_id', 'left')
            ->join('package_types', 'package_types.id = shipments.package_type_id', 'left')
            ->where('invoice_items.invoice_id', $id)
            ->get()
            ->getResultArray();

        // Ambil company_id dinamis dari invoice, default ke 1 jika kosong
        $companyId = (int) ($invoice['company_id'] ?? 0);
        if ($companyId <= 0) {
            $companyId = 1;
        }

        // Ambil data perusahaan sesuai ID
        $company = $db
            ->table('companies')
            ->where('id', $companyId)
            ->get()
            ->getRowArray() ?: [];

        // Ambil rekening bank utama perusahaan
        $bankAccount = $db
            ->table('company_bank_accounts')
            ->where('company_id', $companyId)
            ->where('status', 1)
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()
            ->getRowArray() ?: [];

        // ✅ Ditambahkan bankAccount ke tampilan PDF
        $html = view('invoices/pdf', [
            'invoice'      => $invoice,
            'items'        => $items,
            'company'      => $company,
            'bankAccount'  => $bankAccount
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Invoice-'.$invoice['invoice_no'].'.pdf', [
            'Attachment' => false
        ]);
    }
}