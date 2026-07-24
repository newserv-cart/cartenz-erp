<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Edit Shipment <?= esc($shipment['awb_no'] ?? '') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body>
<?php
$getValue = static function (
    string $key,
    $fallback = ''
) {
    $oldValue = old($key);

    return $oldValue !== null
        ? $oldValue
        : $fallback;
};

$formPackages = old('packages');

if (
    !is_array($formPackages)
    || $formPackages === []
) {
    $formPackages = $packages;
}

$selectedCustomerId = (int) $getValue(
    'customer_id',
    $shipment['customer_id'] ?? 0
);

$selectedSenderId = (int) $getValue(
    'sender_address_id',
    $shipment['sender_address_id'] ?? 0
);

$selectedReceiverId = (int) $getValue(
    'receiver_id',
    $shipment['receiver_address_id'] ?? 0
);

$selectedServiceId = (int) $getValue(
    'service_id',
    $shipment['service_id'] ?? 0
);

$selectedPackageTypeId = (int) $getValue(
    'package_type_id',
    $shipment['package_type_id'] ?? 0
);

$selectedCommodityId = (int) $getValue(
    'commodity_type_id',
    $shipment['commodity_type_id'] ?? 0
);

$selectedPaymentType = strtoupper(
    (string) $getValue(
        'payment_type',
        $shipment['payment_type'] ?? ''
    )
);

$selectedTransportMode = (int) $getValue(
    'transport_mode_id',
    $shipment['transport_mode_id'] ?? 1
);

$selectedDeliveryType = (int) $getValue(
    'delivery_type_id',
    $shipment['delivery_type_id'] ?? 1
);

$selectedAcceptanceMethod = (string) $getValue(
    'acceptance_method',
    $shipment['acceptance_method']
        ?? \App\Services\ShipmentAcceptanceService::DATA_ENTRY_ONLY
);

$selectedReceivingBranch = (int) $getValue(
    'receiving_branch_id',
    $shipment['receiving_branch_id'] ?? 0
);

$customerInsurance = (float) $getValue(
    'insurance_customer',
    $editCharges['insurance_customer'] ?? 0
);

$customerPacking = (float) $getValue(
    'packing_customer',
    $editCharges['packing_customer'] ?? 0
);

$customerAdditional = (float) $getValue(
    'additional_customer',
    $editCharges['additional_customer'] ?? 0
);

$discountAmount = (float) $getValue(
    'discount_amount',
    $editCharges['discount_amount'] ?? 0
);

$vendorInsurance = (float) $getValue(
    'insurance_vendor',
    $editCharges['insurance_vendor'] ?? 0
);

$vendorPacking = (float) $getValue(
    'packing_vendor',
    $editCharges['packing_vendor'] ?? 0
);

$vendorAdditional = (float) $getValue(
    'additional_vendor',
    $editCharges['additional_vendor'] ?? 0
);

$customerTotal = (float) (
    $shipment['total_charge'] ?? 0
);

$vendorTotal = (float) (
    $shipment['vendor_total'] ?? 0
);

$profitAmount = (float) (
    $shipment['profit_amount'] ?? 0
);

$systemSubtotal = max(
    0,
    $customerTotal
    - $customerInsurance
    - $customerPacking
    - $customerAdditional
    + $discountAmount
);

$vendorBaseTotal = max(
    0,
    $vendorTotal
    - $vendorInsurance
    - $vendorPacking
    - $vendorAdditional
);

$customerOtherCharges =
    $customerInsurance
    + $customerPacking
    + $customerAdditional;
?>

<form
    id="shipmentForm"
    method="post"
    action="<?= site_url(
        'shipments/update/' . $shipment['id']
    ) ?>"
    data-base-url="<?= esc(rtrim(base_url(), '/')) ?>"
    data-user-role="admin"
    data-form-mode="edit">

    <?= csrf_field() ?>

    <?php
        $initialOwnerType = strtoupper(
            trim(
                (string) (
                    $shipment['owner_type']
                    ?? 'CUSTOMER'
                )
            )
        );

        $initialOwnerId = (int) (
            $shipment['owner_id']
            ?? $shipment['customer_id']
            ?? 0
        );
    ?>

    <input
        type="hidden"
        name="owner_type"
        id="shipment_owner_type"
        value="<?= esc($initialOwnerType) ?>"
    >
    <input
        type="hidden"
        name="owner_id"
        id="shipment_owner_id"
        value="<?= (int) $initialOwnerId ?>"
    >

    <div class="container mt-4 mb-5">
        <div
            class="d-flex flex-column flex-md-row
                   justify-content-between align-items-md-center
                   gap-3 mb-4">

            <div>
                <h2 class="mb-1">
                    Edit Shipment
                </h2>

                <div class="text-muted">
                    AWB:
                    <strong>
                        <?= esc($shipment['awb_no'] ?? '-') ?>
                    </strong>

                    · Status:
                    <strong>
                        <?= esc(
                            $shipment['shipment_status']
                            ?? '-'
                        ) ?>
                    </strong>
                </div>
            </div>

            <a
                href="<?= site_url(
                    'shipments/detail/' . $shipment['id']
                ) ?>"
                class="btn btn-outline-secondary">
                ← Back to Detail
            </a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= esc(
                    session()->getFlashdata('error')
                ) ?>
            </div>
        <?php endif; ?>

        <div class="alert alert-info">
            Semua berat, declared value, pricing,
            vendor total, profit, dan margin akan dihitung
            ulang oleh server ketika shipment disimpan.
        </div>

        <div class="card mb-3">
            <div class="card-header bg-danger fw-bold">
                🏢 Customer / Sender
            </div>

            <div class="card-body">
                <input
                    type="hidden"
                    id="real_customer_id"
                    value="<?= esc(
                        (string) $selectedCustomerId
                    ) ?>">

                <label
                    for="customer_id"
                    class="form-label fw-bold">
                    Customer Sender
                </label>

                <select
                    name="customer_id"
                    id="customer_id"
                    class="form-select"
                    required>

                    <option value="">
                        -- Select Customer / Sender --
                    </option>

                    <?php foreach (
                        $senderAddresses as $sender
                    ): ?>

                        <?php
                            $senderId   = (int) ($sender['id'] ?? 0);
                            $customerId = (int) ($sender['customer_id'] ?? 0);

                            $contactName = trim(
                                (string) ($sender['contact_name'] ?? '')
                            );

                            $companyName = trim(
                                (string) ($sender['company_name'] ?? '')
                            );

                            $optionLabel = $contactName;

                            if (
                                $companyName !== ''
                                && strcasecmp(
                                    $companyName,
                                    $contactName
                                ) !== 0
                            ) {
                                $optionLabel .=
                                    ' — ' . $companyName;
                            }

                            $isSelected =
                                old('sender_address_id', $selectedSenderId)
                                    == $senderId;
                        ?>

                        <option
                            value="<?= $customerId ?>"

                            data-customer-id="<?= $customerId ?>"
                            data-address-id="<?= $senderId ?>"
                            data-contact-name="<?= esc($contactName, 'attr') ?>"
                            data-company="<?= esc($companyName, 'attr') ?>"
                            data-phone="<?= esc($sender['phone'] ?? '', 'attr') ?>"
                            data-email="<?= esc($sender['email'] ?? '', 'attr') ?>"
                            data-identity="<?= esc(
                                $sender['identity_number'] ?? '',
                                'attr'
                            ) ?>"
                            data-address="<?= esc($sender['address'] ?? '', 'attr') ?>"
                            data-state="<?= esc($sender['state'] ?? '', 'attr') ?>"
                            data-city="<?= esc($sender['city'] ?? '', 'attr') ?>"
                            data-postal-code="<?= esc(
                                $sender['postal_code'] ?? '',
                                'attr'
                            ) ?>"
                            data-country-id="<?= (int) (
                                $sender['country_id'] ?? 0
                            ) ?>"
                            data-country-code="<?= esc(
                                $sender['country_code'] ?? '',
                                'attr'
                            ) ?>"
                            data-country-name="<?= esc(
                                $sender['country_name'] ?? '',
                                'attr'
                            ) ?>"
                            data-country-zone="<?= esc(
                                $sender['express_zone_code'] ?? '',
                                'attr'
                            ) ?>"

                            <?= $isSelected ? 'selected' : '' ?>

                            <?= esc($optionLabel) ?>
                        </option>

                    <?php endforeach; ?>
                </select>

                <input
                    type="hidden"
                    name="sender_address_id"
                    id="sender_address_id"
                    value="<?= esc(
                        old('sender_address_id', $selectedSenderId)
                    ) ?>">

                <div class="form-text">
                    Ganti Sender melalui dropdown ini. Detail di bawah adalah
                    preview Address Book dan sengaja readonly.
                </div>

                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input
                            id="sender_company"
                            class="form-control"
                            value="<?= esc(
                                $shipment['sender_company']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input
                            id="sender_phone"
                            class="form-control"
                            value="<?= esc(
                                $shipment['sender_phone']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input
                            id="sender_email"
                            class="form-control"
                            value="<?= esc(
                                $shipment['sender_email']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Identity Number</label>
                        <input
                            id="sender_identity"
                            class="form-control"
                            value="<?= esc(
                                $shipment[
                                    'sender_identity_number'
                                ] ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Province / State</label>
                        <input
                            id="sender_province"
                            class="form-control"
                            value="<?= esc(
                                $shipment['sender_state']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input
                            id="sender_city"
                            class="form-control"
                            value="<?= esc(
                                $shipment['sender_city']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input
                            id="sender_postal_code"
                            class="form-control"
                            value="<?= esc(
                                $shipment[
                                    'sender_postal_code'
                                ] ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Country</label>
                        <input
                            id="sender_country"
                            class="form-control"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Country Code</label>
                        <input
                            id="sender_country_code"
                            class="form-control"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Country Zone</label>
                        <input
                            id="sender_country_zone"
                            class="form-control"
                            readonly>
                    </div>
                </div>

                <label>Complete Address</label>
                <textarea
                    id="sender_address"
                    class="form-control"
                    rows="3"
                    readonly><?= esc(
                        $shipment['sender_address'] ?? ''
                    ) ?></textarea>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning fw-bold">
                👤 Receiver
            </div>

            <div class="card-body">
                <label
                    for="receiver_id"
                    class="form-label fw-bold">
                    Receiver Address
                </label>

                <select
                    id="receiver_id"
                    name="receiver_id"
                    class="form-select"
                    required>

                    <option value="">
                        -- Select Receiver --
                    </option>

                    <?php foreach (
                        $receiverAddresses as $row
                    ): ?>
                        <option
                            value="<?= esc(
                                (string) $row['id']
                            ) ?>"
                            data-customer-id="<?= esc(
                                (string) (
                                    $row['customer_id'] ?? ''
                                )
                            ) ?>"
                            data-country-id="<?= esc(
                                (string) (
                                    $row['country_id'] ?? ''
                                )
                            ) ?>"
                            data-company="<?= esc(
                                $row['company_name'] ?? ''
                            ) ?>"
                            data-phone="<?= esc(
                                $row['phone'] ?? ''
                            ) ?>"
                            data-email="<?= esc(
                                $row['email'] ?? ''
                            ) ?>"
                            data-city="<?= esc(
                                $row['city'] ?? ''
                            ) ?>"
                            data-state="<?= esc(
                                $row['state'] ?? ''
                            ) ?>"
                            data-postal-code="<?= esc(
                                $row['postal_code'] ?? ''
                            ) ?>"
                            data-address="<?= esc(
                                $row['address'] ?? ''
                            ) ?>"
                            data-country="<?= esc(
                                $row['country_name'] ?? ''
                            ) ?>"
                            data-identity="<?= esc(
                                $row['identity_number'] ?? ''
                            ) ?>"
                            <?= (int) $row['id']
                                === $selectedReceiverId
                                ? 'selected'
                                : '' ?>>

                            <?= !empty(
                                $row['owner_customer_name']
                            )
                                ? esc(
                                    $row['owner_customer_name']
                                ) . ' — '
                                : '' ?>

                            <?= esc(
                                $row['contact_name'] ?? '-'
                            ) ?>

                            <?= !empty($row['company_name'])
                                ? ' - ' . esc(
                                    $row['company_name']
                                )
                                : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="form-text">
                    Daftar Receiver otomatis mengikuti customer dari Sender.
                    Detail di bawah adalah preview Address Book dan sengaja readonly.
                </div>

                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input
                            id="receiver_company"
                            class="form-control"
                            value="<?= esc(
                                $shipment['receiver_company']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input
                            id="receiver_phone"
                            class="form-control"
                            value="<?= esc(
                                $shipment['receiver_phone']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input
                            id="receiver_email"
                            class="form-control"
                            value="<?= esc(
                                $shipment['receiver_email']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Identity Number</label>
                        <input
                            id="receiver_identity"
                            class="form-control"
                            value="<?= esc(
                                $shipment[
                                    'receiver_id_number'
                                ] ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Province / State</label>
                        <input
                            id="receiver_province"
                            class="form-control"
                            value="<?= esc(
                                $shipment['receiver_state']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input
                            id="receiver_city"
                            class="form-control"
                            value="<?= esc(
                                $shipment['receiver_city']
                                ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input
                            id="receiver_postal_code"
                            class="form-control"
                            value="<?= esc(
                                $shipment[
                                    'receiver_postal_code'
                                ] ?? ''
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Destination Country</label>

                        <input
                            type="hidden"
                            id="country_id"
                            name="country_id"
                            value="<?= esc(
                                (string) (
                                    $shipment['country_id']
                                    ?? ''
                                )
                            ) ?>">

                        <input
                            id="receiver_country"
                            class="form-control"
                            readonly>
                    </div>
                </div>

                <label>Complete Address</label>
                <textarea
                    id="receiver_address"
                    class="form-control"
                    rows="3"
                    readonly><?= esc(
                        $shipment['receiver_address']
                        ?? ''
                    ) ?></textarea>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                🏢 Acceptance & Receiving
            </div>

            <div class="card-body">
                <input
                    type="hidden"
                    name="acceptance_method"
                    value="<?= esc(
                        $shipment['acceptance_method']
                        ?? \App\Services\ShipmentAcceptanceService::
                            DATA_ENTRY_ONLY
                    ) ?>">

                <input
                    type="hidden"
                    name="receiving_branch_id"
                    value="<?= (int) (
                        $shipment['receiving_branch_id']
                        ?? 0
                    ) ?>">

                <input
                    type="hidden"
                    name="receiving_location"
                    value="<?= esc(
                        $shipment['receiving_location']
                        ?? 'Cartenz System'
                    ) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">
                            Acceptance Method
                        </div>

                        <div class="fw-bold">
                            <?= esc(
                                $shipment['acceptance_method']
                                ?? '-'
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Receiving Branch ID
                        </div>

                        <div class="fw-bold">
                            <?= esc(
                                (string) (
                                    $shipment[
                                        'receiving_branch_id'
                                    ]
                                    ?? '-'
                                )
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Receiving Location
                        </div>

                        <div class="fw-bold">
                            <?= esc(
                                $shipment[
                                    'receiving_location'
                                ]
                                ?? '-'
                            ) ?>
                        </div>
                    </div>
                </div>

                <div class="alert alert-secondary mt-3 mb-0">
                    Acceptance merupakan snapshot operasional.
                    Edit shipment tidak boleh memindahkan branch
                    atau mengubah status penerimaan.
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                📦 Shipment Information
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Service *
                        </label>

                        <select
                            name="service_id"
                            id="service_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Select Service --
                            </option>

                            <?php foreach (
                                $services as $row
                            ): ?>
                                <option
                                    value="<?= esc(
                                        (string) $row['id']
                                    ) ?>"
                                    data-engine="<?= esc(
                                        strtoupper(
                                            $row['service_code']
                                            ?? $row['service_name']
                                            ?? 'REGULAR'
                                        )
                                    ) ?>"
                                    <?= (int) $row['id']
                                        === $selectedServiceId
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc(
                                        $row['service_name']
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Package Type *
                        </label>

                        <select
                            name="package_type_id"
                            id="package_type_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Select Package Type --
                            </option>

                            <?php foreach (
                                $packageTypes as $row
                            ): ?>
                                <option
                                    value="<?= esc(
                                        (string) $row['id']
                                    ) ?>"
                                    <?= (int) $row['id']
                                        === $selectedPackageTypeId
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc(
                                        $row[
                                            'package_type_name'
                                        ]
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Payment Type *
                        </label>

                        <select
                            name="payment_type"
                            id="payment_type"
                            class="form-select"
                            required>

                            <option value="">
                                -- Select Payment --
                            </option>

                            <?php foreach (
                                ['CASH', 'DEPOSIT', 'INVOICE']
                                as $paymentType
                            ): ?>
                                <option
                                    value="<?= esc($paymentType) ?>"
                                    <?= $paymentType
                                        === $selectedPaymentType
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($paymentType) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Commodity Type *
                        </label>

                        <select
                            name="commodity_type_id"
                            id="commodity_type_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Select Commodity --
                            </option>

                            <?php foreach (
                                $commodityTypes as $row
                            ): ?>
                                <option
                                    value="<?= esc(
                                        (string) $row['id']
                                    ) ?>"
                                    <?= (int) $row['id']
                                        === $selectedCommodityId
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc(
                                        $row['commodity_name']
                                    ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Transport Mode *</label>
                        <select name="transport_mode_id" id="transport_mode_id" class="form-select" required>
                            <?php foreach ($transportModes as $mode): ?>
                                <option value="<?= (int) $mode['id'] ?>" <?= $selectedTransportMode === (int) $mode['id'] ? 'selected' : '' ?>>
                                    <?= esc($mode['mode_name'] ?? $mode['mode_code'] ?? ('Mode #' . $mode['id'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Delivery Type *</label>
                        <select name="delivery_type_id" id="delivery_type_id" class="form-select" required>
                            <?php foreach ($deliveryTypes as $delivery): ?>
                                <?php
                                $deliveryLabel = $delivery['delivery_type_name']
                                    ?? $delivery['delivery_name']
                                    ?? $delivery['type_name']
                                    ?? $delivery['name']
                                    ?? $delivery['delivery_code']
                                    ?? ('Delivery #' . $delivery['id']);
                                ?>
                                <option value="<?= (int) $delivery['id'] ?>" <?= $selectedDeliveryType === (int) $delivery['id'] ? 'selected' : '' ?>><?= esc($deliveryLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">
                            Reference Number
                        </label>

                        <input
                            type="text"
                            name="customer_reference_no"
                            id="customer_reference_no"
                            class="form-control"
                            value="<?= esc(
                                (string) $getValue(
                                    'customer_reference_no',
                                    $shipment[
                                        'customer_reference_no'
                                    ] ?? ''
                                )
                            ) ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">
                            Shipment Date
                        </label>

                        <input
                            type="date"
                            name="shipment_date"
                            class="form-control"
                            value="<?= esc(
                                (string) $getValue(
                                    'shipment_date',
                                    $shipment[
                                        'shipment_date'
                                    ] ?? date('Y-m-d')
                                )
                            ) ?>"
                            required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">
                            Expected Delivery
                        </label>

                        <input
                            type="text"
                            id="expected_delivery"
                            class="form-control"
                            placeholder="Auto Calculate"
                            readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">
                            Remarks
                        </label>

                        <textarea
                            name="remarks"
                            class="form-control"
                            rows="3"><?= esc(
                                (string) $getValue(
                                    'remarks',
                                    $shipment['remarks'] ?? ''
                                )
                            ) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning">
                Package / Colly Information
            </div>

            <div class="card-body">
                <button
                    type="button"
                    class="btn btn-success"
                    id="add-colly">
                    + Add Colly
                </button>

                <hr>

                <div id="colly-container">
                    <?php foreach (
                        array_values($formPackages)
                        as $packageIndex => $package
                    ): ?>
                        <?php
                        $items = is_array(
                            $package['items'] ?? null
                        )
                            ? array_values($package['items'])
                            : [];

                        if ($items === []) {
                            $items = [[
                                'item_description' => '',
                                'quantity'         => 1,
                                'currency'         => 'USD',
                                'unit_value'       => 0,
                                'total_value'      => 0,
                            ]];
                        }

                        $packagePrefix =
                            'packages[' .
                            $packageIndex .
                            ']';
                        ?>

                        <div
                            class="card border-primary mb-3"
                            id="colly-<?= $packageIndex + 1 ?>">

                            <div class="card-header">
                                <strong>
                                    COLLY #<?= $packageIndex + 1 ?>
                                </strong>

                                <div class="float-end">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-secondary
                                               me-1 duplicate-colly">
                                        📋 Duplicate
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger
                                               delete-colly">
                                        🗑 Delete
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <?php
                                    $dimensions = [
                                        [
                                            'label' => 'Weight (Kg)',
                                            'key'   => 'actual_weight',
                                            'class' => 'berat',
                                            'step'  => '0.01',
                                        ],
                                        [
                                            'label' => 'Length (Cm)',
                                            'key'   => 'length_cm',
                                            'class' => 'panjang',
                                            'step'  => '0.01',
                                        ],
                                        [
                                            'label' => 'Width (Cm)',
                                            'key'   => 'width_cm',
                                            'class' => 'lebar',
                                            'step'  => '0.01',
                                        ],
                                        [
                                            'label' => 'Height (Cm)',
                                            'key'   => 'height_cm',
                                            'class' => 'tinggi',
                                            'step'  => '0.01',
                                        ],
                                    ];
                                    ?>

                                    <?php foreach (
                                        $dimensions as $dimension
                                    ): ?>
                                        <div class="col-md-3 mb-3">
                                            <label>
                                                <?= esc(
                                                    $dimension['label']
                                                ) ?>
                                            </label>

                                            <input
                                                type="number"
                                                class="form-control
                                                       <?= esc(
                                                           $dimension[
                                                               'class'
                                                           ]
                                                       ) ?>"
                                                name="<?= esc(
                                                    $packagePrefix .
                                                    '[' .
                                                    $dimension['key'] .
                                                    ']'
                                                ) ?>"
                                                step="<?= esc(
                                                    $dimension['step']
                                                ) ?>"
                                                min="0"
                                                value="<?= esc(
                                                    (string) (
                                                        $package[
                                                            $dimension[
                                                                'key'
                                                            ]
                                                        ] ?? 0
                                                    )
                                                ) ?>">
                                        </div>
                                    <?php endforeach; ?>

                                    <div class="col-md-3 mb-3">
                                        <label>Volume Weight</label>

                                        <input
                                            type="text"
                                            class="form-control volume"
                                            name="<?= esc(
                                                $packagePrefix .
                                                '[volume_weight]'
                                            ) ?>"
                                            value="<?= esc(
                                                (string) (
                                                    $package[
                                                        'volume_weight'
                                                    ] ?? 0
                                                )
                                            ) ?>"
                                            readonly>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label>
                                            Chargeable Weight
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control chargeable"
                                            name="<?= esc(
                                                $packagePrefix .
                                                '[chargeable_weight]'
                                            ) ?>"
                                            value="<?= esc(
                                                (string) (
                                                    $package[
                                                        'chargeable_weight'
                                                    ] ?? 0
                                                )
                                            ) ?>"
                                            readonly>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div
                                    class="d-flex justify-content-between
                                           align-items-center mb-2">

                                    <h6 class="mb-0">
                                        Items
                                    </h6>

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success
                                               add-item">
                                        + Add Item
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered
                                               table-sm item-table">

                                        <thead class="table-light">
                                            <tr>
                                                <th width="60">#</th>
                                                <th>Item Description</th>
                                                <th width="100">Qty</th>
                                                <th width="120">
                                                    Currency
                                                </th>
                                                <th width="140">
                                                    Unit Value
                                                </th>
                                                <th width="140">
                                                    Total Value
                                                </th>
                                                <th width="80">
                                                    Action
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach (
                                                $items
                                                as $itemIndex => $item
                                            ): ?>
                                                <?php
                                                $currencyCode = strtoupper(
                                                    (string) (
                                                        $item['currency']
                                                        ?? $item[
                                                            'currency_code'
                                                        ]
                                                        ?? 'USD'
                                                    )
                                                );

                                                $itemPrefix =
                                                    $packagePrefix .
                                                    '[items][' .
                                                    $itemIndex .
                                                    ']';
                                                ?>

                                                <tr>
                                                    <td class="text-center">
                                                        <?= $itemIndex + 1 ?>
                                                    </td>

                                                    <td>
                                                        <input
                                                            type="text"
                                                            class="form-control"
                                                            name="<?= esc(
                                                                $itemPrefix .
                                                                '[item_description]'
                                                            ) ?>"
                                                            value="<?= esc(
                                                                $item[
                                                                    'item_description'
                                                                ] ?? ''
                                                            ) ?>"
                                                            required>
                                                    </td>

                                                    <td>
                                                        <input
                                                            type="number"
                                                            class="form-control qty"
                                                            name="<?= esc(
                                                                $itemPrefix .
                                                                '[quantity]'
                                                            ) ?>"
                                                            value="<?= esc(
                                                                (string) (
                                                                    $item[
                                                                        'quantity'
                                                                    ] ?? 1
                                                                )
                                                            ) ?>"
                                                            min="1"
                                                            required>
                                                    </td>

                                                    <td>
                                                        <select
                                                            class="form-select
                                                                   currency"
                                                            name="<?= esc(
                                                                $itemPrefix .
                                                                '[currency]'
                                                            ) ?>">

                                                            <?php foreach (
                                                                [
                                                                    'USD',
                                                                    'IDR',
                                                                    'SGD',
                                                                    'MYR',
                                                                    'TWD',
                                                                ]
                                                                as $currency
                                                            ): ?>
                                                                <option
                                                                    value="<?= esc(
                                                                        $currency
                                                                    ) ?>"
                                                                    <?= $currency
                                                                        === $currencyCode
                                                                        ? 'selected'
                                                                        : '' ?>>
                                                                    <?= esc(
                                                                        $currency
                                                                    ) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>

                                                    <td>
                                                        <input
                                                            type="number"
                                                            class="form-control
                                                                   unit_value"
                                                            name="<?= esc(
                                                                $itemPrefix .
                                                                '[unit_value]'
                                                            ) ?>"
                                                            step="0.01"
                                                            min="0"
                                                            value="<?= esc(
                                                                (string) (
                                                                    $item[
                                                                        'unit_value'
                                                                    ] ?? 0
                                                                )
                                                            ) ?>">
                                                    </td>

                                                    <td>
                                                        <input
                                                            type="number"
                                                            class="form-control
                                                                   total_value"
                                                            name="<?= esc(
                                                                $itemPrefix .
                                                                '[total_value]'
                                                            ) ?>"
                                                            value="<?= esc(
                                                                (string) (
                                                                    $item[
                                                                        'total_value'
                                                                    ] ?? 0
                                                                )
                                                            ) ?>"
                                                            readonly>
                                                    </td>

                                                    <td class="text-center">
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm
                                                                   btn-danger
                                                                   delete-item">
                                                            🗑
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div
                class="card-header bg-warning
                       d-flex justify-content-between
                       align-items-center">

                <strong>
                    💰 Charges Information
                </strong>

                <button
                    class="btn btn-sm btn-link text-dark
                           text-decoration-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#chargesCollapse">

                    <span id="chargesIcon">▲</span>
                </button>
            </div>

            <div
                class="collapse show"
                id="chargesCollapse">

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Remote Area</label>

                            <select
                                name="is_remote"
                                id="is_remote"
                                class="form-select">

                                <option value="0">
                                    No
                                </option>

                                <option value="1">
                                    Yes
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Customer Insurance</label>
                            <input
                                type="number"
                                id="insurance_customer"
                                name="insurance_customer"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $customerInsurance
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Vendor Insurance</label>
                            <input
                                type="number"
                                id="insurance_vendor"
                                name="insurance_vendor"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $vendorInsurance
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Customer Packing</label>
                            <input
                                type="number"
                                id="packing_customer"
                                name="packing_customer"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $customerPacking
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Vendor Packing</label>
                            <input
                                type="number"
                                id="packing_vendor"
                                name="packing_vendor"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $vendorPacking
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Customer Additional</label>
                            <input
                                type="number"
                                id="additional_customer"
                                name="additional_customer"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $customerAdditional
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Vendor Additional</label>
                            <input
                                type="number"
                                id="additional_vendor"
                                name="additional_vendor"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $vendorAdditional
                                ) ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Discount</label>
                            <input
                                type="number"
                                id="discount_amount"
                                name="discount_amount"
                                class="form-control text-end"
                                min="0"
                                value="<?= esc(
                                    (string) $discountAmount
                                ) ?>">
                        </div>

                        <div class="col-md-3 d-grid align-self-end">
                            <button
                                type="button"
                                id="btnRecalculatePricing"
                                class="btn btn-outline-primary">
                                🔄 Recalculate Pricing
                            </button>
                        </div>
                    </div>

                    <input
                        type="hidden"
                        id="customer_total"
                        name="customer_total"
                        value="<?= esc(
                            (string) $customerTotal
                        ) ?>">

                    <input
                        type="hidden"
                        id="vendor_total_charge"
                        name="vendor_total_charge"
                        value="<?= esc(
                            (string) $vendorTotal
                        ) ?>">

                    <input
                        type="hidden"
                        id="profit_amount"
                        name="profit_amount"
                        value="<?= esc(
                            (string) $profitAmount
                        ) ?>">

                    <input
                        type="hidden"
                        id="selling_amount"
                        value="<?= esc(
                            (string) $systemSubtotal
                        ) ?>">

                    <input
                        type="hidden"
                        id="grand_total"
                        name="grand_total"
                        value="<?= esc(
                            (string) $customerTotal
                        ) ?>">

                    <input
                        type="hidden"
                        id="vendor_total"
                        value="<?= esc(
                            (string) $vendorBaseTotal
                        ) ?>">

                    <table class="table table-bordered mt-4">
                        <tr>
                            <th>Freight Customer</th>
                            <td class="text-end">
                                <span id="shipment_charge_text">
                                    Rp <?= number_format(
                                        (float) (
                                            $shipment[
                                                'freight_charge'
                                            ]
                                            ?? $shipment[
                                                'selling_amount'
                                            ]
                                            ?? 0
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Remote Area Charges</th>
                            <td class="text-end">
                                <span id="remote_charge_text">
                                    Rp <?= number_format(
                                        (float) (
                                            $shipment[
                                                'remote_charge'
                                            ] ?? 0
                                        ),
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Customer Charges</th>
                            <td class="text-end">
                                <span id="other_charge_text">
                                    Rp <?= number_format(
                                        $customerOtherCharges,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </td>
                        </tr>

                        <tr class="table-warning">
                            <th>Customer Total</th>
                            <th class="text-end text-danger">
                                <span id="grand_total_text">
                                    Rp <?= number_format(
                                        $customerTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </th>
                        </tr>

                        <tr>
                            <th>Vendor Total</th>
                            <td class="text-end">
                                <span id="vendor_cost_text">
                                    Rp <?= number_format(
                                        $vendorTotal,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </td>
                        </tr>

                        <tr class="table-success">
                            <th>Profit</th>
                            <th class="text-end">
                                <span id="profit_text">
                                    Rp <?= number_format(
                                        $profitAmount,
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </span>
                            </th>
                        </tr>

                        <tr>
                            <th>Margin</th>
                            <td class="text-end">
                                <span id="margin_text">
                                    <?= number_format(
                                        (float) (
                                            $shipment[
                                                'margin_percent'
                                            ] ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) ?> %
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-dark text-white">
                Shipment Summary
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <label>Total Colly</label>
                        <input
                            type="text"
                            class="form-control"
                            id="total_colly"
                            name="total_colly"
                            value="<?= count($formPackages) ?>"
                            readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Total Weight</label>
                        <input
                            type="text"
                            class="form-control"
                            id="total_berat"
                            name="total_weight"
                            value="<?= esc(
                                (string) (
                                    $shipment[
                                        'actual_weight'
                                    ] ?? 0
                                )
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Declared Value</label>
                        <input
                            type="text"
                            class="form-control"
                            id="total_nilai"
                            name="total_value"
                            value="<?= esc(
                                (string) (
                                    $shipment[
                                        'declared_value'
                                    ] ?? 0
                                )
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>
                            Total Chargeable Weight
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            id="total_chargeable"
                            name="total_chargeable"
                            value="<?= esc(
                                (string) (
                                    $shipment[
                                        'chargeable_weight'
                                    ] ?? 0
                                )
                            ) ?>"
                            readonly>
                    </div>
                </div>

                <div
                    class="d-flex justify-content-end
                           gap-3 mt-2 mb-2">

                    <a
                        href="<?= site_url(
                            'shipments/detail/'
                            . $shipment['id']
                        ) ?>"
                        class="btn btn-secondary px-4 py-2">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary px-4 py-2"
                        id="btnSaveShipment">
                        💾 Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script
    src="https://code.jquery.com/jquery-3.7.1.min.js">
</script>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script src="<?= base_url(
    'js/modules/shipment/utils.js'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/summary.js?v=49.4.2'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/colly.js'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/items.js'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/pricing.js?v=49.7.2'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/charges.js?v=49.7.3'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/profit.js?v=49.7.3'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/commodity-rule.js'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/sender.js?v=49.7.2'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/receiver.js'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/shipment.js?v=49.7.4'
) ?>"></script>

<script src="<?= base_url(
    'js/modules/shipment/edit-shipment.js?v=49.12.1'
) ?>"></script>
</body>
</html>
