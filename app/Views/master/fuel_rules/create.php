<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4>⛽ Add Fuel Rule</h4>

</div>

<div class="card-body">

<form action="<?= site_url('master/fuel-rules/store') ?>" method="post">

<div class="row">

<div class="col-md-12 mb-3">

<label class="form-label">Service</label>

<select name="service_id" class="form-select" required>

<option value="">-- Select Service --</option>

<?php foreach($services as $service): ?>

<option value="<?= $service['id'] ?>">

<?= esc($service['service_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<hr>

<h5>👤 Customer Charge</h5>

<div class="row">

<div class="col-md-6 mb-3">

<label>Charge Type</label>

<select name="customer_type" class="form-select">

<option value="PER_KG">Per Kg</option>

<option value="FIXED">Fixed</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Customer Value</label>

<input
type="number"
step="0.01"
name="customer_value"
class="form-control"
value="0">

</div>

</div>

<hr>

<h5>🏢 Vendor Charge</h5>

<div class="row">

<div class="col-md-6 mb-3">

<label>Charge Type</label>

<select name="vendor_type" class="form-select">

<option value="PER_KG">Per Kg</option>

<option value="FIXED">Fixed</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Vendor Value</label>

<input
type="number"
step="0.01"
name="vendor_value"
class="form-control"
value="0">

</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">

<label>Effective From</label>

<input
type="date"
name="effective_from"
class="form-control">

</div>

<div class="col-md-4">

<label>Effective To</label>

<input
type="date"
name="effective_to"
class="form-control">

</div>

<div class="col-md-4">

<label>Status</label>

<select name="status" class="form-select">

<option value="1">Active</option>

<option value="0">Inactive</option>

</select>

</div>

</div>

<hr>

<button class="btn btn-success">

💾 Save Rule

</button>

<a href="<?= site_url('master/fuel-rules') ?>" class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?= $this->endSection() ?>