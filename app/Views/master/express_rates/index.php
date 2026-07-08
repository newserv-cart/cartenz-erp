<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

<div class="d-flex justify-content-between mb-3">

<h3>
🚀 Express Rate
</h3>

<a
href="<?= site_url('master/express-rates/upload')?>"
class="btn btn-primary">

📥 Import Excel

</a>

</div>

<?php if(session()->getFlashdata('success')): ?>

<div class="alert alert-success">

<?= session()->getFlashdata('success') ?>

</div>

<?php endif; ?>

<div class="card shadow">

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Vendor</th>

<th>Service</th>

<th>Effective Date</th>

<th>Total Rate</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($headers as $row): ?>

<tr>

<td><?= $row['id'] ?></td>

<td>

<?= esc($row['vendor_name']) ?>

</td>

<td>

<?= esc($row['service_name']) ?>

</td>

<td>

<?= $row['effective_date'] ?>

</td>

<td>

<?= number_format($row['total_rate']) ?>

Rates

</td>

<td>

<a
href="<?= site_url('master/express-rates/view/'.$row['id']) ?>"
class="btn btn-info btn-sm">

👁

</a>

<a
href="<?= site_url('master/express-rates/delete/'.$row['id']) ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete Rate?')">

🗑

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<?= $this->endSection() ?>