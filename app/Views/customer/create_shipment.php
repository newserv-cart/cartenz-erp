<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Shipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .section-title { font-size: 1rem; font-weight: 700; }
        .required::after { content: " *"; color: #dc3545; }
        .summary-value { font-size: 1.1rem; font-weight: 700; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📦 Create Shipment</h3>
            <div class="text-muted">
                Customer: <?= esc($customer['customer_name'] ?? '-') ?>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('customer/addresses') ?>" class="btn btn-outline-primary">
                📍 My Addresses
            </a>
            <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-outline-secondary">
                Dashboard
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                <div><?= esc($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    $hasAddresses = !empty($senderAddresses) && !empty($receiverAddresses);
    $oldHandover = old('planned_handover_method') ?: 'COURIER_PICKUP';
    ?>

    <?php if (!$hasAddresses): ?>
        <div class="alert alert-warning">
            Kamu perlu minimal satu alamat <strong>Sender</strong> dan satu alamat
            <strong>Receiver</strong> sebelum membuat shipment.
            <a href="<?= site_url('customer/addresses/create') ?>" class="alert-link">
                Tambah alamat sekarang
            </a>.
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('customer/create-shipment') ?>" id="customerShipmentForm">
        <?= csrf_field() ?>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">🏢 Sender & Receiver</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">Sender Address</label>
                        <select name="sender_address_id" class="form-select" required>
                            <option value="">-- Select Sender --</option>
                            <?php foreach ($senderAddresses as $address): ?>
                                <?php
                                $label = trim(
                                    (string) ($address['contact_name'] ?? '') .
                                    (!empty($address['company_name'])
                                        ? ' — ' . $address['company_name']
                                        : '')
                                );
                                ?>
                                <option
                                    value="<?= (int) $address['id'] ?>"
                                    <?= (string) old('sender_address_id') === (string) $address['id'] ? 'selected' : '' ?>
                                >
                                    <?= esc($label) ?> · <?= esc($address['city'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">Receiver Address</label>
                        <select name="receiver_address_id" class="form-select" required>
                            <option value="">-- Select Receiver --</option>
                            <?php foreach ($receiverAddresses as $address): ?>
                                <?php
                                $label = trim(
                                    (string) ($address['contact_name'] ?? '') .
                                    (!empty($address['company_name'])
                                        ? ' — ' . $address['company_name']
                                        : '')
                                );
                                ?>
                                <option
                                    value="<?= (int) $address['id'] ?>"
                                    <?= (string) old('receiver_address_id') === (string) $address['id'] ? 'selected' : '' ?>
                                >
                                    <?= esc($label) ?> · <?= esc($address['country_name'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Negara tujuan otomatis mengikuti alamat receiver.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">🚚 Shipment Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">Service</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">-- Select Service --</option>
                            <?php foreach ($services as $option): ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('service_id') === (string) $option['id'] ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Self-service saat ini memakai Regular pricing.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Transport Mode</label>
                        <select name="transport_mode_id" class="form-select" required>
                            <option value="">-- Select Mode --</option>
                            <?php foreach ($transportModes as $option): ?>
                                <?php
                                $defaultAir = old('transport_mode_id') === null
                                    && stripos($option['label'], 'air') !== false;
                                ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('transport_mode_id') === (string) $option['id'] || $defaultAir ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Delivery Type</label>
                        <select name="delivery_type_id" class="form-select" required>
                            <option value="">-- Select Delivery --</option>
                            <?php foreach ($deliveryTypes as $option): ?>
                                <?php
                                $defaultDoor = old('delivery_type_id') === null
                                    && stripos(str_replace('_', ' ', $option['label']), 'door to door') !== false;
                                ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('delivery_type_id') === (string) $option['id'] || $defaultDoor ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Package Type</label>
                        <select name="package_type_id" class="form-select" required>
                            <option value="">-- Select Package --</option>
                            <?php foreach ($packageTypes as $option): ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('package_type_id') === (string) $option['id'] ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">Commodity</label>
                        <select name="commodity_type_id" class="form-select" required>
                            <option value="">-- Select Commodity --</option>
                            <?php foreach ($commodityTypes as $option): ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('commodity_type_id') === (string) $option['id'] ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Type</label>
                        <input class="form-control" value="<?= esc($paymentType) ?>" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input
                            type="text"
                            name="customer_reference_no"
                            class="form-control"
                            maxlength="100"
                            value="<?= esc(old('customer_reference_no')) ?>"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">📦 Package — 1 Colly</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label required">Actual Weight (Kg)</label>
                        <input type="number" min="0.01" step="0.01" name="actual_weight" id="actualWeight" class="form-control" required value="<?= esc(old('actual_weight')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Length (Cm)</label>
                        <input type="number" min="0.01" step="0.01" name="length_cm" id="lengthCm" class="form-control" required value="<?= esc(old('length_cm')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Width (Cm)</label>
                        <input type="number" min="0.01" step="0.01" name="width_cm" id="widthCm" class="form-control" required value="<?= esc(old('width_cm')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Height (Cm)</label>
                        <input type="number" min="0.01" step="0.01" name="height_cm" id="heightCm" class="form-control" required value="<?= esc(old('height_cm')) ?>">
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <div class="text-muted">Volume Weight (/5000)</div>
                            <div class="summary-value"><span id="volumeWeight">0.00</span> Kg</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <div class="text-muted">Estimated Chargeable Weight</div>
                            <div class="summary-value"><span id="chargeableWeight">0.00</span> Kg</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">🧾 Item Declaration</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label required">Item Description</label>
                        <input type="text" name="item_description" class="form-control" required maxlength="255" value="<?= esc(old('item_description')) ?>" placeholder="Example: Shirts">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label required">Qty</label>
                        <input type="number" min="1" step="1" name="item_quantity" id="itemQuantity" class="form-control" required value="<?= esc(old('item_quantity') ?: 1) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input type="text" name="item_unit_type" class="form-control" maxlength="20" value="<?= esc(old('item_unit_type') ?: 'PCS') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label required">Currency</label>
                        <select name="currency_id" class="form-select" required>
                            <option value="">-- Currency --</option>
                            <?php foreach ($currencies as $option): ?>
                                <?php
                                $defaultUsd = old('currency_id') === null
                                    && strtoupper((string) ($option['code'] ?? '')) === 'USD';
                                ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('currency_id') === (string) $option['id'] || $defaultUsd ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required">Unit Value</label>
                        <input type="number" min="0" step="0.01" name="item_unit_value" id="itemUnitValue" class="form-control" required value="<?= esc(old('item_unit_value')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Declared Value</label>
                        <input type="text" id="declaredValue" class="form-control" value="0.00" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">🤝 Handover Method</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check border rounded p-3 ps-5 h-100">
                            <input class="form-check-input" type="radio" name="planned_handover_method" id="handoverPickup" value="COURIER_PICKUP" <?= $oldHandover === 'COURIER_PICKUP' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="handoverPickup">🚚 Courier Pickup</label>
                            <div class="text-muted small">Kurir mengubah status menjadi PICKED_UP saat AWB dipindai.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check border rounded p-3 ps-5 h-100">
                            <input class="form-check-input" type="radio" name="planned_handover_method" id="handoverDropoff" value="CUSTOMER_DROP_OFF_BRANCH" <?= $oldHandover === 'CUSTOMER_DROP_OFF_BRANCH' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="handoverDropoff">🏢 Drop-off at CARTENZ Location</label>
                            <div class="text-muted small">Lokasi aktual tetap mengikuti branch petugas yang melakukan scan.</div>
                        </div>
                    </div>

                    <div class="col-md-6" id="pickupFields">
                        <label class="form-label">Preferred Pickup Date</label>
                        <input type="date" name="pickup_date" class="form-control" value="<?= esc(old('pickup_date') ?: date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-6" id="pickupTimeField">
                        <label class="form-label">Preferred Pickup Time</label>
                        <input type="time" name="pickup_time" class="form-control" value="<?= esc(old('pickup_time')) ?>">
                    </div>

                    <div class="col-12" id="dropoffFields">
                        <label class="form-label required">Planned Drop-off Location</label>
                        <select name="planned_dropoff_branch_id" id="dropoffBranch" class="form-select">
                            <option value="">-- Select CARTENZ Location --</option>
                            <?php foreach ($dropoffBranches as $option): ?>
                                <option
                                    value="<?= (int) $option['id'] ?>"
                                    <?= (string) old('planned_dropoff_branch_id') === (string) $option['id'] ? 'selected' : '' ?>
                                ><?= esc($option['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white section-title">📝 Remarks</div>
            <div class="card-body">
                <textarea name="remarks" class="form-control" rows="3" maxlength="2000"><?= esc(old('remarks')) ?></textarea>
            </div>
        </div>

        <div class="alert alert-info">
            Setelah disimpan, status shipment tetap <strong>CREATED</strong>.
            Status baru berubah setelah kurir atau petugas branch memindai AWB.
        </div>

        <button type="submit" class="btn btn-primary btn-lg" <?= !$hasAddresses ? 'disabled' : '' ?>>
            📦 Create Shipment
        </button>
    </form>
</div>

<script>
(function () {
    const numberValue = (id) => {
        const element = document.getElementById(id);
        const value = parseFloat(element?.value || '0');
        return Number.isFinite(value) ? value : 0;
    };

    const calculatePackage = () => {
        const actual = numberValue('actualWeight');
        const volume = (
            numberValue('lengthCm') *
            numberValue('widthCm') *
            numberValue('heightCm')
        ) / 5000;

        document.getElementById('volumeWeight').textContent = volume.toFixed(2);
        document.getElementById('chargeableWeight').textContent = Math.max(actual, volume).toFixed(2);
    };

    const calculateDeclared = () => {
        const qty = numberValue('itemQuantity');
        const unit = numberValue('itemUnitValue');
        document.getElementById('declaredValue').value = (qty * unit).toFixed(2);
    };

    ['actualWeight', 'lengthCm', 'widthCm', 'heightCm'].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', calculatePackage);
    });

    ['itemQuantity', 'itemUnitValue'].forEach((id) => {
        document.getElementById(id)?.addEventListener('input', calculateDeclared);
    });

    const pickup = document.getElementById('handoverPickup');
    const dropoff = document.getElementById('handoverDropoff');
    const pickupFields = document.getElementById('pickupFields');
    const pickupTimeField = document.getElementById('pickupTimeField');
    const dropoffFields = document.getElementById('dropoffFields');
    const dropoffBranch = document.getElementById('dropoffBranch');

    const refreshHandover = () => {
        const isDropoff = Boolean(dropoff?.checked);
        pickupFields.classList.toggle('d-none', isDropoff);
        pickupTimeField.classList.toggle('d-none', isDropoff);
        dropoffFields.classList.toggle('d-none', !isDropoff);
        dropoffBranch.required = isDropoff;
    };

    pickup?.addEventListener('change', refreshHandover);
    dropoff?.addEventListener('change', refreshHandover);

    calculatePackage();
    calculateDeclared();
    refreshHandover();
})();
</script>
</body>
</html>
