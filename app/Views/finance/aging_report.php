<h2>📊 Aging Report</h2>

<table border="1" cellpadding="8">

<tr>
    <th>Invoice</th>
    <th>Due Date</th>
    <th>Balance</th>
    <th>Days</th>
    <th>Bucket</th>
</tr>

<?php foreach($invoices as $invoice): ?>

<tr>

    <td><?= $invoice['invoice_no'] ?></td>

    <td><?= $invoice['due_date'] ?></td>

    <td>
        Rp <?= number_format($invoice['balance_amount']) ?>
    </td>

    <td>
        <?= $invoice['aging_days'] ?>
    </td>

    <td>
        <?= $invoice['bucket'] ?>
    </td>

</tr>

<?php endforeach; ?>

</table>