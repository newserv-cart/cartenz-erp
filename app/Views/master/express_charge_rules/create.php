<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<form action="<?= site_url('master/express-charge-rules/store') ?>" method="post">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4 class="mb-0">
🚀 Add Express Charge Rule
</h4>

</div>

<div class="card-body">

<?php if(session()->getFlashdata('errors')): ?>

<div class="alert alert-danger">

<ul class="mb-0">

<?php foreach(session()->getFlashdata('errors') as $e): ?>

<li><?= esc($e) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>


<div class="row">

<div class="col-md-6 mb-3">

<label>Country</label>

<select
name="country_id"
class="form-select"
required>

<option value="">Select Country</option>

<?php foreach($countries as $c): ?>

<option value="<?= $c['id'] ?>">

<?= esc($c['country_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Service</label>

<select
name="service_id"
class="form-select"
required>

<option value="">Select Service</option>

<?php foreach($services as $s): ?>

<option value="<?= $s['id'] ?>">

<?= esc($s['service_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-4">

<div class="card border-warning mb-3">

<div class="card-header bg-warning">

🔥 Fuel Surcharge

</div>

<div class="card-body">

<label>Customer Type</label>

<select
name="customer_fuel_type"
class="form-select mb-2">

<option value="PERCENT">Percent</option>

<option value="FIXED">Fixed</option>

</select>

<input
type="number"
step="0.01"
name="customer_fuel_value"
class="form-control mb-3"
placeholder="Customer Value">

<label>Vendor Type</label>

<select
name="vendor_fuel_type"
class="form-select mb-2">

<option value="PERCENT">Percent</option>

<option value="FIXED">Fixed</option>

</select>

<input
type="number"
step="0.01"
name="vendor_fuel_value"
class="form-control"
placeholder="Vendor Value">

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-success mb-3">

<div class="card-header bg-success text-white">

🌱 Go Green

</div>

<div class="card-body">

<label>Customer Type</label>

<select
name="customer_gogreen_type"
class="form-select mb-2">

<option value="PER_KG">Per Kg</option>

<option value="FIXED">Fixed</option>

</select>

<input
type="number"
step="0.01"
name="customer_gogreen_value"
class="form-control mb-3">

<label>Vendor Type</label>

<select
name="vendor_gogreen_type"
class="form-select mb-2">

<option value="PER_KG">Per Kg</option>

<option value="FIXED">Fixed</option>

</select>

<input
type="number"
step="0.01"
name="vendor_gogreen_value"
class="form-control">

</div>

</div>

</div>

<div class="col-md-4">

<div class="card border-danger mb-3">

<div class="card-header bg-danger text-white">

📍 Remote Area

</div>

<div class="card-body">

<label>Customer Type</label>

<select
name="customer_remote_type"
class="form-select mb-2">

<option value="FIXED">Fixed</option>

<option value="PERCENT">Percent</option>

</select>

<input
type="number"
step="0.01"
name="customer_remote_value"
class="form-control mb-3">

<label>Vendor Type</label>

<select
name="vendor_remote_type"
class="form-select mb-2">

<option value="FIXED">Fixed</option>

<option value="PERCENT">Percent</option>

</select>

<input
type="number"
step="0.01"
name="vendor_remote_value"
class="form-control">

</div>

</div>

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

<select
name="status"
class="form-select">

<option value="1">

Active

</option>

<option value="0">

Inactive

</option>

</select>

</div>

</div>

<hr>

<div class="text-end">

<a
href="<?= site_url('master/express-charge-rules') ?>"
class="btn btn-secondary">

← Back

</a>

<button
type="submit"
class="btn btn-primary">

💾 Save Rule

</button>

</div>

</div>

</div>

</form>

</div>

<?= $this->endSection() ?>

