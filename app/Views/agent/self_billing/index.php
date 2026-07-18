<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">💳 Tagihan Agen & Self Billing</h3>
            <div class="text-muted">
                <?= esc($agent['agent_code'] ?? '-') ?> · <?= esc($agent['agent_name'] ?? '-') ?>
            </div>
        </div>
        <a href="<?= site_url('agent/dashboard') ?>" class="btn btn-outline-secondary">← Agent Dashboard</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted">Belum Ditagihkan</div>
            <h4>Rp <?= number_format((float) ($summary['unbilled'] ?? 0), 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted">Outstanding</div>
            <h4>Rp <?= number_format((float) ($summary['outstanding'] ?? 0), 0, ',', '.') ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted">Menunggu Approval</div>
            <h4><?= number_format((int) ($summary['pending_approval'] ?? 0)) ?></h4>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body">
            <div class="text-muted">Total Lunas</div>
            <h4>Rp <?= number_format((float) ($summary['paid'] ?? 0), 0, ',', '.') ?></h4>
        </div></div></div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Shipment Belum Ditagihkan</h5></div>
        <div class="card-body">
            <div class="alert alert-light border">
                Agen hanya memilih AWB. Nominal dihitung dan dikunci oleh server.
            </div>

            <form method="post" action="<?= site_url('agent/self-billing/create') ?>"
                  onsubmit="return confirm('Buat Self Billing dari shipment terpilih?');">
                <?= csrf_field() ?>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="checkAllShipments"></th>
                                <th>AWB</th><th>Date</th><th>Status</th><th>Destination</th>
                                <th class="text-end">Chargeable</th><th class="text-end">Total Agen</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($shipments === []): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">
                                Belum ada shipment yang dapat dibuatkan Self Billing.
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($shipments as $shipment): ?>
                                <tr>
                                    <td><input type="checkbox" name="shipment_ids[]"
                                               value="<?= (int) ($shipment['id'] ?? 0) ?>"
                                               class="js-shipment-check"></td>
                                    <td><strong><?= esc($shipment['awb_no'] ?? '-') ?></strong></td>
                                    <td><?= esc($shipment['shipment_date'] ?? '-') ?></td>
                                    <td><span class="badge bg-info text-dark"><?= esc($shipment['shipment_status'] ?? '-') ?></span></td>
                                    <td><?= esc($shipment['receiver_city'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float) ($shipment['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                                    <td class="text-end">Rp <?= number_format((float) ($shipment['total_charge'] ?? 0), 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($shipments !== []): ?>
                    <button type="submit" class="btn btn-primary">🧾 Buat Invoice dari AWB Terpilih</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light"><h5 class="mb-0">Agent Invoices</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr>
                        <th>Invoice</th><th>Date</th><th>Due Date</th><th>Approval</th><th>Status</th>
                        <th class="text-end">Total</th><th class="text-end">Balance</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                    <?php if ($invoices === []): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada agent invoice.</td></tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= esc($invoice['invoice_no'] ?? '-') ?></td>
                                <td><?= esc($invoice['invoice_date'] ?? '-') ?></td>
                                <td><?= esc($invoice['due_date'] ?? '-') ?></td>
                                <td><?= esc($invoice['approval_status'] ?? '-') ?></td>
                                <td><?= esc($invoice['invoice_status'] ?? '-') ?></td>
                                <td class="text-end">Rp <?= number_format((float) ($invoice['total_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td class="text-end">Rp <?= number_format((float) ($invoice['balance_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td><a href="<?= site_url('agent/self-billing/invoice/' . (int) ($invoice['id'] ?? 0)) ?>"
                                       class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAllShipments');
    if (!checkAll) return;
    checkAll.addEventListener('change', function () {
        document.querySelectorAll('.js-shipment-check').forEach(function (box) {
            box.checked = checkAll.checked;
        });
    });
});
</script>

<?= $this->endSection() ?>
