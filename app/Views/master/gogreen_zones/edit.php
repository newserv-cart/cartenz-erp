<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-warning text-dark">

<h4>✏ Edit Go Green Zone</h4>

</div>

<div class="card-body">

<form action="<?= site_url('master/gogreen-zones/update/'.$zone['id']) ?>" method="post">

<div class="mb-3">

<label>Zone Code</label>

<input
type="text"
name="zone_code"
class="form-control"
value="<?= $zone['zone_code'] ?>"
required>

</div>

<div class="mb-3">

<label>Zone Name</label>

<input
type="text"
name="zone_name"
class="form-control"
value="<?= $zone['zone_name'] ?>"
required>

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"><?= $zone['description'] ?></textarea>

</div>

<div class="mb-3">

<label>Status</label>

<select name="status" class="form-select">

<option value="1" <?= $zone['status']==1?'selected':'' ?>>Active</option>

<option value="0" <?= $zone['status']==0?'selected':'' ?>>Inactive</option>

</select>

</div>

<button class="btn btn-success">

💾 Update

</button>

<a href="<?= site_url('master/gogreen-zones') ?>" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?= $this->endSection() ?>