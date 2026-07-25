<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-3">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
        $detailStatus = strtoupper(
            trim(
                (string) (
                    $shipment['shipment_status'] ?? ''
                )
            )
        );
    ?>

    <?php if ($detailStatus === 'CANCELLED'): ?>
        <div class="alert alert-secondary">
            Shipment telah dibatalkan dan seluruh datanya dikunci.
        </div>
    <?php endif ?>

    <?= $this->include('shipments/partials/header') ?>

    <?= $this->include('shipments/partials/tabs') ?>

    <?= $this->include(
        'shipments/partials/lifecycle_modals'
    ) ?>

</div>

<?= $this->endSection() ?>