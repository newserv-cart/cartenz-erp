<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>


<style>
        #btnSaveSender,
        #btnSaveReceiver {
            display: none !important;
        }
    </style>
<?php
$inboundIntake = $inboundIntake ?? null;
$intakePackages = $intakePackages ?? [];
$automaticReceiving = $automaticReceiving ?? null;

$selectedAcceptanceMethod = old(
    'acceptance_method',
    $automaticReceiving['acceptance_method']
        ?? (
            $inboundIntake
                ? \App\Services\ShipmentAcceptanceService::
                    AGENT_HANDOVER_NO_AWB
                : ''
        )
);

$selectedTransportMode = (int) old(
    'transport_mode_id',
    1
);

$selectedDeliveryType = (int) old(
    'delivery_type_id',
    1
);

$selectedReceivingBranch = (int) old(
    'receiving_branch_id',
    $automaticReceiving['receiving_branch_id']
        ?? $inboundIntake['receiving_branch_id']
        ?? 0
);

$receivingLocationValue = old(
    'receiving_location',
    $automaticReceiving['receiving_location']
        ?? $inboundIntake['receiving_location']
        ?? 'Cartenz Main Warehouse Jakarta'
);
?>

<form
    id="shipmentCreateForm"
    method="post"
    action="<?= site_url('shipments/store') ?>"
    data-base-url="<?= esc(rtrim(base_url(), '/')) ?>"
    data-user-role="admin"
    data-can-choose-any-owner="<?= !empty($canChooseAnyOwner) ? '1' : '0' ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="inbound_intake_id" value="<?= (int) ($inboundIntake['id'] ?? 0) ?>">
    <input type="hidden" name="source_agent_id" value="<?= (int) ($inboundIntake['agent_id'] ?? 0) ?>">

    <input
        type="hidden"
        name="owner_type"
        id="shipment_owner_type"
        value="<?= esc(
            old(
                'owner_type',
                $ownerContext['owner_type'] ?? ''
            )
        ) ?>"
    >
    <input
        type="hidden"
        name="owner_id"
        id="shipment_owner_id"
        value="<?= (int) old(
            'owner_id',
            $ownerContext['owner_id'] ?? 0
        ) ?>"
    >
    <input type="hidden" name="customer_id" id="customer_id" value="<?= (int) old('customer_id', 0) ?>">
    <input type="hidden" name="agent_id" id="agent_id" value="<?= (int) old('agent_id', 0) ?>">

<div class="container mt-4 mb-5">
    <h2 class="mb-4">Create Shipment</h2>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= site_url('inbound-intakes') ?>" class="btn btn-outline-primary">
            📥 Inbound Intake
        </a>
    </div>

    <?php if ($inboundIntake): ?>
        <div class="alert alert-info">
            <strong>Convert Intake <?= esc($inboundIntake['intake_no']) ?></strong><br>
            Agent packages arrived without AWB. Complete customer, sender, receiver,
            item, pricing, then save. Shipment will start at
            <strong>RECEIVED_AT_MAIN_WAREHOUSE</strong>.
        </div>
    <?php endif; ?>

    <!-- =======================================
         SENDER INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-danger fw-bold">
            🏢 Sender Information
        </div>
        <div class="card-body">

            <?php if (!empty($canChooseAnyOwner)): ?>
                <div class="mb-3">
                    <label for="account_owner_search" class="form-label fw-bold">
                        🔎 Cari Customer / Agent
                    </label>

                    <div class="position-relative">
                        <input
                            type="search"
                            id="account_owner_search"
                            class="form-control"
                            placeholder="Ketik nomor account, nama, kota, telepon, atau alamat..."
                            autocomplete="off"
                            required
                        >

                        <div
                            id="account_owner_results"
                            class="list-group position-absolute w-100 shadow d-none"
                            style="z-index:1060;max-height:320px;overflow-y:auto;"
                        ></div>
                    </div>

                    <div
                        id="account_owner_selected"
                        class="alert alert-light border mt-2 d-none"
                    ></div>

                    <div class="form-text">
                        Nomor account hanya ditampilkan pada pencarian. Nama pengirim di bawah boleh diubah khusus untuk AWB ini.
                    </div>
                </div>
            <?php else: ?>
                <?php $activeAccount = $accountOwners[0] ?? null; ?>
                <?php if ($activeAccount): ?>
                    <div class="alert alert-light border mb-3">
                        <strong><?= esc($activeAccount['account_code'] ?? '') ?></strong>
                        &middot; <?= esc($activeAccount['account_name'] ?? '') ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($canChooseAnyOwner)): ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Sender Source</label>

                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="sender_source"
                            id="sender_source_new"
                            value="NEW"
                            <?= old('sender_source', 'NEW') === 'NEW'
                                ? 'checked'
                                : '' ?>
                        >
                        <label class="form-check-label" for="sender_source_new">
                            Input Pengirim Baru
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="sender_source"
                            id="sender_source_history"
                            value="HISTORY"
                            <?= old('sender_source') === 'HISTORY'
                                ? 'checked'
                                : '' ?>
                        >
                        <label class="form-check-label" for="sender_source_history">
                            Pilih dari Riwayat
                        </label>
                    </div>
                </div>
            </div>

            <div id="senderHistorySection" class="d-none">
                <label
                    for="sender_history_search"
                    class="form-label fw-bold"
                >
                    Cari Riwayat Pengirim
                </label>

                <div class="position-relative">
                    <input
                        type="search"
                        id="sender_history_search"
                        class="form-control"
                        placeholder="Ketik nama, perusahaan, telepon, kota, atau alamat..."
                        autocomplete="off"
                    >

                    <input
                        type="hidden"
                        name="sender_address_id"
                        id="sender_address_id"
                        value="<?= esc(
                            old('sender_address_id', '')
                        ) ?>"
                    >

                    <div
                        id="sender_history_results"
                        class="list-group position-absolute w-100 shadow d-none"
                        style="
                            z-index: 1050;
                            max-height: 320px;
                            overflow-y: auto;
                        "
                    ></div>
                </div>

                <div
                    id="sender_history_selected"
                    class="alert alert-light border mt-2 d-none"
                ></div>
            </div>
            <?php else: ?>
                <input type="hidden" name="sender_source" id="sender_source_new" value="NEW">
                <input type="hidden" name="sender_address_id" id="sender_address_id" value="">
            <?php endif; ?>

            <div id="senderNewSection">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input
                            type="text"
                            id="sender_name"
                            name="sender_name"
                            class="form-control"
                            value="<?= esc(old('sender_name') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input
                            type="text"
                            id="sender_company"
                            name="sender_company"
                            class="form-control"
                            value="<?= esc(old('sender_company') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input
                            type="text"
                            id="sender_phone"
                            name="sender_phone"
                            class="form-control"
                            value="<?= esc(old('sender_phone') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input
                            type="email"
                            id="sender_email"
                            name="sender_email"
                            class="form-control"
                            value="<?= esc(old('sender_email') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input
                            type="text"
                            id="sender_identity"
                            name="sender_identity"
                            class="form-control"
                            value="<?= esc(old('sender_identity') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Country *</label>
                        <select
                            id="sender_country"
                            name="sender_country"
                            class="form-select"
                        >
                            <option value="">-- Select Country --</option>
                            <?php foreach ($countries as $country): ?>
                                <option
                                    value="<?= (int) ($country['id'] ?? 0) ?>"
                                    <?= old('sender_country') == ($country['id'] ?? 0)
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($country['country_name'] ?? '') ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Province / State</label>
                        <input
                            type="text"
                            id="sender_province"
                            name="sender_province"
                            class="form-control"
                            value="<?= esc(old('sender_province') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input
                            type="text"
                            id="sender_city"
                            name="sender_city"
                            class="form-control"
                            value="<?= esc(old('sender_city') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input
                            type="text"
                            id="sender_postal"
                            name="sender_postal"
                            class="form-control"
                            value="<?= esc(old('sender_postal') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea
                        id="sender_address"
                        name="sender_address"
                        class="form-control"
                        rows="3"
                    ><?= esc(old('sender_address') ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================================
         RECEIVER INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning">
            👤 Receiver Information
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">Receiver Source</label>

                <div class="d-flex gap-4">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="receiver_source"
                            id="receiver_source_new"
                            value="NEW"
                            checked
                        >
                        <label class="form-check-label" for="receiver_source_new">
                            Input Penerima Baru
                        </label>
                    </div>
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="receiver_source"
                            id="receiver_source_history"
                            value="HISTORY"
                        >
                        <label class="form-check-label" for="receiver_source_history">
                            Pilih dari Riwayat
                        </label>
                    </div>
                </div>
            </div>

            <div id="receiverHistorySection" class="d-none">
                <label
                    for="receiver_history_search"
                    class="form-label fw-bold"
                >
                    Cari Riwayat Penerima
                </label>

                <div class="position-relative">
                    <input
                        type="search"
                        id="receiver_history_search"
                        class="form-control"
                        placeholder="Ketik nama, perusahaan, telepon, kota, atau alamat..."
                        autocomplete="off"
                    >

                    <input
                        type="hidden"
                        name="receiver_address_id"
                        id="receiver_address_id"
                        value="<?= esc(
                            old('receiver_address_id', '')
                        ) ?>"
                    >

                    <div
                        id="receiver_history_results"
                        class="list-group position-absolute w-100 shadow d-none"
                        style="
                            z-index: 1050;
                            max-height: 320px;
                            overflow-y: auto;
                        "
                    ></div>
                </div>

                <div
                    id="receiver_history_selected"
                    class="alert alert-light border mt-2 d-none"
                ></div>
            </div>

            <div id="receiverNewSection">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>Destination Country *</label>
                        <input
                            type="hidden"
                            id="country_id"
                            name="country_id"
                            value="<?= esc(old('country_id') ?? '') ?>"
                        >

                        <select
                            id="receiver_country"
                            name="receiver_country"
                            class="form-select"
                        >
                            <option value="">-- Select Country --</option>
                            <?php foreach ($countries as $country): ?>
                                <option
                                    value="<?= (int) ($country['id'] ?? 0) ?>"
                                >
                                    <?= esc($country['country_name'] ?? '') ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input
                            type="text"
                            id="receiver_name"
                            name="receiver_name"
                            class="form-control"
                            value="<?= esc(old('receiver_name') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input
                            type="text"
                            id="receiver_company"
                            name="receiver_company"
                            class="form-control"
                            value="<?= esc(old('receiver_company') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input
                            type="text"
                            id="receiver_phone"
                            name="receiver_phone"
                            class="form-control"
                            value="<?= esc(old('receiver_phone') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input
                            type="text"
                            id="receiver_identity"
                            name="receiver_identity"
                            class="form-control"
                            value="<?= esc(old('receiver_identity') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Province / District</label>
                        <input
                            type="text"
                            id="receiver_province"
                            name="receiver_province"
                            class="form-control"
                            value="<?= esc(old('receiver_province') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input
                            type="text"
                            id="receiver_city"
                            name="receiver_city"
                            class="form-control"
                            value="<?= esc(old('receiver_city') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input
                            type="text"
                            id="receiver_postal"
                            name="receiver_postal"
                            class="form-control"
                            value="<?= esc(old('receiver_postal') ?? '') ?>"
                        >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input
                            type="email"
                            id="receiver_email"
                            name="receiver_email"
                            class="form-control"
                            value="<?= esc(old('receiver_email') ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea
                        id="receiver_address"
                        name="receiver_address"
                        class="form-control"
                        rows="3"
                    ><?= esc(old('receiver_address') ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            🏢 Acceptance & Receiving
        </div>

        <div class="card-body">
            <?php if ($automaticReceiving): ?>
                <input
                    type="hidden"
                    name="acceptance_method"
                    value="<?= esc(
                        $automaticReceiving[
                            'acceptance_method'
                        ]
                    ) ?>">

                <input
                    type="hidden"
                    name="receiving_branch_id"
                    value="<?= (int) (
                        $automaticReceiving[
                            'receiving_branch_id'
                        ]
                        ?? 0
                    ) ?>">

                <input
                    type="hidden"
                    name="receiving_location"
                    value="<?= esc(
                        $automaticReceiving[
                            'receiving_location'
                        ]
                        ?? ''
                    ) ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">
                            Receiving Point
                        </div>

                        <div class="fw-bold">
                            <?= esc(
                                $automaticReceiving[
                                    'branch_label'
                                ]
                                ?? '-'
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Initial Status
                        </div>

                        <div class="fw-bold text-success">
                            <?= esc(
                                $automaticReceiving[
                                    'initial_state'
                                ]['internal_status']
                                ?? '-'
                            ) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small">
                            Workflow
                        </div>

                        <div class="fw-bold">
                            Automatically determined
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3 mb-0">
                    Lokasi dan status awal ditentukan dari
                    Operational Profile user atau Inbound Intake.
                    Operator tidak perlu memilih manual.
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    User belum mempunyai Operational Profile.
                    Gunakan pilihan manual sementara atau hubungkan
                    user ke branch pada menu Operational Profiles.
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Acceptance Method *
                        </label>

                        <select
                            name="acceptance_method"
                            id="acceptance_method"
                            class="form-select"
                            required>

                            <option value="">
                                -- Select Acceptance Method --
                            </option>

                            <?php foreach (
                                $acceptanceMethods
                                as $value => $label
                            ): ?>
                                <option
                                    value="<?= esc($value) ?>"
                                    <?= $selectedAcceptanceMethod
                                        === $value
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Receiving Branch
                        </label>

                        <select
                            name="receiving_branch_id"
                            class="form-select">

                            <option value="">
                                -- Optional --
                            </option>

                            <?php foreach ($branches as $branch): ?>
                                <option
                                    value="<?= (int) $branch['id'] ?>"
                                    <?= $selectedReceivingBranch
                                        === (int) $branch['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($branch['_label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Receiving Location *
                        </label>

                        <input
                            type="text"
                            name="receiving_location"
                            class="form-control"
                            value="<?= esc(
                                $receivingLocationValue
                            ) ?>"
                            required>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- =======================================
         SHIPMENT INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            📦 Shipment Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Service <span class="text-danger">*</span></label>
                    <select name="service_id" id="service_id" class="form-select">
                        <option value="">-- Select Service --</option>
                        <?php foreach($services as $row): ?>
                            <option
        value="<?= $row['id'] ?>"
        data-engine="<?= esc(strtoupper($row['service_code'] ?? $row['service_name'] ?? 'REGULAR')) ?>">
        <?= esc($row['service_name']) ?>
    </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Package Type <span class="text-danger">*</span></label>
                    <select name="package_type_id" id="package_type_id" class="form-select">
                        <option value="">-- Select Package Type --</option>
                        <?php foreach($packageTypes as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= esc($row['package_type_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select">
                        <option value="">-- Select Payment --</option>
                        <option value="CASH">CASH</option>
                        <option value="DEPOSIT">DEPOSIT</option>
                        <option value="INVOICE">INVOICE</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Commodity Type <span class="text-danger">*</span></label>
                    <select name="commodity_type_id" id="commodity_type_id" class="form-select">
                        <option value="">-- Select Commodity --</option>
                        <?php foreach($commodityTypes as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= esc($row['commodity_name']) ?></option>
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
                            <option value="<?= (int) $delivery['id'] ?>" <?= $selectedDeliveryType === (int) $delivery['id'] ? 'selected' : '' ?>>
                                <?= esc($deliveryLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Reference Number</label>
                    <input type="text" name="customer_reference_no" id="customer_reference_no" class="form-control" placeholder="Customer Reference Number" value="<?= esc(old('customer_reference_no') ?? $inboundIntake['agent_reference_no'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Shipment Date</label>
                    <input type="date" name="shipment_date" value="<?= esc(old('shipment_date') ?? $inboundIntake['arrival_date'] ?? date('Y-m-d')) ?>" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Expected Delivery</label>
                    <input type="text" id="expected_delivery" class="form-control" placeholder="Auto Calculate" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Remarks</label>
                <textarea name="remarks" class="form-control" rows="2"><?= esc(old('remarks') ?? ($inboundIntake ? ('Converted from ' . $inboundIntake['intake_no'] . '. ' . ($inboundIntake['notes'] ?? '')) : '')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- =======================================
         PACKAGE / COLLY INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning">Package / Colly Information</div>
        <div class="card-body">
            <button type="button" class="btn btn-primary me-2">⚡ Generate Multiple Colly</button>
            <button type="button" class="btn btn-success" id="add-colly">+ Add Colly</button>
            <hr>

            <div id="colly-container">
                <!-- Colly #1 -->
                <div class="card border-primary mb-3" id="colly-1">
                    <div class="card-header">
                        <strong>COLLY #1</strong>
                        <div class="float-end">
                            <button type="button" class="btn btn-sm btn-secondary me-1 duplicate-colly">📋 Duplicate</button>
                            <button type="button" class="btn btn-sm btn-danger delete-colly">🗑 Delete</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Weight (Kg)</label>
                                <input type="number" class="form-control berat" name="packages[0][actual_weight]" step="0.01" min="0" value="<?= esc(old('packages.0.actual_weight') ?? $intakePackages[0]['actual_weight'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Length (Cm)</label>
                                <input type="number" class="form-control panjang" name="packages[0][length_cm]" min="0" value="<?= esc(old('packages.0.length_cm') ?? $intakePackages[0]['length_cm'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Width (Cm)</label>
                                <input type="number" class="form-control lebar" name="packages[0][width_cm]" min="0" value="<?= esc(old('packages.0.width_cm') ?? $intakePackages[0]['width_cm'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Height (Cm)</label>
                                <input type="number" class="form-control tinggi" name="packages[0][height_cm]" min="0" value="<?= esc(old('packages.0.height_cm') ?? $intakePackages[0]['height_cm'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Volume Weight</label>
                                <input type="text" class="form-control volume" name="packages[0][volume_weight]" value="0" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Chargeable Weight</label>
                                <input type="text" class="form-control chargeable" name="packages[0][chargeable_weight]" value="0" readonly>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Items</h6>
                            <button type="button" class="btn btn-sm btn-success add-item">+ Add Item</button>
                        </div>

                        <table class="table table-bordered table-sm item-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Item Description</th>
                                    <th width="100">Qty</th>
                                    <th width="120">Currency</th>
                                    <th width="140">Unit Value</th>
                                    <th width="140">Total Value</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td><input type="text" class="form-control" name="packages[0][items][0][item_description]" placeholder="Item Description"></td>
                                    <td><input type="number" class="form-control qty" name="packages[0][items][0][quantity]" value="1" min="1"></td>
                                    <td>
                                        <select class="form-select currency" name="packages[0][items][0][currency]">
                                            <option value="USD" selected>USD</option>
                                            <option value="IDR">IDR</option>
                                            <option value="SGD">SGD</option>
                                            <option value="MYR">MYR</option>
                                            <option value="TWD">TWD</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="packages[0][items][0][unit_value]" class="form-control unit_value" step="0.01" value="0.00"></td>
                                    <td><input type="number" class="form-control total_value" name="packages[0][items][0][total_value]" value="0.00" readonly></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================================
         CHARGES INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
            <strong>💰 Charges Information</strong>
            <button class="btn btn-sm btn-link text-dark text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#chargesCollapse">
                <span id="chargesIcon">▼</span>
            </button>
        </div>

        <div class="collapse" id="chargesCollapse">
            <div class="card-body">
                <div class="row mb-3" id="rowRemoteArea">
                    <label class="col-md-4 col-form-label">Remote Area</label>
                    <div class="col-md-8">
                        <select name="is_remote" id="is_remote" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerInsurance">
                    <div class="col-md-3">
                        <label>Customer Insurance</label>
                        <input type="number" id="insurance_customer" name="insurance_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorInsurance">
                        <label>Vendor Insurance</label>
                        <input type="number" id="insurance_vendor" name="insurance_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerPacking">
                    <div class="col-md-3">
                        <label>Customer Packing</label>
                        <input type="number" id="packing_customer" name="packing_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorPacking">
                        <label>Vendor Packing</label>
                        <input type="number" id="packing_vendor" name="packing_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerAdditional">
                    <div class="col-md-3">
                        <label>Customer Additional</label>
                        <input type="number" id="additional_customer" name="additional_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorAdditional">
                        <label>Vendor Additional</label>
                        <input type="number" id="additional_vendor" name="additional_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowDiscount">
                    <div class="col-md-3">
                        <label>Discount</label>
                        <input type="number" id="discount_amount" name="discount_amount" class="form-control text-end" value="0">
                    </div>
                </div>

                <div id="expressCharges" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Fuel Surcharge</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Go Green</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>VAT</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="customer_total" name="customer_total" value="0">
                <input type="hidden" id="vendor_total_charge" name="vendor_total_charge" value="0">
                <input type="hidden" id="profit_amount" name="profit_amount" value="0">
                <input type="hidden" id="selling_amount" value="0">
                <input type="hidden" id="grand_total" name="grand_total" value="0">
                <input type="hidden" id="vendor_total" value="0">

                <table class="table table-bordered mt-4">
                    <tr>
                        <th>Freight Customer</th>
                        <td class="text-end"><span id="shipment_charge_text">Rp 0</span></td>
                    </tr>
                    <tr>
                        <th>Remote Area Charges</th>
                        <td class="text-end"><span id="remote_charge_text">Rp 0</span></td>
                    </tr>
                    <tr>
                        <th>Customer Charges</th>
                        <td class="text-end"><span id="other_charge_text">Rp 0</span></td>
                    </tr>
                    <tr class="table-warning">
                        <th>Customer Total</th>
                        <th class="text-end text-danger"><span id="grand_total_text">Rp 0</span></th>
                    </tr>
                    <tr>
                        <th>Vendor Total</th>
                        <td class="text-end"><span id="vendor_cost_text">Rp 0</span></td>
                    </tr>
                    <tr class="table-success">
                        <th>Profit</th>
                        <th class="text-end"><span id="profit_text">Rp 0</span></th>
                    </tr>
                    <tr>
                        <th>Margin</th>
                        <td class="text-end"><span id="margin_text">0 %</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <a href="#" id="btnPricingAnalysis" class="btn btn-outline-primary">📈 Pricing Analysis</a>
    </div>

    <!-- =======================================
         SHIPMENT SUMMARY & ACTIONS
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">Shipment Summary</div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label>Total Colly</label>
                    <input type="text" class="form-control" id="total_colly" name="total_colly" value="1" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Total Weight</label>
                    <input type="text" class="form-control" id="total_berat" name="total_weight" value="1.00" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Declared Value</label>
                    <input type="text" class="form-control" id="total_nilai" name="total_value" value="0.00" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Total Chargeable Weight</label>
                    <input type="text" class="form-control" id="total_chargeable" name="total_chargeable" value="1.00" readonly>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-2 mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2">💾 Save Shipment</button>
                <button type="reset" class="btn btn-secondary px-4 py-2">🔄 Reset</button>
            </div>
        </div>
    </div>

</div>

</form>

<!-- =======================================
     MODAL ADD NEW SENDER
======================================= -->
<div class="modal fade" id="senderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">➕ Add New Sender</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <?php
                    $defaultSenderCountry = null;

                    foreach ($countries as $c) {
                        if (($c['country_code'] ?? '') === 'ID') {
                            $defaultSenderCountry = $c;
                            break;
                        }
                    }

                    if ($defaultSenderCountry === null) {
                        $defaultSenderCountry = $countries[0] ?? null;
                    }
                ?>

                <input
                    type="hidden"
                    id="new_sender_country_id"
                    value="<?= (int) ($defaultSenderCountry['id'] ?? 0) ?>">

                <input
                    type="hidden"
                    id="new_sender_country_name"
                    value="<?= esc(
                        $defaultSenderCountry['country_name'] ?? 'Indonesia',
                        'attr'
                    ) ?>">

                <input
                    type="hidden"
                    id="new_sender_country_code"
                    value="<?= esc(
                        $defaultSenderCountry['country_code'] ?? 'ID',
                        'attr'
                    ) ?>">

                <input
                    type="hidden"
                    id="new_sender_country_zone"
                    value="<?= esc(
                        $defaultSenderCountry['express_zone_code'] ?? '',
                        'attr'
                    ) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input type="text" id="new_sender_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input type="text" id="new_sender_company" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input type="text" id="new_sender_phone" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="new_sender_email" class="form-control" placeholder="email@example.com">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input type="text" id="new_sender_identity" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Province / State</label>
                        <input type="text" id="new_sender_province" class="form-control" placeholder="e.g. West Java">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input type="text" id="new_sender_city" class="form-control" placeholder="e.g. Bekasi">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input type="text" id="new_sender_postal_code" class="form-control" placeholder="e.g. 17111">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea id="new_sender_address" rows="3" class="form-control" placeholder="Street name, building, etc."></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSender">💾 Save Sender</button>
            </div>
        </div>
    </div>
</div>
<!-- =======================================
     MODAL ADD NEW RECEIVER
======================================= -->
<div class="modal fade" id="receiverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">➕ Add New Receiver</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>Destination Country *</label>
                        <select id="new_receiver_country" name="country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>"><?= esc($country['country_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input type="text" id="new_receiver_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input type="text" id="new_receiver_company" class="form-control">
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input type="text" id="new_receiver_phone" class="form-control">
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input type="text" id="new_receiver_identity" class="form-control">
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Province / District</label>
                       <input
                        type="text"
                        id="new_receiver_province"
                        class="form-control"
                        placeholder="Province / State">
                    </div>


                    <div class="col-md-6 mb-3">
    <label>City</label>
    <input
        type="text"
        id="new_receiver_city"
        class="form-control"
        placeholder="City">
</div>
</div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input type="text" id="new_receiver_postal" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="new_receiver_email" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea id="new_receiver_address" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveReceiver">💾 Save Receiver</button>
            </div>
        </div>
    </div>
</div>

<!-- =======================================
     SCRIPTS
======================================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="<?= base_url('js/modules/shipment/utils.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/summary.js?v=49.4.2') ?>"></script>
<script src="<?= base_url('js/modules/shipment/colly.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/items.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/pricing.js?v=49.7.2') ?>"></script>
<script src="<?= base_url('js/modules/shipment/charges.js?v=49.7.3') ?>"></script>
<script src="<?= base_url('js/modules/shipment/profit.js?v=49.7.3') ?>"></script>
<script src="<?= base_url('js/modules/shipment/commodity-rule.js') ?>"></script>

<script>
window.shipmentAccountOwners = <?= json_encode(
    $accountOwners ?? [],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) ?>;

window.addressHistorySearchUrl =
    <?= json_encode(
        site_url('customer-addresses/search')
    ) ?>;
</script>

<script src="<?= base_url('js/create-shipment.js?v=50.7') ?>"></script>
<script src="<?= base_url('js/modules/shipment/shipment.js?v=49.7.4') ?>"></script>

<?php if (!empty($intakePackages)): ?>
<script>
window.addEventListener('load', function () {
    const packages = <?= json_encode($intakePackages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const addButton = document.getElementById('add-colly');

    for (let index = 1; index < packages.length; index++) {
        addButton?.click();
    }

    packages.forEach((pkg, index) => {
        const fields = ['actual_weight', 'length_cm', 'width_cm', 'height_cm'];
        fields.forEach((field) => {
            const input = document.querySelector(`[name="packages[${index}][${field}]"]`);
            if (!input) return;
            input.value = pkg[field] ?? 0;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
});
</script>
<?php endif; ?>


<?= $this->endSection() ?>
