<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📦 Manifest List</h3>
            <div class="text-muted">
                Workflow: OPEN → DISPATCHED → RECEIVED → CLOSED
            </div>
        </div>

        <div class="d-flex gap-2">
            <a
                href="<?= site_url('manifests/scan') ?>"
                class="btn btn-outline-success">
                📱 Scan Manifest
            </a>

            <a
                href="<?= site_url('manifests/create') ?>"
                class="btn btn-primary">
                ➕ Create Manifest
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

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Manifest No</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Shipment</th>
                            <th class="text-end">Weight</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($manifests)): ?>
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4">
                                    Belum ada manifest.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($manifests as $manifest): ?>
                                <?php
                                $status = strtoupper(
                                    (string) ($manifest['status'] ?? 'OPEN')
                                );

                                $badge = match ($status) {
                                    'DISPATCHED' => 'warning',
                                    'RECEIVED'   => 'success',
                                    'CLOSED'     => 'dark',
                                    default      => 'primary',
                                };
                                ?>

                                <tr>
                                    <td><?= (int) $manifest['id'] ?></td>
                                    <td class="fw-semibold">
                                        <?= esc($manifest['manifest_no']) ?>
                                    </td>
                                    <td>
                                        <?= esc($manifest['manifest_date']) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= esc($badge) ?>">
                                            <?= esc($status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (int) ($manifest['total_shipment'] ?? 0)
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) ($manifest['total_weight'] ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </td>
                                    <td>
                                        <a
                                            href="<?= site_url(
                                                'manifests/view/' .
                                                $manifest['id']
                                            ) ?>"
                                            class="btn btn-sm btn-primary">
                                            Open
                                        </a>
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
