<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$rate = $rate ?? [];
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Regular Vendor Rate</h3>
            <div class="text-muted">
                <?= esc($vendor['vendor_name']) ?>
            </div>
        </div>

        <a
            href="<?= site_url(
                'master/vendors/' . $vendor['id']
            ) ?>"
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

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $selects = [
                        'country_id' => [
                            'label' => 'Country',
                            'rows' => $countries,
                            'text' => 'country_name',
                        ],
                        'service_id' => [
                            'label' => 'Service',
                            'rows' => $services,
                            'text' => 'service_name',
                        ],
                        'transport_mode_id' => [
                            'label' => 'Transport Mode',
                            'rows' => $transportModes,
                            'text' => array_key_exists(
                                'mode_name',
                                $transportModes[0] ?? []
                            ) ? 'mode_name' : 'id',
                        ],
                        'delivery_type_id' => [
                            'label' => 'Delivery Type',
                            'rows' => $deliveryTypes,
                            'text' => array_key_exists(
                                'delivery_name',
                                $deliveryTypes[0] ?? []
                            ) ? 'delivery_name' : 'id',
                        ],
                    ];
                    ?>

                    <?php foreach ($selects as $field => $config): ?>
                        <div class="col-md-6">
                            <label class="form-label">
                                <?= esc($config['label']) ?> *
                            </label>

                            <select
                                class="form-select"
                                name="<?= esc($field) ?>"
                                required>

                                <option value="">-- Select --</option>

                                <?php foreach ($config['rows'] as $row): ?>
                                    <?php
                                    $selected = (string) (
                                        old($field)
                                        ?? $rate[$field]
                                        ?? ''
                                    );
                                    ?>

                                    <option
                                        value="<?= (int) $row['id'] ?>"
                                        <?= $selected === (string) $row['id']
                                            ? 'selected'
                                            : '' ?>>
                                        <?= esc((string) $row[$config['text']]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>

                    <?php
                    $numberFields = [
                        'weight_from'    => 'Weight From',
                        'weight_to'      => 'Weight To',
                        'cost_per_kg'    => 'Cost Per Kg',
                        'minimum_charge' => 'Minimum Charge',
                    ];
                    ?>

                    <?php foreach ($numberFields as $field => $label): ?>
                        <div class="col-md-3">
                            <label class="form-label">
                                <?= esc($label) ?> *
                            </label>

                            <input
                                type="number"
                                class="form-control"
                                name="<?= esc($field) ?>"
                                min="0"
                                step="0.01"
                                value="<?= esc(
                                    old($field)
                                    ?? $rate[$field]
                                    ?? 0
                                ) ?>"
                                required>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-md-4">
                        <label class="form-label">Effective From *</label>
                        <input
                            type="date"
                            class="form-control"
                            name="effective_from"
                            value="<?= esc(
                                old('effective_from')
                                ?? $rate['effective_from']
                                ?? date('Y-m-d')
                            ) ?>"
                            required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Effective Until</label>
                        <input
                            type="date"
                            class="form-control"
                            name="effective_until"
                            value="<?= esc(
                                old('effective_until')
                                ?? $rate['effective_until']
                                ?? ''
                            ) ?>">
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="status"
                                name="status"
                                value="1"
                                <?= (
                                    old('status')
                                    ?? $rate['status']
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

                <div class="alert alert-info mt-3 mb-0">
                    Vendor freight dihitung:
                    <strong>
                        max(Chargeable Weight × Cost Per Kg,
                        Minimum Charge)
                    </strong>.
                </div>
            </div>

            <div class="card-footer">
                <button
                    type="submit"
                    class="btn btn-success">
                    Save Regular Rate
                </button>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
