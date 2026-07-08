<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container-fluid py-4">

    <!-- Header & Logout -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Welcome, <?= esc($customer['customer_name'] ?? '-') ?></h3>
            <small class="text-muted">
                Status: 
                <?php
                $status = $customer['account_status'] ?? 'ACTIVE';
                $statusBadge = match($status) {
                    'ACTIVE' => 'bg-success',
                    'LOCKED' => 'bg-danger',
                    'PENDING' => 'bg-warning',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $statusBadge ?>"><?= esc($status) ?></span>
            </small>
        </div>
        <a href="<?= site_url('logout') ?>" class="btn btn-danger">
            🚪 Logout
        </a>
    </div>

    <!-- Peringatan jika akun terkunci -->
    <?php if (($customer['account_status'] ?? '') === 'LOCKED'): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <strong>🔒 Account Locked</strong><br>
            Please settle overdue invoices before creating new shipments.
        </div>
    <?php endif; ?>

    <!-- Menu Navigasi -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-primary">
            📊 Dashboard
        </a>
        <a href="<?= site_url('customer/create-shipment') ?>" class="btn btn-success">
            📦 Create Shipment
        </a>
        <a href="<?= site_url('customer/my-shipments') ?>" class="btn btn-info">
            📋 My Shipments
        </a>
        <a href="<?= site_url('customer/my-invoices') ?>" class="btn btn-warning">
            🧾 My Invoices
        </a>
        <a href="<?= site_url('customer/my-payments') ?>" class="btn btn-secondary">
            💳 My Payments
        </a>
    </div>

    <!-- Ringkasan Data -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <h5 class="card-title text-primary">📦 Total Shipment</h5>
                    <h2 class="display-6 fw-bold"><?= $totalShipments ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">🧾 Total Invoice</h5>
                    <h2 class="display-6 fw-bold"><?= $totalInvoices ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-warning">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">❌ Unpaid</h5>
                    <h2 class="display-6 fw-bold"><?= $unpaidInvoices ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <h5 class="card-title text-info">💳 Payments</h5>
                    <h2 class="display-6 fw-bold"><?= $totalPayments ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Pengiriman Terbaru -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">📦 Recent Shipments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>AWB Number</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentShipments)): ?>
                            <?php foreach ($recentShipments as $s): ?>


                                
                                <tr>
                                    <td><?= esc($s['awb_no'] ?? '-') ?></td>
                                    <td>
                                        <?= !empty($s['created_at']) 
                                            ? date('d-m-Y', strtotime($s['created_at'])) 
                                            : '-' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $s['shipment_status'] ?? 'DRAFT';
                                        $statusClass = match($status) {
                                            'DRAFT' => 'bg-secondary',
                                            'DELIVERED' => 'bg-success',
                                            'IN TRANSIT' => 'bg-warning text-dark',
                                            'PENDING' => 'bg-info',
                                            default => 'bg-light text-dark border'
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= esc($status) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No recent shipments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>