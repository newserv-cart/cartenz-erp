<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Shipments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📦 My Shipments</h3>
            <div class="text-muted"><?= esc($customer['customer_name'] ?? '-') ?></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('customer/create-shipment') ?>" class="btn btn-success">➕ Create Shipment</a>
            <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-outline-secondary">Dashboard</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>AWB</th>
                        <th>Date</th>
                        <th>Weight</th>
                        <th>Internal</th>
                        <th>Public</th>
                        <th>Handover Plan</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($shipments)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada shipment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($shipments as $shipment): ?>
                            <tr>
                                <td><strong><?= esc($shipment['awb_no'] ?? '-') ?></strong></td>
                                <td><?= esc($shipment['shipment_date'] ?? '-') ?></td>
                                <td><?= number_format((float) ($shipment['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                                <td><span class="badge bg-secondary"><?= esc($shipment['shipment_status'] ?? 'DRAFT') ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($shipment['public_tracking_status'] ?? 'CREATED') ?></span></td>
                                <td><?= esc($shipment['planned_handover_method'] ?? '-') ?></td>
                                <td class="text-end"><a href="<?= site_url('customer/shipment/' . (int) $shipment['id']) ?>" class="btn btn-sm btn-primary">📍 Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
