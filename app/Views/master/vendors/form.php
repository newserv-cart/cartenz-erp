<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$vendor = $vendor ?? [];
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><?= esc($formTitle) ?></h3>

        <a
            href="<?= site_url('master/vendors') ?>"
            class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form
        method="post"
        action="<?= esc($formAction) ?>">

        <?= csrf_field() ?>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light fw-bold">
                Vendor Identity
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vendor Code *</label>
                        <input
                            type="text"
                            class="form-control"
                            name="vendor_code"
                            maxlength="30"
                            value="<?= esc(
                                old('vendor_code')
                                ?? $vendor['vendor_code']
                                ?? ''
                            ) ?>"
                            required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Vendor Name *</label>
                        <input
                            type="text"
                            class="form-control"
                            name="vendor_name"
                            maxlength="150"
                            value="<?= esc(
                                old('vendor_name')
                                ?? $vendor['vendor_name']
                                ?? ''
                            ) ?>"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <input
                            type="text"
                            class="form-control"
                            name="vendor_category"
                            value="<?= esc(
                                old('vendor_category')
                                ?? $vendor['vendor_category']
                                ?? ''
                            ) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <input
                            type="text"
                            class="form-control"
                            name="vendor_type"
                            value="<?= esc(
                                old('vendor_type')
                                ?? $vendor['vendor_type']
                                ?? ''
                            ) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <input
                            type="number"
                            class="form-control"
                            name="vendor_priority"
                            min="0"
                            value="<?= esc(
                                old('vendor_priority')
                                ?? $vendor['vendor_priority']
                                ?? 0
                            ) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Base Country</label>
                        <select
                            class="form-select"
                            name="country_id">

                            <option value="">-- Global / Not Set --</option>

                            <?php foreach ($countries as $country): ?>
                                <?php
                                $selectedCountry = (string) (
                                    old('country_id')
                                    ?? $vendor['country_id']
                                    ?? ''
                                );
                                ?>

                                <option
                                    value="<?= (int) $country['id'] ?>"
                                    <?= $selectedCountry === (string) $country['id']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($country['country_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Payment Term Days</label>
                        <input
                            type="number"
                            class="form-control"
                            name="payment_term_days"
                            min="0"
                            value="<?= esc(
                                old('payment_term_days')
                                ?? $vendor['payment_term_days']
                                ?? 0
                            ) ?>">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-4">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="api_available"
                                name="api_available"
                                value="1"
                                <?= (
                                    old('api_available')
                                    ?? $vendor['api_available']
                                    ?? 0
                                ) ? 'checked' : '' ?>>

                            <label
                                for="api_available"
                                class="form-check-label">
                                API Available
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="status"
                                name="status"
                                value="1"
                                <?= (
                                    old('status')
                                    ?? $vendor['status']
                                    ?? 1
                                ) ? 'checked' : '' ?>>

                            <label
                                for="status"
                                class="form-check-label">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light fw-bold">
                Contact & Finance
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $fields = [
                        'contact_person' => 'Contact Person',
                        'phone'          => 'Phone',
                        'email'          => 'Email',
                        'website'        => 'Website',
                        'tracking_url'   => 'Tracking URL',
                    ];
                    ?>

                    <?php foreach ($fields as $field => $label): ?>
                        <div class="col-md-6">
                            <label class="form-label">
                                <?= esc($label) ?>
                            </label>

                            <input
                                type="<?= $field === 'email' ? 'email' : 'text' ?>"
                                class="form-control"
                                name="<?= esc($field) ?>"
                                value="<?= esc(
                                    old($field)
                                    ?? $vendor[$field]
                                    ?? ''
                                ) ?>">
                        </div>
                    <?php endforeach; ?>

                    <div class="col-md-3">
                        <label class="form-label">Credit Limit</label>
                        <input
                            type="number"
                            class="form-control"
                            name="credit_limit"
                            min="0"
                            step="0.01"
                            value="<?= esc(
                                old('credit_limit')
                                ?? $vendor['credit_limit']
                                ?? 0
                            ) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Deposit Balance</label>
                        <input
                            type="number"
                            class="form-control"
                            name="deposit_balance"
                            min="0"
                            step="0.01"
                            value="<?= esc(
                                old('deposit_balance')
                                ?? $vendor['deposit_balance']
                                ?? 0
                            ) ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea
                            class="form-control"
                            name="address"
                            rows="3"><?= esc(
                                old('address')
                                ?? $vendor['address']
                                ?? ''
                            ) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea
                            class="form-control"
                            name="notes"
                            rows="3"><?= esc(
                                old('notes')
                                ?? $vendor['notes']
                                ?? ''
                            ) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            Save Vendor
        </button>
    </form>
</div>

<?= $this->endSection() ?>
