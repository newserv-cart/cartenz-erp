<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h4>📦 Commodity Charge Rules</h4>

</div>

<div class="card-body">

<form method="post"
action="<?= site_url('master/commodity-charge-rules/store') ?>">

<?= csrf_field() ?>

<div class="row">

<div class="col-md-3 mb-3">

<label>Commodity</label>

<select
name="commodity_type_id"
class="form-select"
required>

<option value="">Select Commodity</option>

<?php foreach($commodities as $row): ?>

<option value="<?= $row['id'] ?>">

<?= esc($row['commodity_name']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-3 mb-3">

<label>Customer Insurance</label>

<input
type="number"
name="customer_insurance"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3">

<label>Vendor Insurance</label>

<input
type="number"
name="vendor_insurance"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3">

<label>Customer Packing</label>

<input
type="number"
name="customer_packing"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3">

<label>Vendor Packing</label>

<input
type="number"
name="vendor_packing"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3">

<label>Customer Additional</label>

<input
type="number"
name="customer_additional"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3">

<label>Vendor Additional</label>

<input
type="number"
name="vendor_additional"
class="form-control text-end"
value="0">

</div>

<div class="col-md-3 mb-3 d-flex align-items-end">

<button
type="submit"
class="btn btn-success w-100">

💾 Save Rule

</button>

</div>

</div>

</form>

<hr>

<table class="table table-bordered table-hover">

<thead class="table-primary">

<tr>

<th>Commodity</th>

<th>Cust Ins</th>

<th>Vend Ins</th>

<th>Cust Pack</th>

<th>Vend Pack</th>

<th>Cust Add</th>

<th>Vend Add</th>

<th width="120">Action</th>

</tr>

</thead>

<tbody>

<?php if(!empty($rules)): ?>

<?php foreach($rules as $row): ?>

<tr>

<td><?= esc($row['commodity_name']) ?></td>

<td class="text-end"><?= number_format($row['customer_insurance']) ?></td>

<td class="text-end"><?= number_format($row['vendor_insurance']) ?></td>

<td class="text-end"><?= number_format($row['customer_packing']) ?></td>

<td class="text-end"><?= number_format($row['vendor_packing']) ?></td>

<td class="text-end"><?= number_format($row['customer_additional']) ?></td>

<td class="text-end"><?= number_format($row['vendor_additional']) ?></td>

<td class="text-center">

<a
href="<?= site_url('master/commodity-charge-rules/edit/'.$row['id']) ?>"
class="btn btn-warning btn-sm">

✏️

</a>

<a
href="<?= site_url('master/commodity-charge-rules/delete/'.$row['id']) ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this rule?')">

🗑

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="8" class="text-center">

No Rules Found

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

<?= $this->endSection() ?>