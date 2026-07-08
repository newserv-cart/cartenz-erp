<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:12px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th,
table td{
    border:1px solid #000;
    padding:5px;
}

.text-right{
    text-align:right;
}

.header{
    margin-bottom:20px;
}

</style>

</head>
<body>

<h2>
<?= esc($company['company_name'] ?? '-') ?>
</h2>

<p>
<?= esc($company['legal_name'] ?? '') ?>
</p>

<hr>

<h2>
INVOICE
</h2>

<table style="border:none;">
<tr>

<td style="border:none;width:60%;">

<strong>Customer :</strong><br>

<?= esc($invoice['customer_name'] ?? '-') ?>

<br><br>

<?= esc($invoice['address'] ?? '-') ?>

</td>

<td style="border:none;">

Invoice No :
<?= esc($invoice['invoice_no']) ?>

<br>

Invoice Date :
<?= date('d-m-Y',strtotime($invoice['invoice_date'])) ?>

<br>

Due Date :
<?= date('d-m-Y',strtotime($invoice['due_date'])) ?>

</td>

</tr>
</table>

<br>

<table>

<thead>

<tr>

<th>Date</th>
<th>AWB</th>
<th>Weight</th>
<th>Amount</th>

</tr>

</thead>

<tbody>

<?php foreach($items as $item): ?>

<tr>

<td>
<?= date('d-m-Y',strtotime($item['created_at'])) ?>
</td>

<td>
<?= esc($item['awb_no']) ?>
</td>

<td class="text-right">
<?= number_format($item['chargeable_weight'],2) ?>
</td>

<td class="text-right">
Rp <?= number_format($item['amount'],0,',','.') ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

<tfoot>

<tr>

<th colspan="3" class="text-right">
Sub Total
</th>

<th class="text-right">
Rp <?= number_format($invoice['total_amount'],0,',','.') ?>
</th>

</tr>

<tr>

<th colspan="3" class="text-right">
VAT
</th>

<th class="text-right">
Rp <?= number_format($invoice['vat_amount'] ?? 0,0,',','.') ?>
</th>

</tr>

<tr>

<th colspan="3" class="text-right">
Extra Charge
</th>

<th class="text-right">
Rp <?= number_format($invoice['extra_charge'] ?? 0,0,',','.') ?>
</th>

</tr>

<tr>

    <th colspan="3" class="text-right">
        Grand Total
    </th>

    <th class="text-right">
        Rp <?= number_format(
            $invoice['grand_total'] ?? $invoice['total_amount'],
            0,
            ',',
            '.'
        ) ?>
    </th>

</tr>
</tfoot>

</table>

<br>

<p>

<strong>Terbilang :</strong>

<?= trim(
    terbilang(
        (int)($invoice['grand_total'] ?? $invoice['total_amount'])
    )
) ?>

Rupiah

</p>

<br><br>

<h4>
Payment Information
</h4>

<p>

Bank Transfer

</p>

<br><br><br>

<table style="border:none;">

<tr>

<td style="border:none;text-align:center;">

Prepared By

<br><br><br><br>

____________________

</td>

<td style="border:none;text-align:center;">

Approved By

<br><br><br><br>

____________________

</td>

</tr>

</table>

</body>
</html>