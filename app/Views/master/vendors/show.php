<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">
                🏢 <?= esc($vendor['vendor_name']) ?>
            </h3>

            <span class="badge bg-primary">
                <?= esc($vendor['vendor_code']) ?>
            </span>

            <span class="badge bg-<?= (int) ($vendor['status'] ?? 0) === 1
                ? 'success'
                : 'secondary' ?>">
                <?= (int) ($vendor['status'] ?? 0) === 1
                    ? 'ACTIVE'
                    : 'INACTIVE' ?>
            </span>
        </div>

        <div class="d-flex gap-2">
            <a
                href="<?= site_url(
                    'master/vendors/edit/' . $vendor['id']
                ) ?>"
                class="btn btn-warning">
                Edit Vendor
            </a>

            <a
                href="<?= site_url('master/vendors') ?>"
                class="btn btn-outline-secondary">
                Back
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Category</div>
                    <div class="fw-semibold">
                        <?= esc($vendor['vendor_category'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Type</div>
                    <div class="fw-semibold">
                        <?= esc($vendor['vendor_type'] ?? '-') ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Priority</div>
                    <div class="fw-semibold">
                        <?= (int) ($vendor['vendor_priority'] ?? 0) ?>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">API Available</div>
                    <div class="fw-semibold">
                        <?= (int) ($vendor['api_available'] ?? 0) === 1
                            ? 'Yes'
                            : 'No' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold">Regular Vendor Rates</span>

            <a
                href="<?= site_url(
                    'master/vendors/' .
                    $vendor['id'] .
                    '/regular-rates/create'
                ) ?>"
                class="btn btn-sm btn-primary">
                Add Regular Rate
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Service</th>
                            <th>Mode / Delivery</th>
                            <th>Weight</th>
                            <th class="text-end">Cost/Kg</th>
                            <th class="text-end">Minimum</th>
                            <th>Effective</th>
                            <th>Status</th>
                            <th width="150">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($regularRates)): ?>
                            <tr>
                                <td
                                    colspan="9"
                                    class="text-center text-muted py-4">
                                    Belum ada Regular Vendor Rate.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($regularRates as $rate): ?>
                                <tr>
                                    <td>
                                        <?= esc($rate['country_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($rate['service_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($rate['transport_mode_name'] ?? $rate['transport_mode_id']) ?>
                                        /
                                        <?= esc($rate['delivery_type_name'] ?? $rate['delivery_type_id']) ?>
                                    </td>
                                    <td>
                                        <?= number_format(
                                            (float) $rate['weight_from'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                        –
                                        <?= number_format(
                                            (float) $rate['weight_to'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) $rate['cost_per_kg'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) $rate['minimum_charge'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= esc($rate['effective_from']) ?>
                                        –
                                        <?= esc($rate['effective_until'] ?? 'Open') ?>
                                    </td>
                                    <td>
                                        <?= (int) $rate['status'] === 1
                                            ? 'ACTIVE'
                                            : 'INACTIVE' ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a
                                                href="<?= site_url(
                                                    'master/vendors/' .
                                                    $vendor['id'] .
                                                    '/regular-rates/edit/' .
                                                    $rate['id']
                                                ) ?>"
                                                class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <form
                                                method="post"
                                                action="<?= site_url(
                                                    'master/vendors/' .
                                                    $vendor['id'] .
                                                    '/regular-rates/delete/' .
                                                    $rate['id']
                                                ) ?>"
                                                onsubmit="return confirm(
                                                    'Hapus Regular Vendor Rate ini?'
                                                );">

                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold">Express Rate Headers</span>

            <div class="d-flex gap-2">
                <a
                    href="<?= site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-import'
                    ) ?>"
                    class="btn btn-sm btn-success">
                    Upload Excel
                </a>

                <a
                    href="<?= site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-rates/create'
                    ) ?>"
                    class="btn btn-sm btn-primary">
                    Add Express Header
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Effective Date</th>
                            <th>Currency</th>
                            <th>Details</th>
                            <th>Source</th>
                            <th>Remarks</th>
                            <th width="160">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($expressHeaders)): ?>
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4">
                                    Belum ada Express Rate Header.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($expressHeaders as $header): ?>
                                <tr>
                                    <td>
                                        <?= esc($header['service_name'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= esc($header['effective_date']) ?>
                                    </td>
                                    <td>
                                        <?= esc($header['currency_label'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= (int) ($header['total_details'] ?? 0) ?>
                                    </td>
                                    <td>
                                        <?= esc($header['import_source'] ?? 'Manual') ?>
                                    </td>
                                    <td>
                                        <?= esc($header['remarks'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a
                                                href="<?= site_url(
                                                    'master/vendors/' .
                                                    $vendor['id'] .
                                                    '/express-rates/' .
                                                    $header['id']
                                                ) ?>"
                                                class="btn btn-sm btn-primary">
                                                Open
                                            </a>

                                            <form
                                                method="post"
                                                action="<?= site_url(
                                                    'master/vendors/' .
                                                    $vendor['id'] .
                                                    '/express-rates/delete/' .
                                                    $header['id']
                                                ) ?>"
                                                onsubmit="return confirm(
                                                    'Hapus header dan seluruh detail Express Rate ini?'
                                                );">

                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
