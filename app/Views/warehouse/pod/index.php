<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-1">
            📸 Proof of Delivery
        </h3>

        <div class="text-muted">
            Catat dan lihat bukti penerimaan shipment.
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>AWB</th>
                            <th>Vendor AWB</th>
                            <th>Destination</th>
                            <th class="text-end">Chargeable</th>
                            <th>Status POD</th>
                            <th>Delivered To</th>
                            <th>Delivered At</th>
                            <th style="width: 130px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($shipments)): ?>

                            <tr>
                                <td
                                    colspan="8"
                                    class="text-center py-5 text-muted"
                                >
                                    Belum ada shipment DELIVERED.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($shipments as $shipment): ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= esc(
                                                $shipment['awb_no'] ?? '-'
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['vendor_awb'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['country_name'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) (
                                                $shipment[
                                                    'chargeable_weight'
                                                ] ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </td>

                                    <td>
                                        <?php if (
                                            !empty($shipment['pod_id'])
                                        ): ?>
                                            <span class="badge bg-success">
                                                RECORDED
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                PENDING
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['delivered_to'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['delivered_at'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="<?= site_url(
                                                'warehouse/pod/'
                                                . $shipment['id']
                                            ) ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            <?= !empty($shipment['pod_id'])
                                                ? 'View POD'
                                                : 'Record POD' ?>
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
