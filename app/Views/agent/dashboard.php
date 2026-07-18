<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">🏢 <?= esc($agent['agent_name'] ?? 'Agent Dashboard') ?></h3>
            <div class="text-muted"><?= esc($agent['agent_code'] ?? '-') ?></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('shipments') ?>" class="btn btn-success">📦 Shipments</a>
            <a href="<?= site_url('manifests') ?>" class="btn btn-info">📋 Manifests</a>
            <a href="<?= site_url('agent/self-billing') ?>" class="btn btn-primary">💳 Tagihan Agen</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center">
            <div class="text-muted">Customers</div><h2><?= number_format((int) $totalCustomers) ?></h2>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center">
            <div class="text-muted">Shipments</div><h2><?= number_format((int) $totalShipments) ?></h2>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center">
            <div class="text-muted">Agent Invoices</div><h2><?= number_format((int) $totalInvoices) ?></h2>
        </div></div></div>
        <div class="col-md-3"><div class="card shadow-sm border-0"><div class="card-body text-center">
            <div class="text-muted">Outstanding</div>
            <h4>Rp <?= number_format((float) $outstanding, 0, ',', '.') ?></h4>
        </div></div></div>
    </div>
</div>

<?= $this->endSection() ?>
