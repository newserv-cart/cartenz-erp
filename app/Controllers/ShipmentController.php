<?php

namespace App\Controllers;
// Taruh ini di bagian paling atas, sebelum class
use App\Services\RegularRateService;
use App\Controllers\BaseController;
use App\Libraries\AwbGenerator;
use App\Models\ShipmentPackageModel;
use App\Models\ShipmentPackageItemModel;
use App\Models\ShipmentTrackingModel;
use App\Models\TrackingStatusModel;
use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use CodeIgniter\Shield\Entities\User;
use App\Models\CountryModel;
use App\Models\ServiceModel;
use App\Models\PackageTypeModel;
use App\Models\CommodityTypeModel;
use App\Libraries\RegularRate;
use App\Models\CommodityChargeRuleModel;
use App\Models\ShipmentModel;
use App\Services\ShipmentCalculationService;
use App\Services\CommodityRuleService;

class ShipmentController extends BaseController
{
    public function index()
    {
        $shipmentModel = new ShipmentModel();
        $data['shipments'] = $shipmentModel->orderBy('id', 'DESC')->findAll();
        return view('shipments/index', $data);
    }

    public function testAwb()
    {
        return AwbGenerator::generate();
    }

    public function create()
    {
        $customer = (new CustomerModel())->find(1);
        $addressModel = new CustomerAddressModel();

        // Sender Address Book
        $senderAddresses = $addressModel
            ->select('customer_addresses.*, countries.country_name')
            ->join('countries', 'countries.id = customer_addresses.country_id', 'left')
            ->where('customer_addresses.customer_id', $customer['id'])
            ->where('customer_addresses.address_type', 'SENDER')
            ->where('customer_addresses.status', 1)
            ->orderBy('customer_addresses.contact_name', 'ASC')
            ->findAll();

        // Receiver Address Book
        $receiverAddresses = $addressModel
            ->select('customer_addresses.*, countries.country_name')
            ->join('countries', 'countries.id = customer_addresses.country_id', 'left')
            ->where('customer_addresses.customer_id', $customer['id'])
            ->where('customer_addresses.address_type', 'RECEIVER')
            ->where('customer_addresses.status', 1)
            ->orderBy('customer_addresses.contact_name', 'ASC')
            ->findAll();

        $countryModel       = new CountryModel();
        $serviceModel       = new ServiceModel();
        $packageTypeModel   = new PackageTypeModel();
        $commodityModel     = new CommodityTypeModel();
        $countries = $countryModel->where('status', 1)->orderBy('country_name', 'ASC')->findAll();

        return view('shipments/create', [
            'customer'           => $customer,
            'senderAddresses'    => $senderAddresses,
            'receiverAddresses'  => $receiverAddresses,
            'countries'          => $countries,
            'services'           => $serviceModel->orderBy('service_name')->findAll(),
            'packageTypes'       => $packageTypeModel->orderBy('package_type_name')->findAll(),
            'commodityTypes'     => $commodityModel->orderBy('commodity_name')->findAll(),
        ]);
    }

    public function view($id)
    {
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();
        $trackingModel = new ShipmentTrackingModel();
        $statusModel   = new TrackingStatusModel();

        $shipment = $shipmentModel
            ->select('shipments.*, customers.customer_name, customers.company_name, customers.phone, customers.email')
            ->join('customers', 'customers.id = shipments.customer_id', 'left')
            ->where('shipments.id', $id)
            ->first();

        if (!$shipment) {
            $shipment = $shipmentModel->find($id);
        }

        if (!$shipment) {
            return redirect()->to(site_url('shipments'))->with('error', 'Shipment tidak ditemukan atau sudah dihapus');
        }

        $packages = $packageModel->where('shipment_id', $id)->findAll();
        foreach ($packages as &$package) {
            $package['items'] = $itemModel->where('shipment_package_id', $package['id'])->findAll();
        }

        $tracking = $trackingModel->where('shipment_id', $id)->orderBy('event_time', 'DESC')->findAll();
        $statuses = $statusModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();

        return view('shipments/view', [
            'shipment' => $shipment,
            'packages' => $packages,
            'tracking' => $tracking,
            'statuses' => $statuses,
        ]);
    }

    public function edit($id)
    {
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();

        $shipment = $shipmentModel->find($id);
        if (!$shipment) {
            return redirect()->to(site_url('shipments'))->with('error', 'Shipment tidak ditemukan atau sudah dihapus');
        }

        $packages = $packageModel->where('shipment_id', $id)->findAll();
        if (empty($packages)) {
            $packages = [
                0 => [
                    'id'                => '',
                    'actual_weight'     => 0,
                    'length_cm'         => 0,
                    'width_cm'          => 0,
                    'height_cm'         => 0,
                    'volume_weight'     => 0,
                    'chargeable_weight' => 0,
                    'items'             => [
                        0 => [
                            'id'               => '',
                            'item_description' => '',
                            'quantity'         => 0,
                            'unit_value'       => 0,
                            'total_value'      => 0
                        ]
                    ]
                ]
            ];
        } else {
            foreach ($packages as &$package) {
                $package['items'] = $itemModel->where('shipment_package_id', $package['id'])->findAll();
                if (empty($package['items'])) {
                    $package['items'] = [
                        0 => [
                            'id'               => '',
                            'item_description' => '',
                            'quantity'         => 0,
                            'unit_value'       => 0,
                            'total_value'      => 0
                        ]
                    ];
                }
            }
        }

        return view('shipments/edit', ['shipment' => $shipment, 'packages' => $packages]);
    }

    public function update($id)
    {
        $db = db_connect();
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();

        $db->transBegin();
        try {
            $shipment = $shipmentModel->find($id);
            if (!$shipment) throw new \Exception('Shipment tidak ditemukan');

            $shipmentModel->update($id, [
                'sender_name'           => $this->request->getPost('sender_name', FILTER_SANITIZE_STRING),
                'sender_phone'          => $this->request->getPost('sender_phone', FILTER_SANITIZE_STRING),
                'sender_city'           => $this->request->getPost('sender_city', FILTER_SANITIZE_STRING),
                'sender_postal_code'    => $this->request->getPost('sender_postal_code', FILTER_SANITIZE_STRING),
                'sender_address'        => $this->request->getPost('sender_address', FILTER_SANITIZE_STRING),
                'receiver_type'         => $this->request->getPost('receiver_type', FILTER_SANITIZE_STRING),
                'receiver_name'         => $this->request->getPost('receiver_name', FILTER_SANITIZE_STRING),
                'receiver_company'      => $this->request->getPost('receiver_company', FILTER_SANITIZE_STRING),
                'receiver_phone'        => $this->request->getPost('receiver_phone', FILTER_SANITIZE_STRING),
                'receiver_email'        => $this->request->getPost('receiver_email', FILTER_SANITIZE_EMAIL),
                'receiver_city'         => $this->request->getPost('receiver_city', FILTER_SANITIZE_STRING),
                'receiver_postal_code'  => $this->request->getPost('receiver_postal_code', FILTER_SANITIZE_STRING),
                'receiver_id_number'    => $this->request->getPost('receiver_id_number', FILTER_SANITIZE_STRING),
                'receiver_address'      => $this->request->getPost('receiver_address', FILTER_SANITIZE_STRING),
                'total_colly'           => $this->request->getPost('total_colly', FILTER_SANITIZE_NUMBER_INT),
                'actual_weight'         => $this->request->getPost('total_weight', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'chargeable_weight'     => $this->request->getPost('total_chargeable', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
                'declared_value'        => $this->request->getPost('total_value', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            ]);

            $packages = $this->request->getPost('packages') ?? [];
            foreach ($packages as $package) {
                if (!empty($package['id'])) {
                    $packageModel->update($package['id'], [
                        'actual_weight'     => $package['actual_weight'] ?? 0,
                        'length_cm'         => $package['length_cm'] ?? 0,
                        'width_cm'          => $package['width_cm'] ?? 0,
                        'height_cm'         => $package['height_cm'] ?? 0,
                        'volume_weight'     => $package['volume_weight'] ?? 0,
                        'chargeable_weight' => $package['chargeable_weight'] ?? 0,
                    ]);
                    $packageId = $package['id'];
                } else {
                    $packageId = $packageModel->insert([
                        'shipment_id'       => $id,
                        'actual_weight'     => $package['actual_weight'] ?? 0,
                        'length_cm'         => $package['length_cm'] ?? 0,
                        'width_cm'          => $package['width_cm'] ?? 0,
                        'height_cm'         => $package['height_cm'] ?? 0,
                        'volume_weight'     => $package['volume_weight'] ?? 0,
                        'chargeable_weight' => $package['chargeable_weight'] ?? 0,
                    ]);
                }

                if (!empty($package['items'])) {
                    foreach ($package['items'] as $item) {
                        if (!empty($item['id'])) {
                            $itemModel->update($item['id'], [
                                'item_description' => $item['item_description'] ?? '',
                                'quantity'         => $item['quantity'] ?? 0,
                                'unit_value'       => $item['unit_value'] ?? 0,
                                'total_value'      => $item['total_value'] ?? 0,
                            ]);
                        } else {
                            $itemModel->insert([
                                'shipment_package_id' => $packageId,
                                'item_description'     => $item['item_description'] ?? '',
                                'quantity'             => $item['quantity'] ?? 0,
                                'unit_value'           => $item['unit_value'] ?? 0,
                                'total_value'          => $item['total_value'] ?? 0,
                            ]);
                        }
                    }
                }
            }

            $db->transCommit();
            return redirect()->to(site_url('shipments'))->with('success', 'Shipment berhasil diperbarui');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $db = db_connect();
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();
        $trackingModel = new ShipmentTrackingModel();

        $db->transBegin();
        try {
            $packages = $packageModel->where('shipment_id', $id)->findAll();
            foreach ($packages as $package) {
                $itemModel->where('shipment_package_id', $package['id'])->delete();
            }
            $packageModel->where('shipment_id', $id)->delete();
            $trackingModel->where('shipment_id', $id)->delete();
            $shipmentModel->delete($id);

            $db->transCommit();
            return redirect()->to('/shipments')->with('success', 'Shipment Deleted Successfully');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

    public function printAwb($id)
    {
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();

        $shipment = $shipmentModel->find($id);
        if (!$shipment) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $packages = $packageModel->where('shipment_id', $id)->findAll();
        foreach ($packages as &$package) {
            $package['items'] = $itemModel->where('shipment_package_id', $package['id'])->findAll();
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode(base_url('track/' . $shipment['awb_no']));

        $html = view('shipments/awb_pdf', ['shipment' => $shipment, 'packages' => $packages, 'qrUrl' => $qrUrl]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('AWB-' . $shipment['awb_no'] . '.pdf', ['Attachment' => false]);
    }

    public function testPdf()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml('<h1>PDF TEST CARTENZ</h1>');
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream('test.pdf', ['Attachment' => false]);
    }

    public function track($awb)
    {
        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();

        $shipment = $shipmentModel->where('awb_no', $awb)->first();
        if (!$shipment) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $tracking = $trackingModel->where('tracking_code', $awb)->orderBy('event_time', 'DESC')->findAll();
        return view('shipments/tracking', ['shipment' => $shipment, 'tracking' => $tracking]);
    }

public function store()
{
    $db = db_connect();
    $shipmentModel = new ShipmentModel();

    $db->transBegin();
    try {
        // =====================================
        // 1. Ambil data input
        // =====================================
        $post = $this->request->getPost();
        

        // =====================================
        // 2. Validasi data
        // =====================================
        $rules = [
            'customer_id'       => 'required|integer',
            'country_id'        => 'required|integer',
            'service_id'        => 'required|integer',
            'commodity_type_id' => 'required|integer',
            'total_weight'      => 'required|numeric|greater_than[0]',
            'total_chargeable'  => 'required|numeric|greater_than[0]',
            'sender_name'       => 'required|max_length[150]',
            'sender_phone'      => 'permit_empty|max_length[30]',
            'receiver_name'     => 'required|max_length[150]',
            'receiver_phone'    => 'permit_empty|max_length[30]',
        ];

        if (!$this->validate($rules)) {
            throw new \Exception('Validasi gagal: ' . implode(', ', $this->validator->getErrors()));
        }

        // Validasi email terpisah
        $receiverEmail = trim($post['receiver_email'] ?? '');
        if (!empty($receiverEmail) && !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Alamat email penerima tidak valid.');
        }

        // Cek apakah ada input paket
        if (empty($post['packages'])) {
            throw new \Exception('Data paket belum diinput.');
        }

        $customerId = (int) $post['customer_id'];

        // =====================================
        // 3. Generate AWB setelah validasi
        // =====================================
        // Nanti bisa diubah jika AwbGenerator dikembangkan
        $awbNo = \App\Libraries\AwbGenerator::generate();

        // =====================================
        // 4. Hitung pricing
        // =====================================
        $calculator = new \App\Services\ShipmentCalculationService();
        $pricing = $calculator->calculate($post);

        // =====================================
        // 5. Siapkan data shipment
        // =====================================
        $chargeWeight = max(
            (float) $post['total_weight'],
            (float) $post['total_chargeable']
        );

        $data = $post;

        $data['awb_no']              = $awbNo;
        $data['customer_id']         = $customerId;
        $data['chargeable_weight']   = $chargeWeight;
        $data['shipment_status']     = 'DRAFT';

        // Data dari hasil perhitungan tarif
        $data['selling_amount']      = $pricing['freight']        ?? 0;
        $data['insurance_amount']    = $pricing['insurance']      ?? 0;
        $data['packing_charge']      = $pricing['packing']        ?? 0;
        $data['additional_charge']   = $pricing['additional']     ?? 0;
        $data['discount_amount']     = $pricing['discount']       ?? 0;
        $data['total_charge']        = $pricing['customer_total'] ?? 0;
        $data['vendor_cost']         = $pricing['vendor_total']   ?? 0;
        $data['vendor_total']        = $pricing['vendor_total']   ?? 0;
        $data['profit_amount']       = $pricing['profit']         ?? 0;
        $data['margin_percent']      = $pricing['margin']         ?? 0;
        $data['markup_percent']      = $pricing['markup']         ?? 0;

        // Bersihkan input
        $data['sender_name']         = trim(strip_tags($post['sender_name'] ?? ''));
        $data['sender_phone']        = trim(strip_tags($post['sender_phone'] ?? ''));
        $data['sender_address']      = trim(strip_tags($post['sender_address'] ?? ''));
        $data['receiver_name']       = trim(strip_tags($post['receiver_name'] ?? ''));
        $data['receiver_phone']      = trim(strip_tags($post['receiver_phone'] ?? ''));
        $data['receiver_email']      = $receiverEmail;
        $data['receiver_address']    = trim(strip_tags($post['receiver_address'] ?? ''));

        // =====================================
        // 6. Simpan data utama
        // =====================================
        $shipmentModel->insert($data);
        $shipmentId = $shipmentModel->getInsertID();

        // =====================================
        // 7. Simpan rincian biaya
        // =====================================
        $this->saveCostBreakdown($shipmentId, 'CUSTOMER', $pricing['pricing_breakdown']);
        $this->saveCostBreakdown($shipmentId, 'VENDOR',   $pricing['vendor_breakdown']);

        // =====================================
        // 8. Simpan paket
        // =====================================
        $this->savePackages($shipmentId, $post['packages']);

        // =====================================
        // 9. Simpan tracking awal
        // =====================================
        $this->createTracking($shipmentId, $awbNo);

        // =====================================
        // Selesai
        // =====================================
        $db->transCommit();
        return redirect()->to('/shipments')->with('success', 'Shipment berhasil dibuat: ' . $awbNo);

    } catch (\Throwable $e) {
        $db->transRollback();
        return redirect()->back()->withInput()->with('error', 'Gagal membuat pengiriman: ' . $e->getMessage());
    }
}



private function createTracking(int $shipmentId, string $awbNo): void
{
    $trackingModel = new \App\Models\ShipmentTrackingModel();

    $trackingModel->insert([
        'shipment_id'       => $shipmentId,
        'tracking_code'     => $awbNo,
        'tracking_status'   => 'CREATED',
        'tracking_location' => 'System',
        'tracking_notes'    => 'Shipment berhasil dibuat',
        'event_time'        => date('Y-m-d H:i:s'),
        'event_type'        => 'SYSTEM',
        'created_by'        => auth()->id() ?? null
    ]);
}
    public function addTracking($shipmentId)
    {
        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();
        $statusModel   = new TrackingStatusModel();

        $shipment = $shipmentModel->find($shipmentId);
        if (!$shipment) return redirect()->back()->with('error', 'Shipment not found');

        $status = $this->request->getPost('tracking_status');
        $validStatuses = array_column($statusModel->where('is_active', 1)->findAll(), 'tracking_status');
        if (!in_array($status, $validStatuses)) return redirect()->back()->with('error', 'Invalid status selected');

        $trackingModel->insert([
            'shipment_id'       => $shipmentId,
            'tracking_code'     => $shipment['awb_no'],
            'tracking_status'   => $status,
            'tracking_location' => $this->request->getPost('tracking_location') ?? '',
            'tracking_notes'    => $this->request->getPost('tracking_notes') ?? '',
            'event_time'        => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $shipmentModel->update($shipmentId, ['shipment_status' => $status]);
        return redirect()->back()->with('success', 'Tracking Updated');
    }

    public function quickStatus($shipmentId, $status)
    {
        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();
        $statusModel   = new TrackingStatusModel();

        $shipment = $shipmentModel->find($shipmentId);
        if (!$shipment) return redirect()->to(site_url('shipments'))->with('error', 'Shipment not found');

        $validStatuses = array_column($statusModel->where('is_active', 1)->findAll(), 'tracking_status');
        if (!in_array($status, $validStatuses)) return redirect()->back()->with('error', 'Invalid status');

        $lastTracking = $trackingModel->where('shipment_id', $shipmentId)->orderBy('event_time', 'DESC')->first();
        if ($lastTracking && $lastTracking['tracking_status'] === $status) return redirect()->back()->with('error', 'Status sudah sama');

        $trackingModel->insert([
            'shipment_id'       => $shipmentId,
            'tracking_code'     => $shipment['awb_no'],
            'tracking_status'   => $status,
            'tracking_location' => 'Cartenz System',
            'tracking_notes'    => 'Status updated to: ' . $status,
            'event_time'        => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $shipmentModel->update($shipmentId, ['shipment_status' => $status]);
        return redirect()->to('/shipments/view/' . $shipmentId)->with('success', 'Status updated to: ' . $status);
    }

    public function printLabel($id)
    {
        $shipment = (new \App\Models\ShipmentModel())->find($id);
        if (!$shipment) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('shipments/label', ['shipment' => $shipment]);
    }

    public function label($id)
    {
        $shipment = (new \App\Models\ShipmentModel())->find($id);
        return view('shipments/label', ['shipment' => $shipment]);
    }

    public function testRate()
    {
        return $this->response->setJSON(['success' => true, 'price_per_kg' => 55000, 'total' => 660000]);
    }

  
    

public function getCommodityRule()
{
    $commodityId = (int)$this->request->getGet('commodity_id');

    // Panggil Service saja, nggak perlu tulis query lagi di sini
    $service = new CommodityRuleService();
    $rule = $service->getRule($commodityId);

    if (!$rule) {
        return $this->response->setJSON([
            'success' => false
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'rule' => $rule
    ]);
}







public function detail($id)
{
    $shipmentModel = new ShipmentModel();

    $shipment = $shipmentModel
        ->select('shipments.*, customers.customer_name, countries.country_name, services.service_name')
        ->join('customers', 'customers.id = shipments.customer_id', 'left')
        ->join('countries', 'countries.id = shipments.country_id', 'left')
        ->join('services', 'services.id = shipments.service_id', 'left')
        ->find($id);

    if (!$shipment) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    return view('shipments/detail', [
        'shipment' => $shipment
    ]);

}
// Taruh ini di dalam isi class-nya
public function calculateRate()
{
    $service = new RegularRateService();

    $rate = $service->calculate(
        (int)$this->request->getPost('country_id'),
        (int)$this->request->getPost('service_id'),
        (int)$this->request->getPost('transport_mode_id'),
        (float)$this->request->getPost('chargeable_weight'),
        $this->request->getPost('agent_type') ?? 'A'
    );

    if (!$rate) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Tarif tidak ditemukan.'
        ]);
    }

    return $this->response->setJSON([
        'success'       => true,
        'price_per_kg'  => $rate['price_per_kg'],
        'vendor_per_kg' => $rate['vendor_per_kg'],
        'vendor_total'  => $rate['vendor_total'],
        'total'         => $rate['total']
    ]);
}
    // ✅ DIPERBARUI SESUAI STRUKTUR SERVICE
    public function calculate()
    {
        try {
            $shipment = [
                'service_id'         => (int)$this->request->getPost('service_id'),
                'country_id'         => (int)$this->request->getPost('country_id'),
                'transport_mode_id'  => (int)$this->request->getPost('transport_mode_id'),
                'chargeable_weight'  => (float)$this->request->getPost('chargeable_weight'),
                'agent_type'         => $this->request->getPost('agent_type') ?? 'A',
                'commodity_type_id'  => (int)$this->request->getPost('commodity_type_id'),
                'insurance_amount'   => (float)$this->request->getPost('insurance_amount') ?? 0,
                'packing_charge'     => (float)$this->request->getPost('packing_charge') ?? 0,
                'additional_charge'  => (float)$this->request->getPost('additional_charge') ?? 0,
                'discount'           => (float)$this->request->getPost('discount') ?? 0,
                'receiver_city'      => $this->request->getPost('receiver_city') ?? '',
                'receiver_postcode'  => $this->request->getPost('receiver_postcode') ?? ''
            ];

            // ✅ SEKARANG PANGGIL SERVICE SAJA
            $result = (new ShipmentCalculationService())->calculate($shipment);

            return $this->response->setJSON([
                'success' => true,
                ...$result
            ]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}