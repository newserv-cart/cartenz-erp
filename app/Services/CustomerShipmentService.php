<?php

namespace App\Services;

use App\Libraries\AwbGenerator;
use App\Models\CustomerAddressModel;
use App\Models\ShipmentItemModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentPackageModel;
use CodeIgniter\Database\BaseConnection;

class CustomerShipmentService
{
    public const HANDOVER_PICKUP = 'COURIER_PICKUP';
    public const HANDOVER_DROPOFF = 'CUSTOMER_DROP_OFF_BRANCH';

    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function formData(array $customer): array
    {
        $customerId = (int) ($customer['id'] ?? 0);

        return [
            'customer'          => $customer,
            'senderAddresses'   => $this->addresses($customerId, 'SENDER'),
            'receiverAddresses' => $this->addresses($customerId, 'RECEIVER'),
            'services'          => $this->regularServices(),
            'transportModes'    => $this->masterOptions(
                'transport_modes',
                ['mode_name', 'name', 'mode_code', 'code']
            ),
            'deliveryTypes'     => $this->masterOptions(
                'delivery_types',
                [
                    'delivery_name',
                    'delivery_type_name',
                    'type_name',
                    'name',
                    'delivery_code',
                    'code',
                ]
            ),
            'packageTypes'      => $this->masterOptions(
                'package_types',
                [
                    'package_name',
                    'package_type_name',
                    'type_name',
                    'name',
                    'package_code',
                    'code',
                ]
            ),
            'commodityTypes'    => $this->masterOptions(
                'commodity_types',
                [
                    'commodity_name',
                    'commodity_type_name',
                    'type_name',
                    'name',
                    'commodity_code',
                    'code',
                ]
            ),
            'currencies'        => $this->masterOptions(
                'currencies',
                [
                    'currency_code',
                    'code',
                    'currency_name',
                    'name',
                ],
                true
            ),
            'dropoffBranches'   => $this->masterOptions(
                'branches',
                ['branch_name', 'branch_code', 'city']
            ),
            'paymentType'       => $this->resolvePaymentType($customer),
        ];
    }

    public function create(
        array $customer,
        array $post,
        int $actorId
    ): array {
        $customerId = (int) ($customer['id'] ?? 0);

        if ($customerId <= 0 || $actorId <= 0) {
            throw new \RuntimeException(
                'Customer atau sesi user tidak valid.'
            );
        }

        $senderId   = (int) ($post['sender_address_id'] ?? 0);
        $receiverId = (int) ($post['receiver_address_id'] ?? 0);

        $sender = $this->ownedAddress(
            $customerId,
            $senderId,
            'SENDER'
        );

        $receiver = $this->ownedAddress(
            $customerId,
            $receiverId,
            'RECEIVER'
        );

        $countryId      = (int) ($receiver['country_id'] ?? 0);
        $serviceId      = (int) ($post['service_id'] ?? 0);
        $transportId    = (int) ($post['transport_mode_id'] ?? 0);
        $deliveryId     = (int) ($post['delivery_type_id'] ?? 0);
        $packageTypeId  = (int) ($post['package_type_id'] ?? 0);
        $commodityId    = (int) ($post['commodity_type_id'] ?? 0);
        $currencyId     = (int) ($post['currency_id'] ?? 0);
        $actualWeight   = $this->positiveNumber(
            $post['actual_weight'] ?? null,
            'Actual Weight'
        );
        $length         = $this->positiveNumber(
            $post['length_cm'] ?? null,
            'Length'
        );
        $width          = $this->positiveNumber(
            $post['width_cm'] ?? null,
            'Width'
        );
        $height         = $this->positiveNumber(
            $post['height_cm'] ?? null,
            'Height'
        );
        $quantity       = (int) ($post['item_quantity'] ?? 0);
        $unitValue      = $this->nonNegativeNumber(
            $post['item_unit_value'] ?? null,
            'Unit Value'
        );
        $itemDescription = trim(
            (string) ($post['item_description'] ?? '')
        );
        $handoverMethod = strtoupper(trim(
            (string) ($post['planned_handover_method'] ?? '')
        ));
        $dropoffBranchId = (int) (
            $post['planned_dropoff_branch_id'] ?? 0
        );

        if ($countryId <= 0) {
            throw new \RuntimeException(
                'Receiver wajib mempunyai negara tujuan.'
            );
        }

        $this->assertActiveId('services', $serviceId, 'Service');
        $this->assertRegularService($serviceId);
        $this->assertActiveId(
            'transport_modes',
            $transportId,
            'Transport Mode'
        );
        $this->assertActiveId(
            'delivery_types',
            $deliveryId,
            'Delivery Type'
        );
        $this->assertActiveId(
            'package_types',
            $packageTypeId,
            'Package Type'
        );
        $this->assertActiveId(
            'commodity_types',
            $commodityId,
            'Commodity Type'
        );
        $this->assertActiveId(
            'currencies',
            $currencyId,
            'Currency'
        );

        if ($itemDescription === '') {
            throw new \RuntimeException(
                'Item Description wajib diisi.'
            );
        }

        if ($quantity <= 0) {
            throw new \RuntimeException(
                'Item Quantity harus lebih dari 0.'
            );
        }

        if (
            !in_array(
                $handoverMethod,
                [self::HANDOVER_PICKUP, self::HANDOVER_DROPOFF],
                true
            )
        ) {
            throw new \RuntimeException(
                'Handover Method tidak valid.'
            );
        }

        if ($handoverMethod === self::HANDOVER_DROPOFF) {
            $this->assertActiveId(
                'branches',
                $dropoffBranchId,
                'Drop-off Location'
            );
        } else {
            $dropoffBranchId = 0;
        }

        $volumeWeight = round(
            ($length * $width * $height) / 5000,
            2
        );
        $chargeableWeight = round(
            max($actualWeight, $volumeWeight),
            2
        );
        $declaredValue = round($quantity * $unitValue, 2);
        $shipmentDate = date('Y-m-d');

        $pricing = (new RegularCalculationService())->calculate([
            'customer_id'       => $customerId,
            'country_id'        => $countryId,
            'service_id'        => $serviceId,
            'commodity_type_id' => $commodityId,
            'actual_weight'     => $actualWeight,
            'volume_weight'     => $volumeWeight,
            'chargeable_weight' => $chargeableWeight,
            'shipment_date'     => $shipmentDate,
            'insurance_amount'  => 0,
            'packing_charge'    => 0,
            'additional_charge' => 0,
            'discount_amount'   => 0,
        ]);

        $freight      = (float) ($pricing['freight'] ?? 0);
        $vendorTotal  = (float) ($pricing['vendor_total'] ?? 0);
        $customerTotal = (float) (
            $pricing['customer_total'] ?? 0
        );

        $this->db->transBegin();

        try {
            $awbNo = AwbGenerator::generate();
            $now   = date('Y-m-d H:i:s');

            $shipmentData = [
                'company_id' => (int) (
                    $customer['company_id'] ?? 0
                ) ?: null,
                'awb_no' => $awbNo,
                'shipment_mode' => 'MULTI_COLLY',
                'customer_reference_no' => $this->nullableText(
                    $post['customer_reference_no'] ?? null,
                    100
                ),
                'shipment_date' => $shipmentDate,
                'customer_id' => $customerId,
                'sender_address_id' => $senderId,
                'sender_country_id' => (int) (
                    $sender['country_id'] ?? 0
                ) ?: null,
                'receiver_address_id' => $receiverId,
                'agent_id' => (int) ($customer['agent_id'] ?? 0)
                    ?: null,
                'acceptance_method' =>
                    ShipmentAcceptanceService::DATA_ENTRY_ONLY,
                'planned_handover_method' => $handoverMethod,
                'planned_dropoff_branch_id' =>
                    $dropoffBranchId > 0
                        ? $dropoffBranchId
                        : null,
                'acceptance_branch_id' => null,
                'receiving_branch_id' => null,
                'current_branch_id' => null,
                'receiving_location' => null,
                'received_at' => null,
                'received_by' => null,
                'country_id' => $countryId,
                'service_id' => $serviceId,
                'transport_mode_id' => $transportId,
                'delivery_type_id' => $deliveryId,
                'package_type_id' => $packageTypeId,
                'payment_type' => $this->resolvePaymentType(
                    $customer
                ),
                'commodity_type_id' => $commodityId,
                'total_colly' => 1,
                'actual_weight' => $actualWeight,
                'volume_weight' => $volumeWeight,
                'chargeable_weight' => $chargeableWeight,
                'declared_value' => $declaredValue,
                'currency_id' => $currencyId,
                'insurance_amount' => 0,
                'packing_charge' => 0,
                'additional_charge' => 0,
                'discount_amount' => 0,
                'freight_charge' => $freight,
                'remote_charge' => 0,
                'selling_amount' => $freight,
                'vendor_cost' => $vendorTotal,
                'vendor_freight' => $vendorTotal,
                'vendor_total' => $vendorTotal,
                'total_charge' => $customerTotal,
                'profit_amount' => (float) (
                    $pricing['profit'] ?? 0
                ),
                'margin_percent' => (float) (
                    $pricing['margin'] ?? 0
                ),
                'markup_percent' => (float) (
                    $pricing['markup'] ?? 0
                ),
                'shipment_status' => 'DRAFT',
                'public_tracking_status' => 'CREATED',
                'remarks' => $this->nullableText(
                    $post['remarks'] ?? null,
                    2000
                ),
                'sender_name' => $sender['contact_name'] ?? null,
                'sender_company' => $sender['company_name'] ?? null,
                'sender_phone' => $sender['phone'] ?? null,
                'sender_email' => $sender['email'] ?? null,
                'sender_identity_number' =>
                    $sender['identity_number'] ?? null,
                'sender_address' => $sender['address'] ?? null,
                'sender_city' => $sender['city'] ?? null,
                'sender_state' => $sender['state'] ?? null,
                'sender_postal_code' =>
                    $sender['postal_code'] ?? null,
                'receiver_name' => $receiver['contact_name'] ?? null,
                'receiver_company' =>
                    $receiver['company_name'] ?? null,
                'receiver_phone' => $receiver['phone'] ?? null,
                'receiver_email' => $receiver['email'] ?? null,
                'receiver_id_number' =>
                    $receiver['identity_number'] ?? null,
                'receiver_address' => $receiver['address'] ?? null,
                'receiver_city' => $receiver['city'] ?? null,
                'receiver_state' => $receiver['state'] ?? null,
                'receiver_postal_code' =>
                    $receiver['postal_code'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $shipmentModel = new ShipmentModel();
            $shipmentId = $shipmentModel->insert(
                $shipmentData,
                true
            );

            if (!$shipmentId) {
                throw new \RuntimeException(
                    'Shipment gagal disimpan.'
                );
            }

            $packageId = (new ShipmentPackageModel())->insert([
                'shipment_id' => $shipmentId,
                'package_no' => 1,
                'package_barcode' => $awbNo . '-01',
                'package_type_id' => $packageTypeId,
                'actual_weight' => $actualWeight,
                'length_cm' => $length,
                'width_cm' => $width,
                'height_cm' => $height,
                'volume_weight' => $volumeWeight,
                'chargeable_weight' => $chargeableWeight,
                'remarks' => $this->nullableText(
                    $post['package_remarks'] ?? null,
                    500
                ),
            ], true);

            if (!$packageId) {
                throw new \RuntimeException(
                    'Package shipment gagal disimpan.'
                );
            }

            $itemId = (new ShipmentItemModel())->insert([
                'shipment_id' => $shipmentId,
                'item_description' => $itemDescription,
                'quantity' => $quantity,
                'unit_type' => trim(
                    (string) ($post['item_unit_type'] ?? 'PCS')
                ) ?: 'PCS',
                'unit_value' => $unitValue,
                'currency_id' => $currencyId,
                'total_value' => $declaredValue,
                'item_weight' => $actualWeight,
                'length_cm' => $length,
                'width_cm' => $width,
                'height_cm' => $height,
                'is_dangerous_goods' => 0,
                'remarks' => null,
            ], true);

            if (!$itemId) {
                throw new \RuntimeException(
                    'Item shipment gagal disimpan.'
                );
            }

            $this->saveCostBreakdown(
                (int) $shipmentId,
                $pricing,
                $now
            );

            $this->saveInitialAudit(
                (int) $shipmentId,
                $awbNo,
                $actorId,
                $handoverMethod,
                $dropoffBranchId,
                $now
            );

            if ($handoverMethod === self::HANDOVER_PICKUP) {
                $this->savePickupRequest(
                    (int) $shipmentId,
                    $sender,
                    $post,
                    $now
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi pembuatan shipment gagal.'
                );
            }

            $this->db->transCommit();

            return [
                'shipment_id' => (int) $shipmentId,
                'awb_no' => $awbNo,
                'customer_total' => $customerTotal,
                'chargeable_weight' => $chargeableWeight,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function addresses(
        int $customerId,
        string $type
    ): array {
        if ($customerId <= 0) {
            return [];
        }

        $builder = $this->db
            ->table('customer_addresses ca')
            ->select('ca.*');

        if ($this->db->tableExists('countries')) {
            $builder
                ->select('c.country_name')
                ->join(
                    'countries c',
                    'c.id = ca.country_id',
                    'left'
                );
        }

        return $builder
            ->where('ca.customer_id', $customerId)
            ->where('ca.address_type', strtoupper($type))
            ->where('ca.status', 1)
            ->orderBy('ca.is_default', 'DESC')
            ->orderBy('ca.contact_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function ownedAddress(
        int $customerId,
        int $addressId,
        string $type
    ): array {
        if ($addressId <= 0) {
            throw new \RuntimeException(
                ucfirst(strtolower($type)) .
                ' Address wajib dipilih.'
            );
        }

        $address = (new CustomerAddressModel())
            ->where('id', $addressId)
            ->where('customer_id', $customerId)
            ->where('address_type', strtoupper($type))
            ->where('status', 1)
            ->first();

        if (!$address) {
            throw new \RuntimeException(
                ucfirst(strtolower($type)) .
                ' Address tidak valid atau bukan milik customer.'
            );
        }

        return $address;
    }

    private function regularServices(): array
    {
        if (!$this->db->tableExists('services')) {
            return [];
        }

        $builder = $this->db->table('services');

        if ($this->db->fieldExists('status', 'services')) {
            $builder->where('status', 1);
        }

        $rows = $builder
            ->orderBy('service_name', 'ASC')
            ->get()
            ->getResultArray();

        $result = [];

        foreach ($rows as $row) {
            $engine = strtoupper((string) (
                $row['pricing_engine']
                ?? $row['service_code']
                ?? ''
            ));

            if ($engine !== 'REGULAR') {
                continue;
            }

            $result[] = [
                'id' => (int) $row['id'],
                'label' => (string) (
                    $row['service_name']
                    ?? $row['service_code']
                    ?? ('Service #' . $row['id'])
                ),
                'code' => (string) (
                    $row['service_code'] ?? ''
                ),
                'pricing_engine' => 'REGULAR',
            ];
        }

        return $result;
    }

    private function masterOptions(
        string $table,
        array $labelFields,
        bool $includeCode = false
    ): array {
        if (!$this->db->tableExists($table)) {
            return [];
        }

        $fields = $this->db->getFieldNames($table);
        $fieldSet = array_fill_keys($fields, true);
        $builder = $this->db->table($table);

        if (isset($fieldSet['status'])) {
            $builder->where('status', 1);
        }

        $orderField = null;

        foreach ($labelFields as $candidate) {
            if (isset($fieldSet[$candidate])) {
                $orderField = $candidate;
                break;
            }
        }

        if ($orderField !== null) {
            $builder->orderBy($orderField, 'ASC');
        } else {
            $builder->orderBy('id', 'ASC');
        }

        $rows = $builder->get()->getResultArray();
        $result = [];

        foreach ($rows as $row) {
            $label = '';

            foreach ($labelFields as $candidate) {
                $value = trim((string) ($row[$candidate] ?? ''));

                if ($value !== '') {
                    $label = $value;
                    break;
                }
            }

            if ($label === '') {
                $label = $table . ' #' . (int) ($row['id'] ?? 0);
            }

            $option = [
                'id' => (int) ($row['id'] ?? 0),
                'label' => $label,
            ];

            if ($includeCode) {
                $option['code'] = (string) (
                    $row['currency_code']
                    ?? $row['code']
                    ?? ''
                );
            }

            if ($table === 'branches') {
                $code = trim((string) ($row['branch_code'] ?? ''));

                if ($code !== '') {
                    $option['label'] = $code . ' · ' . $label;
                }
            }

            $result[] = $option;
        }

        return $result;
    }

    private function assertActiveId(
        string $table,
        int $id,
        string $label
    ): void {
        if ($id <= 0 || !$this->db->tableExists($table)) {
            throw new \RuntimeException($label . ' tidak valid.');
        }

        $builder = $this->db
            ->table($table)
            ->where('id', $id);

        if ($this->db->fieldExists('status', $table)) {
            $builder->where('status', 1);
        }

        if (!$builder->countAllResults()) {
            throw new \RuntimeException(
                $label . ' tidak aktif atau tidak ditemukan.'
            );
        }
    }

    private function assertRegularService(int $serviceId): void
    {
        $service = $this->db
            ->table('services')
            ->where('id', $serviceId)
            ->get()
            ->getRowArray();

        if (!$service) {
            throw new \RuntimeException('Service tidak ditemukan.');
        }

        $engine = strtoupper((string) (
            $service['pricing_engine']
            ?? $service['service_code']
            ?? ''
        ));

        if ($engine !== 'REGULAR') {
            throw new \RuntimeException(
                'Customer self-service saat ini hanya mendukung Regular Service. '
                . 'Express akan diaktifkan setelah customer Express pricing selesai.'
            );
        }
    }

    private function resolvePaymentType(array $customer): string
    {
        $source = strtoupper(trim((string) (
            $customer['billing_type']
            ?? $customer['customer_type']
            ?? 'CASH'
        )));

        if (str_contains($source, 'INVOICE')) {
            return 'INVOICE';
        }

        if (str_contains($source, 'DEPOSIT')) {
            return 'DEPOSIT';
        }

        return 'CASH';
    }

    private function saveCostBreakdown(
        int $shipmentId,
        array $pricing,
        string $now
    ): void {
        if (!$this->db->tableExists('shipment_cost_details')) {
            return;
        }

        $customerRows = [
            ['CUSTOMER', 'FREIGHT', 'Freight Customer', (float) ($pricing['freight'] ?? 0)],
            ['CUSTOMER', 'INSURANCE', 'Customer Insurance', (float) ($pricing['insurance'] ?? 0)],
            ['CUSTOMER', 'PACKING', 'Customer Packing', (float) ($pricing['packing'] ?? 0)],
            ['CUSTOMER', 'ADDITIONAL', 'Customer Additional', (float) ($pricing['additional'] ?? 0)],
            ['CUSTOMER', 'DISCOUNT', 'Discount', -abs((float) ($pricing['discount'] ?? 0))],
        ];

        $vendorRows = [
            ['VENDOR', 'BASE_VENDOR_COST', 'Base Vendor Cost', (float) ($pricing['vendor_total'] ?? 0)],
        ];

        foreach (array_merge($customerRows, $vendorRows) as $row) {
            $this->insertFiltered('shipment_cost_details', [
                'shipment_id' => $shipmentId,
                'party' => $row[0],
                'component_code' => $row[1],
                'component_name' => $row[2],
                'amount' => round((float) $row[3], 2),
                'remarks' => 'Customer self-service pricing snapshot.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function saveInitialAudit(
        int $shipmentId,
        string $awbNo,
        int $actorId,
        string $handoverMethod,
        int $dropoffBranchId,
        string $now
    ): void {
        $description = 'Shipment dibuat oleh customer melalui portal self-service. '
            . 'Barang belum diterima secara fisik oleh CARTENZ.';

        if ($this->db->tableExists('shipment_tracking')) {
            $this->insertFiltered('shipment_tracking', [
                'shipment_id' => $shipmentId,
                'tracking_code' => $awbNo,
                'tracking_status' => 'DRAFT',
                'public_status' => 'CREATED',
                'tracking_location' => 'Customer Portal',
                'tracking_notes' => $description,
                'event_time' => $now,
                'created_by' => $actorId,
                'created_at' => $now,
                'branch_id' => null,
                'event_type' => 'CUSTOMER_SELF_SERVICE_CREATED',
                'scan_type' => null,
                'source_reference' => $awbNo,
            ]);
        }

        if ($this->db->tableExists('shipment_histories')) {
            $this->insertFiltered('shipment_histories', [
                'shipment_id' => $shipmentId,
                'event_type' => 'CUSTOMER_SELF_SERVICE_CREATED',
                'event_title' => 'Customer Self-Service Shipment Created',
                'description' => $description,
                'changed_fields' => json_encode([
                    'shipment_status' => 'DRAFT',
                    'public_status' => 'CREATED',
                    'planned_handover_method' => $handoverMethod,
                    'planned_dropoff_branch_id' =>
                        $dropoffBranchId > 0
                            ? $dropoffBranchId
                            : null,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_by' => $actorId,
                'created_at' => $now,
            ]);
        }
    }

    private function savePickupRequest(
        int $shipmentId,
        array $sender,
        array $post,
        string $now
    ): void {
        if (!$this->db->tableExists('pickup_requests')) {
            return;
        }

        $pickupDate = trim((string) (
            $post['pickup_date'] ?? date('Y-m-d')
        ));
        $pickupTime = trim((string) (
            $post['pickup_time'] ?? ''
        ));

        $this->insertFiltered('pickup_requests', [
            'shipment_id' => $shipmentId,
            'pickup_date' => $pickupDate !== ''
                ? $pickupDate
                : date('Y-m-d'),
            'pickup_time' => $pickupTime !== ''
                ? $pickupTime
                : null,
            'pickup_address' => $sender['address'] ?? null,
            'contact_name' => $sender['contact_name'] ?? null,
            'contact_phone' => $sender['phone'] ?? null,
            'pickup_status' => 'REQUESTED',
            'notes' => 'Dibuat otomatis dari customer self-service.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertFiltered(
        string $table,
        array $data
    ): void {
        $fields = array_fill_keys(
            $this->db->getFieldNames($table),
            true
        );
        $filtered = array_intersect_key($data, $fields);

        if ($filtered === []) {
            return;
        }

        if (!$this->db->table($table)->insert($filtered)) {
            throw new \RuntimeException(
                'Gagal menyimpan data audit ke tabel ' . $table . '.'
            );
        }
    }

    private function positiveNumber($value, string $label): float
    {
        $number = $this->numericValue($value);

        if ($number === null || $number <= 0) {
            throw new \RuntimeException(
                $label . ' harus lebih dari 0.'
            );
        }

        return round($number, 2);
    }

    private function nonNegativeNumber(
        $value,
        string $label
    ): float {
        $number = $this->numericValue($value);

        if ($number === null || $number < 0) {
            throw new \RuntimeException(
                $label . ' tidak boleh negatif.'
            );
        }

        return round($number, 2);
    }

    private function numericValue($value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        $text = str_replace(' ', '', $text);
        $text = str_replace(',', '.', $text);

        return is_numeric($text)
            ? (float) $text
            : null;
    }

    private function nullableText($value, int $maxLength): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $maxLength);
    }
}
