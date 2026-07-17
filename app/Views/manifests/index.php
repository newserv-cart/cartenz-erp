<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">📦 Manifest List</h3>
            <div class="text-muted">
                Workflow: OPEN → DISPATCHED → RECEIVED → CLOSED
            </div>
        </div>

        <div class="d-flex gap-2">
            <a
                href="<?= site_url('operations/scan') ?>"
                class="btn btn-outline-primary">
                📱 Shipment Scan
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
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Status</th>
                            <th class="text-end">Shipment</th>
                            <th class="text-end">Weight</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($manifests)): ?>
                            <tr>
                                <td
                                    colspan="9"
                                    class="text-center text-muted py-4">
                                    Belum ada manifest.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($manifests as $manifest): ?>
                                <tr>
                                    <td>
                                        <?= (int) $manifest['id'] ?>
                                    </td>
                                    <td class="fw-semibold">
                                        <?= esc($manifest['manifest_no']) ?>
                                    </td>
                                    <td>
                                        <?= esc($manifest['manifest_date']) ?>
                                    </td>
                                    <td>
                                        <?= esc(
                                            $manifest['origin_branch_name']
                                            ?? (
                                                !empty(
                                                    $manifest[
                                                        'origin_branch_id'
                                                    ]
                                                )
                                                    ? '#' .
                                                        $manifest[
                                                            'origin_branch_id'
                                                        ]
                                                    : 'LEGACY'
                                            )
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= esc(
                                            $manifest[
                                                'destination_branch_name'
                                            ]
                                            ?? (
                                                !empty(
                                                    $manifest[
                                                        'destination_branch_id'
                                                    ]
                                                )
                                                    ? '#' .
                                                        $manifest[
                                                            'destination_branch_id'
                                                        ]
                                                    : 'LEGACY'
                                            )
                                        ) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= esc($manifest['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?= (int) $manifest['total_shipment'] ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) $manifest['total_weight'],
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
