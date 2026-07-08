<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-warning">

<h4>✏ Edit Fuel Rule</h4>

</div>

<div class="card-body">

<form action="<?= site_url('master/fuel-rules/update/'.$rule['id']) ?>" method="post">

<div class="row">

<div class="col-md-12 mb-3">

<label>Service</label>

<select name="service_id" class="form-select">

<?php foreach($services as $service): ?>

<option
value="<?= $service['id'] ?>"
<?= $service['id']==$rule['service_id']?'selected':'' ?>>

<?= esc($service['service_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<hr>

<h5>👤 Customer Charge</h5>

<div class="row">

<div class="col-md-6">

<label>Type</label>

<select name="customer_type" class="form-select">

<option
value="PER_KG"
<?= $rule['customer_type']=='PER_KG'?'selected':'' ?>>

Per Kg

</option>

<option
value="FIXED"
<?= $rule['customer_type']=='FIXED'?'selected':'' ?>>

Fixed

</option>

</select>

</div>

<div class="col-md-6">

<label>Value</label>

<input
type="number"
step="0.01"
class="form-control"
name="customer_value"
value="<?= $rule['customer_value'] ?>">

</div>

</div>

<hr>

<h5>🏢 Vendor Charge</h5>

<div class="row">

<div class="col-md-6">

<label>Type</label>

<select name="vendor_type" class="form-select">

<option
value="PER_KG"
<?= $rule['vendor_type']=='PER_KG'?'selected':'' ?>>

Per Kg

</option>

<option
value="FIXED"
<?= $rule['vendor_type']=='FIXED'?'selected':'' ?>>

Fixed

</option>

</select>

</div>

<div class="col-md-6">

<label>Value</label>

<input
type="number"
step="0.01"
class="form-control"
name="vendor_value"
value="<?= $rule['vendor_value'] ?>">

</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">

<label>Effective From</label>

<input
type="date"
class="form-control"
name="effective_from"
value="<?= $rule['effective_from'] ?>">

</div>

<div class="col-md-4">

<label>Effective To</label>

<input
type="date"
class="form-control"
name="effective_to"
value="<?= $rule['effective_to'] ?>">

</div>

<div class="col-md-4">

<label>Status</label>

<select
name="status"
class="form-select">

<option
value="1"
<?= $rule['status']==1?'selected':'' ?>>

Active

</option>

<option
value="0"
<?= $rule['status']==0?'selected':'' ?>>

Inactive

</option>

</select>

</div>

</div>

<hr>

<button class="btn btn-success">

💾 Update Rule

</button>

<a
href="<?= site_url('master/fuel-rules') ?>"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?= $this->endSection() ?>