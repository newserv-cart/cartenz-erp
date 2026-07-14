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
use App\Services\PricingEngine;

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
        $senderAddressModel   = new CustomerAddressModel();
        $receiverAddressModel = new CustomerAddressModel();

        $addressSelect = implode(', ', [
            'customer_addresses.*',
            'countries.country_name',
            'customers.customer_name AS owner_customer_name',
            'customers.customer_code AS owner_customer_code',
        ]);

        $senderAddresses = $senderAddressModel
            ->select($addressSelect)
            ->join(
                'countries',
                'countries.id = customer_addresses.country_id',
                'left'
            )
            ->join(
                'customers',
                'customers.id = customer_addresses.customer_id',
                'inner'
            )
            ->where('customer_addresses.address_type', 'SENDER')
            ->where('customer_addresses.status', 1)
            ->where('customers.status', 1)
            ->orderBy('customers.customer_name', 'ASC')
            ->orderBy('customer_addresses.contact_name', 'ASC')
            ->findAll();

        $receiverAddresses = $receiverAddressModel
            ->select($addressSelect)
            ->join(
                'countries',
                'countries.id = customer_addresses.country_id',
                'left'
            )
            ->join(
                'customers',
                'customers.id = customer_addresses.customer_id',
                'inner'
            )
            ->where('customer_addresses.address_type', 'RECEIVER')
            ->where('customer_addresses.status', 1)
            ->where('customers.status', 1)
            ->orderBy('customer_addresses.contact_name', 'ASC')
            ->findAll();

        return view('shipments/create', [
            'senderAddresses'   => $senderAddresses,
            'receiverAddresses' => $receiverAddresses,
            'countries'         => (new CountryModel())
                ->where('status', 1)
                ->orderBy('country_name', 'ASC')
                ->findAll(),
            'services'          => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name', 'ASC')
                ->findAll(),
            'packageTypes'      => (new PackageTypeModel())
                ->where('status', 1)
                ->orderBy('package_type_name', 'ASC')
                ->findAll(),
            'commodityTypes'    => (new CommodityTypeModel())
                ->where('status', 1)
                ->orderBy('commodity_name', 'ASC')
                ->findAll(),
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
        $db            = db_connect();
        $shipmentModel = new ShipmentModel();

        $db->transBegin();

        try {
            $post = $this->request->getPost();

            $rules = [
                'customer_id'       => 'required|is_natural_no_zero',
                'sender_address_id' => 'required|is_natural_no_zero',
                'receiver_id'       => 'required|is_natural_no_zero',
                'service_id'        => 'required|is_natural_no_zero',
                'package_type_id'   => 'required|is_natural_no_zero',
                'commodity_type_id' => 'required|is_natural_no_zero',
                'payment_type'      => 'required|in_list[CASH,DEPOSIT,INVOICE]',
                'shipment_date'     => 'required|valid_date[Y-m-d]',
            ];

            if (!$this->validate($rules)) {
                throw new \RuntimeException(
                    'Validasi gagal: ' .
                    implode(', ', $this->validator->getErrors())
                );
            }

            if (empty($post['packages']) || !is_array($post['packages'])) {
                throw new \RuntimeException('Data paket belum diinput.');
            }

            $customerId     = (int) $post['customer_id'];
            $senderAddressId = (int) $post['sender_address_id'];
            $receiverId     = (int) $post['receiver_id'];
            $serviceId      = (int) $post['service_id'];
            $packageTypeId  = (int) $post['package_type_id'];
            $commodityId    = (int) $post['commodity_type_id'];
            $transportModeId = max(1, (int) ($post['transport_mode_id'] ?? 1));

            $customer = (new CustomerModel())
                ->where('id', $customerId)
                ->where('status', 1)
                ->first();

            if (!$customer) {
                throw new \RuntimeException('Customer tidak ditemukan atau tidak aktif.');
            }

            $addressModel = new CustomerAddressModel();

            $sender = $addressModel
                ->where('id', $senderAddressId)
                ->where('customer_id', $customerId)
                ->where('address_type', 'SENDER')
                ->where('status', 1)
                ->first();

            if (!$sender) {
                throw new \RuntimeException(
                    'Alamat sender tidak ditemukan atau bukan milik customer terpilih.'
                );
            }

            $receiver = (new CustomerAddressModel())
                ->where('id', $receiverId)
                ->where('customer_id', $customerId)
                ->where('address_type', 'RECEIVER')
                ->where('status', 1)
                ->first();

            if (!$receiver) {
                throw new \RuntimeException(
                    'Alamat receiver tidak ditemukan atau bukan milik customer terpilih.'
                );
            }

            $countryId = (int) ($receiver['country_id'] ?? 0);

            if ($countryId <= 0) {
                throw new \RuntimeException('Negara tujuan receiver belum lengkap.');
            }

            if (
                !empty($post['country_id']) &&
                (int) $post['country_id'] !== $countryId
            ) {
                throw new \RuntimeException(
                    'Negara tujuan tidak sama dengan alamat receiver.'
                );
            }

            $country = (new CountryModel())
                ->where('id', $countryId)
                ->where('status', 1)
                ->first();

            $service = (new ServiceModel())
                ->where('id', $serviceId)
                ->where('status', 1)
                ->first();

            $packageType = (new PackageTypeModel())
                ->where('id', $packageTypeId)
                ->where('status', 1)
                ->first();

            $commodity = (new CommodityTypeModel())
                ->where('id', $commodityId)
                ->where('status', 1)
                ->first();

            if (!$country || !$service || !$packageType || !$commodity) {
                throw new \RuntimeException(
                    'Master Country, Service, Package Type, atau Commodity tidak valid.'
                );
            }

            $transportMode = $db->table('transport_modes')
                ->where('id', $transportModeId)
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if (!$transportMode) {
                throw new \RuntimeException('Transport mode tidak valid.');
            }

            $packageSummary = $this->normalizePackages(
                $post['packages'],
                $db
            );

            $customerInsurance = $this->money($post['insurance_customer'] ?? 0);
            $customerPacking   = $this->money($post['packing_customer'] ?? 0);
            $customerAdditional = $this->money($post['additional_customer'] ?? 0);
            $discountAmount    = $this->money($post['discount_amount'] ?? 0);

            $vendorInsurance = $this->money($post['insurance_vendor'] ?? 0);
            $vendorPacking   = $this->money($post['packing_vendor'] ?? 0);
            $vendorAdditional = $this->money($post['additional_vendor'] ?? 0);

            $shipmentType = strtoupper(
                (string) ($packageType['package_type_code'] ?? 'PACKAGE')
            );

            if (!in_array($shipmentType, ['DOCUMENT', 'PACKAGE'], true)) {
                $shipmentType = str_contains($shipmentType, 'DOC')
                    ? 'DOCUMENT'
                    : 'PACKAGE';
            }

            $zone = $this->resolveExpressZone(
                $db,
                (int) ($country['express_zone_id'] ?? 0)
            );

            $pricingInput = [
                'customer_id'        => $customerId,
                'country_id'         => $countryId,
                'service_id'         => $serviceId,
                'commodity_type_id'  => $commodityId,
                'transport_mode_id'  => $transportModeId,
                'package_type_id'    => $packageTypeId,
                'vendor_id'          => (int) ($service['vendor_id'] ?? 0),
                'shipment_type'      => $shipmentType,
                'zone'               => $zone,
                'actual_weight'      => $packageSummary['actual_weight'],

                // Service lama memakai max(actual_weight, volume_weight).
                // Nilai ini sengaja memakai total chargeable multi-colly.
                'volume_weight'      => $packageSummary['chargeable_weight'],
                'chargeable_weight'  => $packageSummary['chargeable_weight'],
                'total_colly'        => $packageSummary['total_colly'],
                'total_items'        => $packageSummary['total_items'],
                'insurance_amount'   => $customerInsurance,
                'packing_charge'     => $customerPacking,
                'additional_charge'  => $customerAdditional,
                'discount_amount'    => $discountAmount,
                'discount'           => $discountAmount,
                'receiver_city'      => (string) ($receiver['city'] ?? ''),
                'receiver_postcode'  => (string) ($receiver['postal_code'] ?? ''),
            ];

            $pricing = (new ShipmentCalculationService())
                ->calculate($pricingInput);

            $freight = $this->money($pricing['freight'] ?? 0);
            $customerTotal = $this->money(
                $pricing['customer_total'] ?? 0
            );
            $vendorBaseTotal = $this->money(
                $pricing['vendor_total'] ?? 0
            );

            if ($freight <= 0 || $customerTotal <= 0) {
                throw new \RuntimeException(
                    'Tarif customer tidak ditemukan. Periksa master tariff.'
                );
            }

            $vendorTotal = round(
                $vendorBaseTotal +
                $vendorInsurance +
                $vendorPacking +
                $vendorAdditional,
                2
            );

            $profit = round($customerTotal - $vendorTotal, 2);
            $margin = $customerTotal > 0
                ? round(($profit / $customerTotal) * 100, 2)
                : 0;
            $markup = $vendorTotal > 0
                ? round(($profit / $vendorTotal) * 100, 2)
                : 0;

            $awbNo = AwbGenerator::generate();

            $shipmentData = [
                'company_id'           => $customer['company_id'] ?? null,
                'awb_no'               => $awbNo,
                'shipment_mode'        => 'MULTI_COLLY',
                'customer_reference_no'=> $this->clean($post['customer_reference_no'] ?? ''),
                'shipment_date'        => $post['shipment_date'],
                'customer_id'          => $customerId,
                'sender_address_id'    => $senderAddressId,
                'sender_country_id'    => $sender['country_id'] ?? null,
                'receiver_address_id'  => $receiverId,
                'agent_id'             => $customer['agent_id'] ?? null,
                'vendor_id'            => $service['vendor_id'] ?? null,
                'country_id'           => $countryId,
                'service_id'           => $serviceId,
                'transport_mode_id'    => $transportModeId,
                'delivery_type_id'     => !empty($post['delivery_type_id'])
                    ? (int) $post['delivery_type_id']
                    : null,
                'package_type_id'      => $packageTypeId,
                'payment_type'         => strtoupper((string) $post['payment_type']),
                'commodity_type_id'    => $commodityId,
                'total_colly'          => $packageSummary['total_colly'],
                'actual_weight'        => $packageSummary['actual_weight'],
                'volume_weight'        => $packageSummary['volume_weight'],
                'chargeable_weight'    => $packageSummary['chargeable_weight'],
                'declared_value'       => $packageSummary['declared_value'],
                'currency_id'          => $packageSummary['currency_id'],
                'insurance_amount'     => $customerInsurance,
                'packing_charge'       => $customerPacking,
                'additional_charge'    => $customerAdditional,
                'discount_amount'      => $discountAmount,
                'freight_charge'       => $freight,
                'remote_charge'        => $this->money($pricing['remote'] ?? 0),
                'selling_amount'       => $freight,
                'vendor_cost'          => $vendorBaseTotal,
                'vendor_freight'       => $vendorBaseTotal,
                'fuel_charge'          => $this->money($pricing['fuel'] ?? 0),
                'vendor_remote_charge' => $this->money(
                    $pricing['vendor_breakdown']['remote'] ?? 0
                ),
                'go_green_charge'      => $this->money(
                    $pricing['gogreen'] ?? $pricing['go_green'] ?? 0
                ),
                'handling_charge'      => $this->money(
                    $pricing['vendor_breakdown']['handling'] ?? 0
                ),
                'vat_amount'           => $this->money(
                    $pricing['tax'] ??
                    $pricing['vendor_breakdown']['vat'] ??
                    0
                ),
                'vendor_total'         => $vendorTotal,
                'total_charge'         => $customerTotal,
                'profit_amount'        => $profit,
                'margin_percent'       => $margin,
                'markup_percent'       => $markup,
                'shipment_status'      => 'DRAFT',
                'remarks'              => $this->clean($post['remarks'] ?? ''),

                // Snapshot sender agar histori tidak berubah saat address book diedit.
                'sender_name'          => $this->clean($sender['contact_name'] ?? ''),
                'sender_company'       => $this->clean($sender['company_name'] ?? ''),
                'sender_phone'         => $this->clean($sender['phone'] ?? ''),
                'sender_email'         => $this->clean($sender['email'] ?? ''),
                'sender_identity_number' => $this->clean(
                    $sender['identity_number'] ?? ''
                ),
                'sender_address'       => $this->clean($sender['address'] ?? ''),
                'sender_city'          => $this->clean($sender['city'] ?? ''),
                'sender_state'         => $this->clean($sender['state'] ?? ''),
                'sender_postal_code'   => $this->clean($sender['postal_code'] ?? ''),

                // Snapshot receiver.
                'receiver_name'        => $this->clean($receiver['contact_name'] ?? ''),
                'receiver_company'     => $this->clean($receiver['company_name'] ?? ''),
                'receiver_phone'       => $this->clean($receiver['phone'] ?? ''),
                'receiver_email'       => $this->clean($receiver['email'] ?? ''),
                'receiver_id_number'   => $this->clean(
                    $receiver['identity_number'] ?? ''
                ),
                'receiver_address'     => $this->clean($receiver['address'] ?? ''),
                'receiver_city'        => $this->clean($receiver['city'] ?? ''),
                'receiver_state'       => $this->clean($receiver['state'] ?? ''),
                'receiver_postal_code' => $this->clean($receiver['postal_code'] ?? ''),
            ];

            $shipmentId = $shipmentModel->insert($shipmentData, true);

            if ($shipmentId === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan shipment: ' .
                    implode(', ', $shipmentModel->errors())
                );
            }

            $this->savePackages(
                (int) $shipmentId,
                $packageSummary['packages'],
                $packageTypeId
            );

            $this->saveCostBreakdown(
                (int) $shipmentId,
                'CUSTOMER',
                [
                    ['code' => 'FREIGHT', 'name' => 'Freight Customer', 'amount' => $freight],
                    ['code' => 'INSURANCE', 'name' => 'Customer Insurance', 'amount' => $customerInsurance],
                    ['code' => 'PACKING', 'name' => 'Customer Packing', 'amount' => $customerPacking],
                    ['code' => 'ADDITIONAL', 'name' => 'Customer Additional', 'amount' => $customerAdditional],
                    ['code' => 'DISCOUNT', 'name' => 'Discount', 'amount' => -$discountAmount],
                ]
            );

            $this->saveCostBreakdown(
                (int) $shipmentId,
                'VENDOR',
                [
                    ['code' => 'BASE_VENDOR_COST', 'name' => 'Base Vendor Cost', 'amount' => $vendorBaseTotal],
                    ['code' => 'INSURANCE', 'name' => 'Vendor Insurance', 'amount' => $vendorInsurance],
                    ['code' => 'PACKING', 'name' => 'Vendor Packing', 'amount' => $vendorPacking],
                    ['code' => 'ADDITIONAL', 'name' => 'Vendor Additional', 'amount' => $vendorAdditional],
                ]
            );

            $this->createTracking((int) $shipmentId, $awbNo);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('shipments'))
                ->with('success', 'Shipment berhasil dibuat: ' . $awbNo);
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                'Shipment store failed: {message}',
                ['message' => $e->getMessage()]
            );

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat shipment: ' . $e->getMessage());
        }
    }
    private function normalizePackages(array $rawPackages, $db): array
    {
        $currencyRows = $db->table('currencies')
            ->select('id, currency_code')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $currencyMap = [];

        foreach ($currencyRows as $currency) {
            $currencyMap[strtoupper((string) $currency['currency_code'])] =
                (int) $currency['id'];
        }

        $packages = [];
        $totalActual = 0.0;
        $totalVolume = 0.0;
        $totalChargeable = 0.0;
        $declaredValue = 0.0;
        $totalItems = 0;
        $shipmentCurrencyCode = null;
        $shipmentCurrencyId = null;

        foreach (array_values($rawPackages) as $packageIndex => $rawPackage) {
            if (!is_array($rawPackage)) {
                continue;
            }

            $actual = max(0, $this->money($rawPackage['actual_weight'] ?? 0));
            $length = max(0, $this->money($rawPackage['length_cm'] ?? 0));
            $width  = max(0, $this->money($rawPackage['width_cm'] ?? 0));
            $height = max(0, $this->money($rawPackage['height_cm'] ?? 0));

            $volume = round(($length * $width * $height) / 5000, 2);
            $chargeable = round(max($actual, $volume), 2);

            if ($chargeable <= 0) {
                throw new \RuntimeException(
                    'Berat Colly #' . ($packageIndex + 1) . ' harus lebih dari 0.'
                );
            }

            $normalizedItems = [];
            $rawItems = is_array($rawPackage['items'] ?? null)
                ? $rawPackage['items']
                : [];

            foreach (array_values($rawItems) as $itemIndex => $rawItem) {
                if (!is_array($rawItem)) {
                    continue;
                }

                $description = $this->clean(
                    $rawItem['item_description'] ?? ''
                );

                if ($description === '') {
                    throw new \RuntimeException(
                        'Item description pada Colly #' .
                        ($packageIndex + 1) .
                        ' baris #' .
                        ($itemIndex + 1) .
                        ' wajib diisi.'
                    );
                }

                $quantity = (int) ($rawItem['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \RuntimeException(
                        'Quantity item harus lebih dari 0.'
                    );
                }

                $unitValue = max(0, $this->money($rawItem['unit_value'] ?? 0));
                $currencyCode = strtoupper(
                    $this->clean($rawItem['currency'] ?? 'USD')
                );

                if (!isset($currencyMap[$currencyCode])) {
                    throw new \RuntimeException(
                        'Currency ' . $currencyCode . ' tidak ditemukan.'
                    );
                }

                if ($shipmentCurrencyCode === null) {
                    $shipmentCurrencyCode = $currencyCode;
                    $shipmentCurrencyId = $currencyMap[$currencyCode];
                } elseif ($shipmentCurrencyCode !== $currencyCode) {
                    throw new \RuntimeException(
                        'Satu shipment sementara hanya boleh memakai satu currency.'
                    );
                }

                $totalValue = round($quantity * $unitValue, 2);
                $declaredValue += $totalValue;
                $totalItems++;

                $normalizedItems[] = [
                    'item_description' => $description,
                    'quantity'         => $quantity,
                    'unit_type'        => 'PCS',
                    'unit_value'       => $unitValue,
                    'currency_id'      => $currencyMap[$currencyCode],
                    'total_value'      => $totalValue,
                ];
            }

            if ($normalizedItems === []) {
                throw new \RuntimeException(
                    'Colly #' . ($packageIndex + 1) . ' belum memiliki item.'
                );
            }

            $packages[] = [
                'package_no'       => $packageIndex + 1,
                'actual_weight'    => round($actual, 2),
                'length_cm'        => round($length, 2),
                'width_cm'         => round($width, 2),
                'height_cm'        => round($height, 2),
                'volume_weight'    => $volume,
                'chargeable_weight'=> $chargeable,
                'items'            => $normalizedItems,
            ];

            $totalActual += $actual;
            $totalVolume += $volume;
            $totalChargeable += $chargeable;
        }

        if ($packages === []) {
            throw new \RuntimeException('Tidak ada paket yang valid.');
        }

        return [
            'packages'          => $packages,
            'total_colly'       => count($packages),
            'total_items'       => $totalItems,
            'actual_weight'     => round($totalActual, 2),
            'volume_weight'     => round($totalVolume, 2),
            'chargeable_weight' => round($totalChargeable, 2),
            'declared_value'    => round($declaredValue, 2),
            'currency_id'       => $shipmentCurrencyId,
        ];
    }

    private function savePackages(
        int $shipmentId,
        array $packages,
        int $packageTypeId
    ): void {
        $packageModel = new ShipmentPackageModel();
        $itemModel    = new ShipmentPackageItemModel();

        foreach ($packages as $package) {
            $items = $package['items'];
            unset($package['items']);

            $package['shipment_id'] = $shipmentId;
            $package['package_type_id'] = $packageTypeId;

            $packageId = $packageModel->insert($package, true);

            if ($packageId === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan package: ' .
                    implode(', ', $packageModel->errors())
                );
            }

            foreach ($items as $item) {
                $item['shipment_package_id'] = (int) $packageId;

                if ($itemModel->insert($item) === false) {
                    throw new \RuntimeException(
                        'Gagal menyimpan package item: ' .
                        implode(', ', $itemModel->errors())
                    );
                }
            }
        }
    }

    private function saveCostBreakdown(
        int $shipmentId,
        string $party,
        array $components
    ): void {
        $model = new \App\Models\ShipmentCostDetailModel();

        foreach ($components as $component) {
            $rawAmount = $component['amount'] ?? 0;
            $amount = is_numeric($rawAmount)
                ? round((float) $rawAmount, 2)
                : 0.0;

            if (abs($amount) < 0.00001) {
                continue;
            }

            if ($model->insert([
                'shipment_id'   => $shipmentId,
                'party'         => strtoupper($party),
                'component_code'=> strtoupper((string) $component['code']),
                'component_name'=> (string) $component['name'],
                'amount'        => $amount,
                'remarks'       => $component['remarks'] ?? null,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan cost breakdown: ' .
                    implode(', ', $model->errors())
                );
            }
        }
    }

    private function createTracking(int $shipmentId, string $awbNo): void
    {
        $trackingModel = new ShipmentTrackingModel();

        if ($trackingModel->insert([
            'shipment_id'       => $shipmentId,
            'tracking_code'     => $awbNo,
            'tracking_status'   => 'DRAFT',
            'tracking_location' => 'Cartenz System',
            'tracking_notes'    => 'Shipment berhasil dibuat',
            'event_time'        => date('Y-m-d H:i:s'),
            'created_by'        => auth()->id() ?? null,
            'created_at'        => date('Y-m-d H:i:s'),
        ]) === false) {
            throw new \RuntimeException(
                'Gagal membuat tracking awal: ' .
                implode(', ', $trackingModel->errors())
            );
        }
    }

    private function resolveExpressZone($db, int $zoneId): int
    {
        if ($zoneId <= 0) {
            return 1;
        }

        $zone = $db->table('express_zones')
            ->where('id', $zoneId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (!$zone) {
            return $zoneId;
        }

        $digits = preg_replace('/\D+/', '', (string) ($zone['zone_code'] ?? ''));

        return $digits !== '' ? (int) $digits : $zoneId;
    }

    private function money($value): float
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return round(max(0, $number), 2);
    }

    private function clean($value): string
    {
        return trim(strip_tags((string) $value));
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

    public function calculatePricing()
{
    $shipment = [
        'customer_id'        => $this->request->getPost('customer_id'),
        'country_id'         => $this->request->getPost('country_id'),
        'service_id'         => $this->request->getPost('service_id'),
        'commodity_type_id'  => $this->request->getPost('commodity_type_id'),

        'actual_weight'      => $this->request->getPost('actual_weight'),
        'volume_weight'      => $this->request->getPost('volume_weight'),
        'chargeable_weight'  => $this->request->getPost('chargeable_weight'),

        'total_colly'        => $this->request->getPost('total_colly'),

        'insurance_amount'   => $this->request->getPost('insurance_amount'),
        'packing_charge'     => $this->request->getPost('packing_charge'),
        'additional_charge'  => $this->request->getPost('additional_charge'),
        'discount_amount'    => $this->request->getPost('discount_amount'),

        'service_engine'     => 'REGULAR'
    ];

    $pricing = new PricingEngine();

    return $this->response->setJSON(
        $pricing->calculate($shipment)
    );
}
}