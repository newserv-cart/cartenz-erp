<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$summary = $preview['summary'];
$rows = array_slice($preview['rows'], 0, 120);
?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">🔎 Express Rate Import Preview</h3>
            <div class="text-muted">
                <?= esc($vendor['vendor_name']) ?>
                · <?= esc($service['service_name'] ?? '-') ?>
                · <?= esc($currency['_label'] ?? '-') ?>
            </div>
        </div>

        <a
            href="<?= site_url(
                'master/vendors/' .
                $vendor['id'] .
                '/express-import'
            ) ?>"
            class="btn btn-outline-secondary">
            Upload Another File
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($preview['warnings'])): ?>
        <div class="alert alert-warning">
            <strong>Warnings:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (array_slice($preview['warnings'], 0, 20) as $warning): ?>
                    <li><?= esc($warning) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($preview['conflicts'])): ?>
        <div class="alert alert-danger">
            Mode APPEND menemukan
            <strong><?= count($preview['conflicts']) ?></strong>
            kombinasi Type + Zone + Weight yang sudah ada.
            Import diblokir. Upload ulang dengan mode REPLACE atau ubah data.
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <?php
        $cards = [
            'Total Rows' => number_format($summary['total_rows']),
            'DOCUMENT' => number_format($summary['document_rows']),
            'PACKAGE' => number_format($summary['package_rows']),
            'Zones' => implode(', ', $summary['zones']),
            'Weight Range' => number_format($summary['minimum_weight'], 2, ',', '.')
                . ' – '
                . number_format($summary['maximum_weight'], 2, ',', '.')
                . ' Kg',
            'Price Range' => 'Rp '
                . number_format($summary['minimum_price'], 0, ',', '.')
                . ' – Rp '
                . number_format($summary['maximum_price'], 0, ',', '.'),
        ];
        ?>

        <?php foreach ($cards as $label => $value): ?>
            <div class="col-md-4 col-xl-2">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="text-muted small">
                            <?= esc($label) ?>
                        </div>
                        <div class="fw-bold">
                            <?= esc($value) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">File</div>
                    <div class="fw-semibold">
                        <?= esc($preview['original_filename']) ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Format</div>
                    <div class="fw-semibold">
                        <?= esc($preview['source_format']) ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Sheet</div>
                    <div class="fw-semibold">
                        <?= esc($preview['sheet_name']) ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="text-muted small">Effective Date</div>
                    <div class="fw-semibold">
                        <?= esc($preview['effective_date']) ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Mode</div>
                    <div class="fw-semibold">
                        <?= esc($preview['import_mode']) ?>
                        <?= $preview['existing_header_id']
                            ? '· Existing Header #' . (int) $preview['existing_header_id']
                            : '· New Header' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-bold">
            Preview Data — menampilkan maksimal 120 baris pertama
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 560px;">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead class="table-dark sticky-top">
                        <tr>
                            <th>Source Row</th>
                            <th>Shipment Type</th>
                            <th>Zone</th>
                            <th class="text-end">Weight</th>
                            <th class="text-end">Price IDR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= (int) $row['source_row'] ?></td>
                                <td><?= esc($row['shipment_type']) ?></td>
                                <td><?= (int) $row['zone'] ?></td>
                                <td class="text-end">
                                    <?= number_format(
                                        (float) $row['weight'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                                <td class="text-end">
                                    Rp <?= number_format(
                                        (float) $row['price'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form
        method="post"
        action="<?= site_url(
            'master/vendors/' .
            $vendor['id'] .
            '/express-import/commit'
        ) ?>"
        onsubmit="return confirm(
            'Import <?= number_format($summary['total_rows']) ?> tarif Express ke database?'
        );">

        <?= csrf_field() ?>

        <input
            type="hidden"
            name="token"
            value="<?= esc($preview['token']) ?>">

        <button
            type="submit"
            class="btn btn-success btn-lg"
            <?= empty($preview['can_import']) ? 'disabled' : '' ?>>
            Confirm Transactional Import
        </button>
    </form>
</div>

<?= $this->endSection() ?>
