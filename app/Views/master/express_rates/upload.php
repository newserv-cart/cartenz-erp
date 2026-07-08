<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>📥 Import Express Rate</h4>

</div>

<div class="card-body">

<form
action="<?= site_url('master/express-rates/import') ?>"
method="post"
enctype="multipart/form-data">

<?= csrf_field() ?>

<div class="row">

<div class="col-md-3 mb-3">

<label>Vendor</label>

<select
name="vendor_id"
class="form-select"
required>

<option value="">Select Vendor</option>

<?php foreach($vendors as $v): ?>

<option value="<?= $v['id'] ?>">
<?= esc($v['vendor_name']) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Service</label>

<select
name="service_id"
class="form-select"
required>

<?php foreach($services as $s): ?>

<option value="<?= $s['id'] ?>">
<?= esc($s['service_name']) ?>
</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Effective Date</label>

<input
type="date"
name="effective_date"
class="form-control"
value="<?= date('Y-m-d') ?>">

</div>

<div class="col-md-3 mb-3">

<label>Remarks</label>

<input
type="text"
name="remarks"
class="form-control">

</div>

</div>

<hr>

<div class="mb-3">

<label>Excel File</label>

<input
type="file"
name="excel"
accept=".xlsx,.xls"
class="form-control"
required>

</div>

<div class="text-end">

<button
class="btn btn-success">

📥 Import Excel

</button>

</div>

</form>

</div>

</div>

</div>

<?= $this->endSection() ?>