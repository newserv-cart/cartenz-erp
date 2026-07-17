<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">📥 <?= esc($intake['intake_no']) ?></h3>
            <span class="badge bg-info text-dark"><?= esc($intake['intake_status']) ?></span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('inbound-intakes') ?>" class="btn btn-outline-secondary">Back</a>
            <?php if (($intake['intake_status'] ?? '') === 'READY_TO_CONVERT' && empty($intake['converted_shipment_id'])): ?>
                <a href="<?= site_url('inbound-intakes/' . $intake['id'] . '/start-shipment') ?>" class="btn btn-success">Create Shipment from Intake</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">Agent</div><div class="fw-bold"><?= esc($intake['agent_name'] ?? '-') ?></div><div><?= esc($intake['agent_code'] ?? '') ?></div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">Arrival / Location</div><div class="fw-bold"><?= esc($intake['arrival_date']) ?></div><div><?= esc($intake['receiving_location']) ?></div></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">Received</div><div class="fw-bold"><?= (int) $intake['total_colly'] ?> Colly</div><div><?= number_format((float) $intake['received_weight'], 2, ',', '.') ?> Kg</div></div></div></div>
    </div>

    <?php if (!empty($intake['converted_shipment_id'])): ?>
        <div class="alert alert-success">Converted to Shipment <a href="<?= site_url('shipments/detail/' . $intake['converted_shipment_id']) ?>">#<?= (int) $intake['converted_shipment_id'] ?></a>.</div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header fw-bold">Packages</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead><tr><th>#</th><th>Barcode</th><th class="text-end">Weight</th><th>Dimensions</th><th>Remarks</th></tr></thead>
                    <tbody>
                        <?php foreach ($packages as $package): ?>
                            <tr>
                                <td><?= (int) $package['package_no'] ?></td>
                                <td><?= esc($package['package_barcode'] ?? '-') ?></td>
                                <td class="text-end"><?= number_format((float) $package['actual_weight'], 2, ',', '.') ?> Kg</td>
                                <td><?= number_format((float) $package['length_cm'], 2, ',', '.') ?> × <?= number_format((float) $package['width_cm'], 2, ',', '.') ?> × <?= number_format((float) $package['height_cm'], 2, ',', '.') ?> Cm</td>
                                <td><?= esc($package['remarks'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if (empty($intake['converted_shipment_id']) && ($intake['intake_status'] ?? '') !== 'CANCELLED'): ?>
        <form method="post" action="<?= site_url('inbound-intakes/' . $intake['id'] . '/cancel') ?>" onsubmit="return confirm('Batalkan Inbound Intake ini?');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger">Cancel Intake</button>
        </form>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
