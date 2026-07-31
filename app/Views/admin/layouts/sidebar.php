<?php
$uri = service('uri');
$current = $uri->getPath();

$canManageFinance = false;

try {
    $actorId = (int) (auth()->id() ?? 0);

    if ($actorId > 0) {
        $branchContext =
            new \App\Services\BranchOperationalContextService();

        $profile =
            $branchContext->profileForUser($actorId);

        $canManageFinance =
            is_array($profile)
            && $branchContext->profileIsMain($profile);
    }
} catch (\Throwable $e) {
    $canManageFinance = false;
}
?>

<div class="sidebar">

    <div class="text-center py-4 border-bottom border-secondary">

        <h4 class="text-white">
            🚚 CARTENZ ERP
        </h4>

        <small class="text-light">
            Enterprise Cargo System
        </small>

    </div>

    <div class="py-3">
<div class="menu-title">MAIN</div>

<a href="<?= site_url('admin/dashboard') ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
</a>

<a href="<?= site_url('shipments/create') ?>">
    <i class="bi bi-plus-circle"></i> Create Resi
</a>

<div class="menu-title">MASTER DATA</div>
        <a href="#"><i class="bi bi-building"></i> Company</a>
        <a href="#"><i class="bi bi-diagram-3"></i> Branch</a>
        <a href="#"><i class="bi bi-people"></i> Department</a>
        <a href="#"><i class="bi bi-person"></i> Users</a>
        <a href="#"><i class="bi bi-person-vcard"></i> Customer</a>
        <a href="#"><i class="bi bi-person-badge"></i> Agent</a>


        <div class="menu-title">PRICING</div>

<a href="<?= site_url('master/weight-ranges') ?>">
    <i class="bi bi-rulers"></i> Weight Ranges
</a>

<a href="<?= site_url('master/regular-tariffs') ?>">
    <i class="bi bi-cash-stack"></i> Regular Tariff
</a>

<a href="#">
    <i class="bi bi-airplane"></i> Express Tariff
</a>


<a href="<?= site_url('master/express-rates') ?>">
<i class="bi bi-file-earmark-excel"></i>
Express Rate
</a>


<a href="#">
    <i class="bi bi-box2"></i> Commodity Charges
</a>

        <div class="menu-title">OPERATIONS</div>

<a href="<?= site_url('shipments') ?>">
    <i class="bi bi-box-seam"></i> Shipment
</a>

<a href="<?= site_url('shipments/report') ?>">
    <i class="bi bi-funnel"></i> Shipment Report
</a>

<a href="<?= site_url('manifests') ?>">
    <i class="bi bi-truck"></i> Manifest
</a>

<a href="<?= site_url('operations/pickups') ?>">
    <i class="bi bi-bicycle"></i> Pickup Operations
</a>

        <div class="menu-title">FINANCE</div>

        <a href="<?= site_url('invoices') ?>">
            <i class="bi bi-receipt"></i> Invoice
        </a>

        <a href="<?= site_url('finance/customer-payments') ?>">
            <i class="bi bi-credit-card"></i> Customer Payment
        </a>

        <?php if ($canManageFinance): ?>
        <a href="<?= site_url('finance/agent-payments') ?>">
            <i class="bi bi-wallet2"></i> Agent Payment
        </a>
        <?php endif; ?>

        <div class="menu-title">SYSTEM</div>

        <a href="<?= site_url('logout') ?>">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>

    </div>

</div>