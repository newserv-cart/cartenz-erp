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
<?= esc($invoice['invoice_no'] ?? '-') ?>

<br>

Invoice Date :
<?= !empty($invoice['invoice_date']) ? date('d-m-Y',strtotime($invoice['invoice_date'])) : '-' ?>

<br>

Due Date :
<?= !empty($invoice['due_date']) ? date('d-m-Y',strtotime($invoice['due_date'])) : '-' ?>

</td>

</tr>
</table>

<br>

<table>

<thead>

<tr>

<th>Date</th>
<th>AWB</th>
<th>Receiver</th>
<th>Country</th>
<th>Package</th>
<th>Weight</th>
<th>Amount</th>

</tr>

</thead>

<tbody>

<?php if (!empty($items)): ?>
<?php foreach($items as $item): ?>

<?php
$receiverName = trim((string) ($item['receiver_name'] ?? ''));
$countryName = trim((string) ($item['country_name'] ?? ''));
$packageTypeName = trim((string) ($item['package_type_name'] ?? ''));
$shipmentDate = $item['shipment_date'] ?? $item['shipment_created_at'] ?? null;
?>

<tr>

<td>
<?= $shipmentDate ? date('d-m-Y', strtotime($shipmentDate)) : '-' ?>
</td>

<td>
<?= esc($item['awb_no'] ?? '-') ?>
</td>

<td>
<?= esc($receiverName !== '' ? $receiverName : '-') ?>
</td>

<td>
<?= esc($countryName !== '' ? $countryName : '-') ?>
</td>

<td>
<?= esc($packageTypeName !== '' ? $packageTypeName : '-') ?>
</td>

<td class="text-right">
<?= number_format($item['invoice_weight'] ?? 0, 2, ',', '.') ?>
</td>

<td class="text-right">
Rp <?= number_format($item['amount'] ?? 0,0,',','.') ?>
</td>

</tr>

<?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="7" class="text-center">No items found</td>
</tr>
<?php endif; ?>

</tbody>

<tfoot>

<tr>
    <th colspan="6" class="text-right">Sub Total</th>
    <th class="text-right">Rp <?= number_format($invoice['total_amount'] ?? 0,0,',','.') ?></th>
</tr>

<tr>
    <th colspan="6" class="text-right">VAT (<?= $invoice['vat_percent'] ?? 0 ?>%)</th>
    <th class="text-right">Rp <?= number_format($invoice['vat_amount'] ?? 0,0,',','.') ?></th>
</tr>

<tr>
    <th colspan="6" class="text-right">Extra Charge</th>
    <th class="text-right">Rp <?= number_format($invoice['extra_charge'] ?? 0,0,',','.') ?></th>
</tr>

<tr>
    <th colspan="6" class="text-right">Grand Total</th>
    <th class="text-right">Rp <?= number_format($invoice['grand_total'] ?? $invoice['total_amount'] ?? 0,0,',','.') ?></th>
</tr>

</tfoot>

</table>

<br>

<p>

<strong>Terbilang :</strong>

<?= trim(
    terbilang(
        (int)($invoice['grand_total'] ?? $invoice['total_amount'] ?? 0)
    )
) ?>

Rupiah

</p>

<!-- Payment Information (Versi PDF) -->
<?php
$bankName = trim(
    (string) ($bankAccount['bank_name'] ?? '')
);

$accountName = trim(
    (string) ($bankAccount['account_name'] ?? '')
);

$accountNumber = trim(
    (string) ($bankAccount['account_number'] ?? '')
);

$swiftCode = trim(
    (string) ($bankAccount['swift_code'] ?? '')
);

$currency = trim(
    (string) ($bankAccount['currency'] ?? '')
);
?>

<div style="margin-top: 25px;">
    <div
        style="
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
        "
    >
        Payment Information
    </div>

    <?php if ($bankAccount !== []): ?>

        <table
            style="
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
            "
        >
            <tr>
                <td style="width: 120px; padding: 2px 0;">
                    Bank
                </td>
                <td style="width: 15px;">:</td>
                <td>
                    <strong><?= esc($bankName) ?></strong>
                </td>
            </tr>

            <tr>
                <td style="padding: 2px 0;">
                    Account Name
                </td>
                <td>:</td>
                <td>
                    <strong><?= esc($accountName) ?></strong>
                </td>
            </tr>

            <tr>
                <td style="padding: 2px 0;">
                    Account Number
                </td>
                <td>:</td>
                <td>
                    <strong><?= esc($accountNumber) ?></strong>
                </td>
            </tr>

            <tr>
                <td style="padding: 2px 0;">
                    Currency
                </td>
                <td>:</td>
                <td><?= esc($currency ?: 'IDR') ?></td>
            </tr>

            <?php if ($swiftCode !== ''): ?>
                <tr>
                    <td style="padding: 2px 0;">
                        SWIFT Code
                    </td>
                    <td>:</td>
                    <td><?= esc($swiftCode) ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <div
            style="
                margin-top: 7px;
                font-size: 10px;
                font-style: italic;
            "
        >
            Mohon cantumkan nomor invoice pada berita transfer.
        </div>

    <?php else: ?>

        <div style="font-size: 11px;">
            Rekening pembayaran belum dikonfigurasi.
        </div>

    <?php endif; ?>
</div>

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