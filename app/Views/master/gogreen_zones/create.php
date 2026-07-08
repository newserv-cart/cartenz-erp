<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white">
    <h4>🌱 Add Go Green Zone</h4>
</div>

<div class="card-body">

<form action="<?= site_url('master/gogreen-zones/store') ?>" method="post">

<div class="mb-3">
    <label class="form-label">Zone Code</label>
    <input
        type="text"
        name="zone_code"
        class="form-control"
        placeholder="Contoh : RRC"
        required>
</div>

<div class="mb-3">
    <label class="form-label">Zone Name</label>
    <input
        type="text"
        name="zone_name"
        class="form-control"
        placeholder="Contoh : RRC / Hong Kong"
        required>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea
        name="description"
        rows="3"
        class="form-control"></textarea>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>

    <select name="status" class="form-select">

        <option value="1">Active</option>

        <option value="0">Inactive</option>

    </select>

</div>

<hr>

<button class="btn btn-success">

💾 Save

</button>

<a href="<?= site_url('master/gogreen-zones') ?>" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?= $this->endSection() ?>