<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>🧾 Agent Invoices</h2>

<table class="table table-bordered">

<tr>
    <th>Invoice No</th>
    <th>Total</th>
    <th>Balance</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php foreach($invoices as $row): ?>

<tr>

<td><?= $row['invoice_no'] ?></td>

<td>
Rp <?= number_format($row['total_amount']) ?>
</td>

<td>
Rp <?= number_format($row['balance_amount']) ?>
</td>

<td><?= $row['invoice_status'] ?></td>
<td>

<a
href="<?= site_url('agent/invoice/'.$row['id']) ?>"
class="btn btn-primary btn-sm">

View

</a>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>