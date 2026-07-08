<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-3">

    <?= $this->include('shipments/partials/header') ?>

    <?= $this->include('shipments/partials/tabs') ?>

</div>

<?= $this->endSection() ?>