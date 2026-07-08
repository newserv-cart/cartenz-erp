<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <?= $this->include('admin/dashboard/partials/header') ?>

    <?= $this->include('admin/dashboard/partials/quick_action') ?>

    <?= $this->include('admin/dashboard/partials/kpi_cards') ?>

    <?= $this->include('admin/dashboard/partials/charts') ?>

    <?= $this->include('admin/dashboard/partials/operation') ?>

    <?= $this->include('admin/dashboard/partials/analytics') ?>

    <?= $this->include('admin/dashboard/partials/activity') ?>

    <?= $this->include('admin/dashboard/partials/notification') ?>

</div>

<?= $this->include('admin/dashboard/partials/scripts') ?>

<?= $this->endSection() ?>