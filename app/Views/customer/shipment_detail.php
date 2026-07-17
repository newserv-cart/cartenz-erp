<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($shipment['awb_no'] ?? 'Shipment') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📦 <?= esc($shipment['awb_no'] ?? '-') ?></h3>
            <span class="badge bg-secondary"><?= esc($shipment['shipment_status'] ?? '-') ?></span>
            <span class="badge bg-light text-dark border"><?= esc($shipment['public_tracking_status'] ?? '-') ?></span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('customer/my-shipments') ?>" class="btn btn-outline-secondary">My Shipments</a>
            <a href="<?= site_url('track/' . ($shipment['awb_no'] ?? '')) ?>" class="btn btn-outline-primary" target="_blank" rel="noopener">Public Tracking</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted">Chargeable Weight</div>
                <div class="fs-4 fw-bold"><?= number_format((float) ($shipment['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted">Customer Total</div>
                <div class="fs-4 fw-bold">Rp <?= number_format((float) ($shipment['total_charge'] ?? 0), 0, ',', '.') ?></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted">Planned Handover</div>
                <div class="fs-5 fw-bold"><?= esc($shipment['planned_handover_method'] ?? '-') ?></div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Shipment Information</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><strong>Sender</strong><br><?= esc($shipment['sender_name'] ?? '-') ?><br><span class="text-muted"><?= nl2br(esc($shipment['sender_address'] ?? '-')) ?></span></div>
                <div class="col-md-6"><strong>Receiver</strong><br><?= esc($shipment['receiver_name'] ?? '-') ?><br><span class="text-muted"><?= nl2br(esc($shipment['receiver_address'] ?? '-')) ?></span></div>
                <div class="col-md-3"><strong>Reference</strong><br><?= esc($shipment['customer_reference_no'] ?? '-') ?></div>
                <div class="col-md-3"><strong>Actual Weight</strong><br><?= number_format((float) ($shipment['actual_weight'] ?? 0), 2, ',', '.') ?> Kg</div>
                <div class="col-md-3"><strong>Volume Weight</strong><br><?= number_format((float) ($shipment['volume_weight'] ?? 0), 2, ',', '.') ?> Kg</div>
                <div class="col-md-3"><strong>Declared Value</strong><br><?= number_format((float) ($shipment['declared_value'] ?? 0), 2, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white"><strong>Packages & Items</strong></div>
        <div class="card-body">
            <?php foreach ($packages as $package): ?>
                <div class="border rounded p-3 mb-2">
                    <strong>Colly #<?= (int) ($package['package_no'] ?? 1) ?></strong>
                    · <?= number_format((float) ($package['actual_weight'] ?? 0), 2, ',', '.') ?> Kg
                    · <?= number_format((float) ($package['length_cm'] ?? 0), 2, ',', '.') ?> × <?= number_format((float) ($package['width_cm'] ?? 0), 2, ',', '.') ?> × <?= number_format((float) ($package['height_cm'] ?? 0), 2, ',', '.') ?> Cm
                </div>
            <?php endforeach; ?>

            <div class="table-responsive mt-3">
                <table class="table table-bordered mb-0">
                    <thead class="table-light"><tr><th>Description</th><th>Qty</th><th>Unit Value</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php if (empty($items)): ?>
                        <tr><td colspan="4" class="text-center text-muted">No items.</td></tr>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= esc($item['item_description'] ?? '-') ?></td>
                                <td><?= number_format((float) ($item['quantity'] ?? 0), 0, ',', '.') ?></td>
                                <td><?= number_format((float) ($item['unit_value'] ?? 0), 2, ',', '.') ?></td>
                                <td><?= number_format((float) ($item['total_value'] ?? 0), 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white"><strong>📍 Tracking Timeline</strong></div>
        <div class="card-body">
            <?php if (empty($tracking)): ?>
                <div class="text-muted">Belum ada tracking.</div>
            <?php else: ?>
                <?php foreach ($tracking as $row): ?>
                    <div class="border-start border-4 border-primary ps-3 py-2 mb-3 bg-light rounded-end">
                        <div class="fw-bold"><?= esc($row['tracking_status'] ?? '-') ?></div>
                        <div class="small text-muted"><?= esc($row['public_status'] ?? '-') ?> · <?= esc($row['tracking_location'] ?? '-') ?></div>
                        <div><?= esc($row['tracking_notes'] ?? '') ?></div>
                        <div class="small text-muted mt-1"><?= esc($row['event_time'] ?? '-') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
