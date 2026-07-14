<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Shipment List 🚚</h3>

        <a
            href="<?= site_url('shipments/create') ?>"
            class="btn btn-primary">
            + Create Shipment
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc(session()->getFlashdata('success')) ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc(session()->getFlashdata('error')) ?>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>AWB</th>
                            <th>Colly</th>
                            <th>Weight</th>
                            <th>Chargeable</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th style="min-width: 260px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($shipments as $row): ?>
                            <?php
                            $status = strtoupper(
                                (string) (
                                    $row['shipment_status']
                                    ?? 'DRAFT'
                                )
                            );

                            $color = match ($status) {
                                'DELIVERED', 'CLOSED' => 'success',
                                'CANCELLED',
                                'RETURN_TO_SENDER' => 'danger',
                                'IN_TRANSIT',
                                'IN_TRANSIT_HUB',
                                'SENT_TO_MAIN_WAREHOUSE',
                                'OUT_FOR_DELIVERY' => 'warning',
                                'PICKED_UP',
                                'ARRIVED_HUB',
                                'MANIFESTED' => 'info',
                                default => 'secondary',
                            };
                            ?>

                            <tr>
                                <td><?= (int) $row['id'] ?></td>

                                <td>
                                    <strong>
                                        <?= esc($row['awb_no'] ?? '-') ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= (int) ($row['total_colly'] ?? 0) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) (
                                            $row['actual_weight']
                                            ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) (
                                            $row['chargeable_weight']
                                            ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <?= number_format(
                                        (float) (
                                            $row['declared_value']
                                            ?? 0
                                        ),
                                        2,
                                        ',',
                                        '.'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="badge bg-<?= esc($color) ?>">
                                        <?= esc($status) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a
                                            href="<?= site_url(
                                                'shipments/detail/' .
                                                (int) $row['id']
                                            ) ?>"
                                            class="btn btn-sm btn-info">
                                            👁 Detail
                                        </a>

                                        <?php
                                        $actionShipment = $row;
                                        $actionState =
                                            $row['_actions'] ?? [];
                                        ?>

                                        <?= $this->include(
                                            'shipments/partials/lifecycle_actions'
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($shipments)): ?>
                            <tr>
                                <td
                                    colspan="8"
                                    class="text-center text-muted">
                                    Belum ada shipment.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?= $this->include(
        'shipments/partials/lifecycle_modals'
    ) ?>
</div>

<?= $this->endSection() ?>
