<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<img
src="<?= FCPATH.'uploads/company/logo.png' ?>"
height="60">
<style>



body{
    font-family: Arial, sans-serif;
    font-size:12px;
}

.title{
    text-align:center;
    font-size:20px;
    font-weight:bold;
    margin-bottom:10px;
}

.subtitle{
    text-align:center;
    margin-bottom:20px;
}

.info{
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th,
table td{
    border:1px solid #000;
    padding:6px;
}

table th{
    background:#f0f0f0;
}

.footer{
    margin-top:40px;
}

.sign{
    width:45%;
    float:left;
    text-align:center;
}

</style>

</head>
<body>

<div class="title">
    CARTENZ GLOBAL EXPRESS
</div>

<div class="subtitle">
    MANIFEST PENGIRIMAN
</div>

<div class="info">

<b>Manifest No :</b>
<?= $manifest['manifest_no'] ?>

<br>

<b>Tanggal :</b>
<?= date('d-m-Y', strtotime($manifest['manifest_date'])) ?>
<br>


<b>Status :</b>
<?= $manifest['status'] ?>

<br>

<b>Total Shipment :</b>
<?= $manifest['total_shipment'] ?>

<br>

<b>Total Weight :</b>
<?= number_format($manifest['total_weight'],2) ?> Kg

</div>

<table>

<tr>
    <th width="50">No</th>
    <th>AWB</th>
    <th width="120">Weight</th>
</tr>

<?php $no=1; ?>

<?php foreach($details as $row): ?>

<tr>

<td align="center">
<?= $no++ ?>
</td>

<td>
<?= $row['awb_no'] ?>
</td>

<td align="right">
<?= number_format($row['actual_weight'],2) ?>
</td>

</tr>

<?php endforeach; ?>


<tr>
    <td colspan="2" align="right">
        <b>Total Weight</b>
    </td>

    <td align="right">
        <b>
            <?= number_format($manifest['total_weight'],2) ?>
        </b>
    </td>
</tr>

</table>





<div class="footer">

<div class="sign">
    Dibuat Oleh
    <br><br><br><br>
    __________________
</div>

<div class="sign" style="float:right;">
    Diterima Oleh
    <br><br><br><br>
    __________________
</div>

</div>

</body>
</html>