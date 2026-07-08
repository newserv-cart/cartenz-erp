<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>🧾 Invoice Detail</h2>

<table class="table table-bordered">

<?php if($invoice['balance_amount'] > 0): ?>

<a
href="<?= site_url('finance/payments') ?>"
class="btn btn-warning">

💳 View Payment

</a>

<?php endif; ?>


<tr>
    <th>Invoice No</th>
    <td><?= $invoice['invoice_no'] ?></td>
</tr>

<tr>
    <th>Invoice Date</th>
    <td><?= $invoice['invoice_date'] ?></td>
</tr>

<tr>
    <th>Due Date</th>
    <td><?= $invoice['due_date'] ?></td>
</tr>

<tr>
    <th>Total Amount</th>
    <td>
        Rp <?= number_format($invoice['total_amount']) ?>
    </td>
</tr>

<tr>
    <th>Paid Amount</th>
    <td>
        Rp <?= number_format($invoice['paid_amount']) ?>
    </td>
</tr>

<tr>
    <th>Balance</th>
    <td>
        Rp <?= number_format($invoice['balance_amount']) ?>
    </td>
</tr>

<tr>
    <th>Status</th>
    <td>
        <?= $invoice['invoice_status'] ?>
    </td>
</tr>

</table>

<a href="<?= site_url('agent/invoices') ?>"
   class="btn btn-secondary">
   Back
</a>

</div>