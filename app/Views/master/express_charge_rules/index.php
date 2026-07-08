<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>


<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

            <h4 class="mb-0">
                🚀 Express Charge Rules
            </h4>

            <a href="<?= site_url('master/express-charge-rules/create') ?>"
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


<table class="table table-bordered table-hover table-striped align-middle">

<thead class="table-dark">

<tr>

<th>#</th>

<th>Country</th>

<th>Service</th>

<th>Fuel</th>

<th>Go Green</th>

<th>Remote</th>

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

<td><?= esc($row['country_name']) ?></td>

<td><?= esc($row['service_name']) ?></td>

<td>

Customer :

<?= esc($row['customer_fuel_type']) ?>

<?= number_format($row['customer_fuel_value'],2) ?>

<br>

Vendor :

<?= esc($row['vendor_fuel_type']) ?>

<?= number_format($row['vendor_fuel_value'],2) ?>

</td>

<td>

Customer :

<?= esc($row['customer_gogreen_type']) ?>

<?= number_format($row['customer_gogreen_value'],2) ?>

<br>

Vendor :

<?= esc($row['vendor_gogreen_type']) ?>

<?= number_format($row['vendor_gogreen_value'],2) ?>

</td>

<td>

Customer :

<?= esc($row['customer_remote_type']) ?>

<?= number_format($row['customer_remote_value'],2) ?>

<br>

Vendor :

<?= esc($row['vendor_remote_type']) ?>

<?= number_format($row['vendor_remote_value'],2) ?>

</td>

<td>

<?= $row['effective_from'] ?: '-' ?>

<br>

s/d

<br>

<?= $row['effective_to'] ?: '-' ?>

</td>

<td>

<?php if($row['status']): ?>

<span class="badge bg-success">

ACTIVE

</span>

<?php else: ?>

<span class="badge bg-danger">

INACTIVE

</span>

<?php endif; ?>

</td>

<td>

<a

href="<?= site_url('master/express-charge-rules/edit/'.$row['id']) ?>"

class="btn btn-warning btn-sm">

✏ Edit

</a>

<a

href="<?= site_url('master/express-charge-rules/delete/'.$row['id']) ?>"

onclick="return confirm('Deactivate this rule?')"

class="btn btn-danger btn-sm">

🗑 Delete

</a>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="9" class="text-center">

No Data

</td>

</tr>

<?php endif; ?>

</tbody>

</table>


<div class="mt-3">

<?= $pager->links() ?>

</div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>