!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>AWB</title>
</head>
<body>


<?php

use Picqer\Barcode\BarcodeGeneratorPNG;

$generator = new BarcodeGeneratorPNG();

$barcode = base64_encode(
    $generator->getBarcode(
        $shipment['awb_no'],
        $generator::TYPE_CODE_128
    )
);

?>



<style>

body{
    font-family: DejaVu Sans;
    font-size:12px;
}

h1,h2,h3{
    margin:3px 0;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

table th,
table td{
    border:1px solid #000;
    padding:5px;
}

.summary{
    margin-top:15px;
    border:1px solid #000;
    padding:10px;
}

</style>





<h1>CARTENZ EXPRESS</h1>

<h2>
Airwaybills:
<?= $shipment['awb_no'] ?>
</h2>

<div style="margin-top:20px">

    <strong>TRACKING QR</strong>

    <br><br>

    <img
    src="<?= $qrUrl ?>"
    width="120">

</div>


<div style="font-size:10px">

    <?= base_url('track/' . $shipment['awb_no']) ?>

</div>


<div style="text-align:center;margin-top:10px;">

    <img
        src="data:image/png;base64,<?= $barcode ?>"
        width="300">

    <br>

    <strong>
        <?= $shipment['awb_no'] ?>
    </strong>

</div>
<h3>COLLY DETAIL</h3>

<table>
<tr>
    <th>No</th>
    <th>Weight</th>
    <th>Dimension</th>
    <th>Volume</th>
</tr>

<?php foreach($packages as $i => $package): ?>

<tr>
    <td><?= $i + 1 ?></td>
    <td><?= $package['actual_weight'] ?></td>
    <td>
        <?= $package['length_cm'] ?>
        x
        <?= $package['width_cm'] ?>
        x
        <?= $package['height_cm'] ?>
    </td>
    <td><?= $package['volume_weight'] ?></td>
</tr>

<?php endforeach; ?>

</table>


<h3>ITEM DETAIL</h3>

<table>
<tr>
    <th>Description</th>
    <th>Qty</th>
    <th>Value</th>
</tr>

<?php foreach($packages as $package): ?>
    <?php foreach($package['items'] as $item): ?>

<tr>
    <td><?= $item['item_description'] ?></td>
    <td><?= $item['quantity'] ?></td>
    <td><?= number_format($item['total_value'],2) ?></td>
</tr>

    <?php endforeach; ?>
<?php endforeach; ?>

</table>


<div class="summary">

<p>
<b>Total Weight :</b>
<?= $shipment['actual_weight'] ?>
</p>

<p>
<b>Chargeable :</b>
<?= $shipment['chargeable_weight'] ?>
</p>

<p>
<b>Declared Value :</b>
<?= number_format(
    $shipment['declared_value'],
    2
) ?>
</p>

</div>



</body>
</html>