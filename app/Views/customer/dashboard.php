<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="mb-1">Welcome, <?= esc($customer['customer_name'] ?? '-') ?></h3>
            <?php
            $accountStatus = strtoupper((string) ($customer['account_status'] ?? 'ACTIVE'));
            $badge = match ($accountStatus) {
                'ACTIVE' => 'bg-success',
                'LOCKED' => 'bg-danger',
                'PENDING' => 'bg-warning text-dark',
                default => 'bg-secondary',
            };
            ?>
            <span class="badge <?= $badge ?>">Status: <?= esc($accountStatus) ?></span>
        </div>
        <a href="<?= site_url('logout') ?>" class="btn btn-danger">🚪 Logout</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if ($accountStatus === 'LOCKED'): ?>
        <div class="alert alert-danger">
            <strong>🔒 Account Locked.</strong>
            Selesaikan invoice overdue sebelum membuat shipment baru.
        </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-primary">📊 Dashboard</a>
        <a href="<?= site_url('customer/create-shipment') ?>" class="btn btn-success <?= $accountStatus !== 'ACTIVE' ? 'disabled' : '' ?>">📦 Create Shipment</a>
        <a href="<?= site_url('customer/my-shipments') ?>" class="btn btn-info">📋 My Shipments</a>
        <a href="<?= site_url('customer/addresses') ?>" class="btn btn-outline-primary">📍 My Addresses</a>
        <a href="<?= site_url('customer/my-invoices') ?>" class="btn btn-warning">🧾 My Invoices</a>
        <a href="<?= site_url('customer/my-payments') ?>" class="btn btn-secondary">💳 My Payments</a>
    </div>

    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['📦 Total Shipment', $totalShipments ?? 0, 'primary'],
            ['🧾 Total Invoice', $totalInvoices ?? 0, 'success'],
            ['❌ Unpaid', $unpaidInvoices ?? 0, 'warning'],
            ['💳 Payments', $totalPayments ?? 0, 'info'],
        ];
        ?>
        <?php foreach ($cards as [$title, $value, $color]): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 border-<?= $color ?> shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-<?= $color ?>"><?= esc($title) ?></h6>
                        <div class="display-6 fw-bold"><?= number_format((int) $value) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>📦 Recent Shipments</strong>
            <a href="<?= site_url('customer/my-shipments') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>AWB</th>
                        <th>Date</th>
                        <th>Internal Status</th>
                        <th>Public Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentShipments)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">No recent shipments found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentShipments as $shipment): ?>
                            <tr>
                                <td><strong><?= esc($shipment['awb_no'] ?? '-') ?></strong></td>
                                <td><?= !empty($shipment['created_at']) ? date('d-m-Y H:i', strtotime($shipment['created_at'])) : '-' ?></td>
                                <td><span class="badge bg-secondary"><?= esc($shipment['shipment_status'] ?? 'DRAFT') ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($shipment['public_tracking_status'] ?? 'CREATED') ?></span></td>
                                <td class="text-end"><a href="<?= site_url('customer/shipment/' . (int) $shipment['id']) ?>" class="btn btn-sm btn-outline-primary">Detail</a></td>
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
