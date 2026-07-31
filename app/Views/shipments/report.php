<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Shipment Report</h3>
            <div class="text-muted">Filter berdasarkan pemilik account dan data operasional.</div>
        </div>
        <a href="<?= site_url('shipments') ?>" class="btn btn-outline-secondary">Shipment List</a>
    </div>

    <form method="get" action="<?= site_url('shipments/report') ?>" class="card shadow-sm mb-4">
        <div class="card-body">
            <input type="hidden" name="owner_type" id="report_owner_type" value="<?= esc($filters['owner_type'] ?? '') ?>">
            <input type="hidden" name="owner_id" id="report_owner_id" value="<?= (int) ($filters['owner_id'] ?? 0) ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Pemilik Account</label>
                    <select id="report_owner_key" class="form-select">
                        <option value="">Semua Account</option>
                        <?php foreach ($accountOwners as $account): ?>
                            <?php $key = ($account['owner_type'] ?? '') . ':' . (int) ($account['owner_id'] ?? 0); ?>
                            <option value="<?= esc($key) ?>"
                                <?= (($filters['owner_type'] ?? '') . ':' . (int) ($filters['owner_id'] ?? 0)) === $key ? 'selected' : '' ?>>
                                <?= esc($account['account_code'] ?? '') ?> | <?= esc($account['account_name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Negara Tujuan</label>
                    <select name="country_id" class="form-select">
                        <option value="">Semua Negara</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= (int) ($country['id'] ?? 0) ?>" <?= (int) ($filters['country_id'] ?? 0) === (int) ($country['id'] ?? 0) ? 'selected' : '' ?>>
                                <?= esc($country['country_name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Vendor</label>
                    <select name="vendor_id" class="form-select">
                        <option value="">Semua Vendor</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= (int) ($vendor['id'] ?? 0) ?>" <?= (int) ($filters['vendor_id'] ?? 0) === (int) ($vendor['id'] ?? 0) ? 'selected' : '' ?>>
                                <?= esc($vendor['display_name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php foreach (['CASH','NOT_INVOICED','UNPAID','PARTIAL','PAID','OVERDUE'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($filters['payment_status'] ?? '') === $status ? 'selected' : '' ?>><?= str_replace('_', ' ', $status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status Tracking</label>
                    <select name="tracking_status" class="form-select">
                        <option value="">Semua Status</option>
                        <?php foreach ($trackingStatuses as $status): ?>
                            <option value="<?= esc($status) ?>" <?= ($filters['tracking_status'] ?? '') === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="<?= site_url('shipments/report') ?>" class="btn btn-outline-secondary">Reset</a>
                    <button type="button" class="btn btn-outline-dark" onclick="window.print()">Print</button>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Shipment</small><h4><?= number_format((int) ($summary['total_shipments'] ?? 0)) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Total Colly</small><h4><?= number_format((int) ($summary['total_colly'] ?? 0)) ?></h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Chargeable Weight</small><h4><?= number_format((float) ($summary['total_weight'] ?? 0), 2, ',', '.') ?> Kg</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><small class="text-muted">Total Charge</small><h4>Rp <?= number_format((float) ($summary['total_charge'] ?? 0), 0, ',', '.') ?></h4></div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th><th>Tanggal</th><th>AWB</th><th>No Account</th><th>Pemilik Account</th><th>Status Pembayaran</th><th>Status Tracking</th><th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($shipments)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Tidak ada data shipment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($shipments as $index => $shipment): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= esc($shipment['shipment_date'] ?? '-') ?></td>
                                <td><a href="<?= site_url('shipments/detail/' . (int) ($shipment['id'] ?? 0)) ?>"><?= esc($shipment['awb_no'] ?? '-') ?></a></td>
                                <td><?= esc($shipment['account_code'] ?? '-') ?></td>
                                <td><?= esc($shipment['account_name'] ?? '-') ?></td>
                                <td><?= esc($shipment['payment_status_label'] ?? '-') ?></td>
                                <td><?= esc($shipment['shipment_status'] ?? '-') ?></td>
                                <td class="text-end">Rp <?= number_format((float) ($shipment['total_charge'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const select = document.getElementById('report_owner_key');
    const type = document.getElementById('report_owner_type');
    const id = document.getElementById('report_owner_id');
    select?.addEventListener('change', function () {
        const parts = this.value ? this.value.split(':') : ['', ''];
        type.value = parts[0] || '';
        id.value = parts[1] || '';
    });
})();
</script>

<?= $this->endSection() ?>
