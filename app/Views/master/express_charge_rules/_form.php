<?php
// Fungsi bantu untuk mengambil nilai dengan aman
function valx($rule, $field, $default = '')
{
    return isset($rule[$field]) ? $rule[$field] : $default;
}

// Pastikan $rule selalu berupa array
$rule = $rule ?? [];
?>

<?= csrf_field() ?>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Country</label>
        <select name="country_id" class="form-select" required>
            <option value="">Select Country</option>
            <?php foreach($countries as $c): ?>
                <option value="<?= $c['id'] ?>"
                    <?= valx($rule, 'country_id') == $c['id'] ? 'selected' : '' ?>>
                    <?= esc($c['country_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label>Service</label>
        <select name="service_id" class="form-select" required>
            <option value="">Select Service</option>
            <?php foreach($services as $s): ?>
                <option value="<?= $s['id'] ?>"
                    <?= valx($rule, 'service_id') == $s['id'] ? 'selected' : '' ?>>
                    <?= esc($s['service_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<hr>

<div class="row">
    <!-- Fuel Surcharge -->
    <div class="col-md-4">
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning">🔥 Fuel Surcharge</div>
            <div class="card-body">
                <label>Customer Type</label>
                <select name="customer_fuel_type" class="form-select mb-2">
                    <option value="PERCENT" <?= valx($rule, 'customer_fuel_type') == 'PERCENT' ? 'selected' : '' ?>>Percent</option>
                    <option value="FIXED" <?= valx($rule, 'customer_fuel_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                </select>
                <input type="number" step="0.01" name="customer_fuel_value" class="form-control mb-3" placeholder="Customer Value" value="<?= valx($rule, 'customer_fuel_value', 0) ?>">

                <label>Vendor Type</label>
                <select name="vendor_fuel_type" class="form-select mb-2">
                    <option value="PERCENT" <?= valx($rule, 'vendor_fuel_type') == 'PERCENT' ? 'selected' : '' ?>>Percent</option>
                    <option value="FIXED" <?= valx($rule, 'vendor_fuel_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                </select>
                <input type="number" step="0.01" name="vendor_fuel_value" class="form-control" placeholder="Vendor Value" value="<?= valx($rule, 'vendor_fuel_value', 0) ?>">
            </div>
        </div>
    </div>

    <!-- Go Green -->
    <div class="col-md-4">
        <div class="card border-success mb-3">
            <div class="card-header bg-success text-white">🌱 Go Green</div>
            <div class="card-body">
                <label>Customer Type</label>
                <select name="customer_gogreen_type" class="form-select mb-2">
                    <option value="PER_KG" <?= valx($rule, 'customer_gogreen_type') == 'PER_KG' ? 'selected' : '' ?>>Per Kg</option>
                    <option value="FIXED" <?= valx($rule, 'customer_gogreen_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                </select>
                <input type="number" step="0.01" name="customer_gogreen_value" class="form-control mb-3" value="<?= valx($rule, 'customer_gogreen_value', 0) ?>">

                <label>Vendor Type</label>
                <select name="vendor_gogreen_type" class="form-select mb-2">
                    <option value="PER_KG" <?= valx($rule, 'vendor_gogreen_type') == 'PER_KG' ? 'selected' : '' ?>>Per Kg</option>
                    <option value="FIXED" <?= valx($rule, 'vendor_gogreen_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                </select>
                <input type="number" step="0.01" name="vendor_gogreen_value" class="form-control" value="<?= valx($rule, 'vendor_gogreen_value', 0) ?>">
            </div>
        </div>
    </div>

    <!-- Remote Area -->
    <div class="col-md-4">
        <div class="card border-danger mb-3">
            <div class="card-header bg-danger text-white">📍 Remote Area</div>
            <div class="card-body">
                <label>Customer Type</label>
                <select name="customer_remote_type" class="form-select mb-2">
                    <option value="FIXED" <?= valx($rule, 'customer_remote_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                    <option value="PERCENT" <?= valx($rule, 'customer_remote_type') == 'PERCENT' ? 'selected' : '' ?>>Percent</option>
                </select>
                <input type="number" step="0.01" name="customer_remote_value" class="form-control mb-3" value="<?= valx($rule, 'customer_remote_value', 0) ?>">

                <label>Vendor Type</label>
                <select name="vendor_remote_type" class="form-select mb-2">
                    <option value="FIXED" <?= valx($rule, 'vendor_remote_type') == 'FIXED' ? 'selected' : '' ?>>Fixed</option>
                    <option value="PERCENT" <?= valx($rule, 'vendor_remote_type') == 'PERCENT' ? 'selected' : '' ?>>Percent</option>
                </select>
                <input type="number" step="0.01" name="vendor_remote_value" class="form-control" value="<?= valx($rule, 'vendor_remote_value', 0) ?>">
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-4 mb-3">
        <label>Effective From</label>
        <input type="date" name="effective_from" class="form-control" value="<?= valx($rule, 'effective_from') ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label>Effective To</label>
        <input type="date" name="effective_to" class="form-control" value="<?= valx($rule, 'effective_to') ?>">
    </div>
    <div class="col-md-4 mb-3">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="1" <?= valx($rule, 'status', 1) == 1 ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= valx($rule, 'status') == 0 ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>
</div>