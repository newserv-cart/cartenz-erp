<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="card shadow">

<div class="card-header bg-success text-white d-flex justify-content-between align-items-center">

<h4 class="mb-0">⛽ Fuel Rules</h4>

<a href="<?= site_url('master/fuel-rules/create') ?>"
class="btn btn-light">

➕ Add Rule

</a>

</div>

<div class="card-body">

<?php if(session()->getFlashdata('success')): ?>

<div class="alert alert-success">

<?= session()->getFlashdata('success') ?>

</div>

<?php endif; ?>

<table class="table table-bordered table-striped table-hover">

<thead class="table-dark">

<tr>

<th>No</th>

<th>Service</th>

<th>Customer</th>

<th>Vendor</th>

<th>Effective</th>

<th>Status</th>

<th width="160">Action</th>

</tr>

</thead>

<tbody>

<?php if(!empty($rules)): ?>

<?php foreach($rules as $i=>$row): ?>

<tr>

<td><?= $i+1 ?></td>

<td><?= esc($row['service_name']) ?></td>

<td>

<?= $row['customer_type'] ?>

<br>

<strong>

Rp <?= number_format($row['customer_value'],0,',','.') ?>

</strong>

</td>

<td>

<?= $row['vendor_type'] ?>

<br>

<strong>

Rp <?= number_format($row['vendor_value'],0,',','.') ?>

</strong>

</td>

<td>

<?= $row['effective_from'] ?>

<br>

<?= $row['effective_to'] ?>

</td>

<td>

<?php if($row['status']==1): ?>

<span class="badge bg-success">

ACTIVE

</span>

<?php else: ?>

<span class="badge bg-secondary">

INACTIVE

</span>

<?php endif; ?>

</td>

<td>

<a
href="<?= site_url('master/fuel-rules/edit/'.$row['id']) ?>"
class="btn btn-warning btn-sm">

✏ Edit

</a>

<a
href="<?= site_url('master/fuel-rules/delete/'.$row['id']) ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Deactivate this rule ?')">

🗑 Delete

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="7" class="text-center">

No Fuel Rule Found

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

<?php if(isset($pager)): ?>

<?= $pager->links() ?>

<?php endif; ?>

</div>

</div>

</div>

<?= $this->endSection() ?>