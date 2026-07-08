a<!DOCTYPE html>
<html>
<head>
    <title>Statement Of Account</title>

    <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet">
</head>
<body>

<div class="container mt-4">

<h3>
Statement Of Account
</h3>

<table class="table table-bordered">

<thead>

<tr>
    <th>Customer</th>
    <th>Invoice</th>
    <th>Due Date</th>
    <th>Status</th>
    <th class="text-end">
        Balance
    </th>
</tr>

</thead>

<tbody>

<?php
$total = 0;
?>

<?php foreach($rows as $row): ?>

<?php
$total += $row['balance_amount'];
?>

<tr>

<td>
<?= esc($row['customer_name']) ?>
</td>

<td>
<?= esc($row['invoice_no']) ?>
</td>

<td>
<?= date(
    'd-m-Y',
    strtotime($row['due_date'])
) ?>
</td>

<td>
<?= esc($row['invoice_status']) ?>
</td>

<td class="text-end">

Rp <?= number_format(
    $row['balance_amount'],
    0,
    ',',
    '.'
) ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

<tfoot>

<tr>

<th colspan="4">
Total Outstanding
</th>

<th class="text-end">

Rp <?= number_format(
    $total,
    0,
    ',',
    '.'
) ?>

</th>

</tr>

</tfoot>

</table>

</div>

</body>
</html>