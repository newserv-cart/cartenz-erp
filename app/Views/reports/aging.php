<!DOCTYPE html>
<html>
<head>
<title>Aging Report</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>
<body>

<div class="container mt-4">

<h3>
Aging Report
</h3>

<table class="table table-bordered">

<thead>

<tr>

<th>Customer</th>
<th>Invoice</th>
<th>Due Date</th>

<th class="text-end">
Current
</th>

<th class="text-end">
0-30
</th>

<th class="text-end">
31-60
</th>

<th class="text-end">
61-90
</th>

<th class="text-end">
90+
</th>

</tr>

</thead>

<tbody>

<?php foreach($rows as $row): ?>

<?php

$days =
    floor(
        (time() - strtotime($row['due_date']))
        / 86400
    );

$current = 0;
$a30 = 0;
$a60 = 0;
$a90 = 0;
$a90plus = 0;

if ($days < 0)
{
    $current =
        $row['balance_amount'];
}
elseif ($days <= 30)
{
    $a30 =
        $row['balance_amount'];
}
elseif ($days <= 60)
{
    $a60 =
        $row['balance_amount'];
}
elseif ($days <= 90)
{
    $a90 =
        $row['balance_amount'];
}
else
{
    $a90plus =
        $row['balance_amount'];
}

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

<td class="text-end">
<?= number_format($current,0,',','.') ?>
</td>

<td class="text-end">
<?= number_format($a30,0,',','.') ?>
</td>

<td class="text-end">
<?= number_format($a60,0,',','.') ?>
</td>

<td class="text-end">
<?= number_format($a90,0,',','.') ?>
</td>

<td class="text-end">
<?= number_format($a90plus,0,',','.') ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</body>
</html>