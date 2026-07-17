<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📥 Inbound Intake</h3>
            <div class="text-muted">Barang agen tiba tanpa AWB / resi.</div>
        </div>

        <a href="<?= site_url('inbound-intakes/create') ?>" class="btn btn-primary">
            ➕ Receive Unregistered Packages
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Intake No</th>
                            <th>Arrival</th>
                            <th>Agent</th>
                            <th>Location</th>
                            <th class="text-end">Colly</th>
                            <th class="text-end">Weight</th>
                            <th>Status</th>
                            <th>Shipment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($intakes)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">Belum ada Inbound Intake.</td></tr>
                        <?php else: ?>
                            <?php foreach ($intakes as $row): ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($row['intake_no']) ?></td>
                                    <td><?= esc($row['arrival_date']) ?></td>
                                    <td>
                                        <?= esc($row['agent_name'] ?? '-') ?>
                                        <?php if (!empty($row['agent_code'])): ?>
                                            <div class="small text-muted"><?= esc($row['agent_code']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($row['receiving_location'] ?? '-') ?></td>
                                    <td class="text-end"><?= (int) ($row['total_colly'] ?? 0) ?></td>
                                    <td class="text-end"><?= number_format((float) ($row['received_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                                    <td><span class="badge bg-info text-dark"><?= esc($row['intake_status']) ?></span></td>
                                    <td>
                                        <?php if (!empty($row['converted_shipment_id'])): ?>
                                            <a href="<?= site_url('shipments/detail/' . $row['converted_shipment_id']) ?>">#<?= (int) $row['converted_shipment_id'] ?></a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><a href="<?= site_url('inbound-intakes/' . $row['id']) ?>" class="btn btn-sm btn-primary">Open</a></td>
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
