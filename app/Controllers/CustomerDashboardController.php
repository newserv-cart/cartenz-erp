<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ShipmentModel;

class CustomerDashboardController extends BaseController
{
    public function index()
    {
        try {
            $customer = $this->requireCustomer();
            $customerId = (int) $customer['id'];
            $db = db_connect();

            $data = [
                'customer' => $customer,
                'totalShipments' => (new ShipmentModel())
                    ->where('customer_id', $customerId)
                    ->countAllResults(),
                'totalInvoices' => $db->tableExists('invoices')
                    ? $db->table('invoices')
                        ->where('customer_id', $customerId)
                        ->countAllResults()
                    : 0,
                'unpaidInvoices' => $db->tableExists('invoices')
                    ? $db->table('invoices')
                        ->where('customer_id', $customerId)
                        ->where('invoice_status !=', 'PAID')
                        ->countAllResults()
                    : 0,
                'totalPayments' => 0,
                'recentShipments' => (new ShipmentModel())
                    ->where('customer_id', $customerId)
                    ->orderBy('id', 'DESC')
                    ->findAll(5),
            ];

            if (
                $db->tableExists('payments')
                && $db->tableExists('invoices')
            ) {
                $data['totalPayments'] = $db
                    ->table('payments p')
                    ->join(
                        'invoices i',
                        'i.id = p.invoice_id',
                        'inner'
                    )
                    ->where('i.customer_id', $customerId)
                    ->countAllResults();
            }

            return view('customer/dashboard', $data);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('logout'))
                ->with('error', $e->getMessage());
        }
    }

    public function myShipments()
    {
        try {
            $customer = $this->requireCustomer();

            return view('customer/my_shipments', [
                'customer' => $customer,
                'shipments' => (new ShipmentModel())
                    ->where('customer_id', (int) $customer['id'])
                    ->orderBy('id', 'DESC')
                    ->findAll(),
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with('error', $e->getMessage());
        }
    }

    public function shipmentDetail(int $shipmentId)
    {
        try {
            $customer = $this->requireCustomer();
            $shipment = (new ShipmentModel())
                ->where('id', $shipmentId)
                ->where('customer_id', (int) $customer['id'])
                ->first();

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan atau bukan milik customer.'
                );
            }

            $db = db_connect();
            $tracking = $db->tableExists('shipment_tracking')
                ? $db->table('shipment_tracking')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('event_time', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];
            $packages = $db->tableExists('shipment_packages')
                ? $db->table('shipment_packages')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('package_no', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];
            $items = $db->tableExists('shipment_items')
                ? $db->table('shipment_items')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];

            return view('customer/shipment_detail', [
                'customer' => $customer,
                'shipment' => $shipment,
                'tracking' => $tracking,
                'packages' => $packages,
                'items' => $items,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/my-shipments'))
                ->with('error', $e->getMessage());
        }
    }

    private function requireCustomer(): array
    {
        $user = auth()->user();
        $userId = (int) (auth()->id() ?? 0);

        if (
            $user === null
            || $userId <= 0
            || !$user->inGroup('user')
        ) {
            throw new \RuntimeException(
                'Akses customer tidak valid.'
            );
        }

        $customer = (new CustomerModel())
            ->where('user_id', $userId)
            ->first();

        if (!$customer) {
            throw new \RuntimeException(
                'Akun belum terhubung ke profil customer.'
            );
        }

        return $customer;
    }
}
