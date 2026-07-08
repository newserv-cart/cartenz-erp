<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<title>AWB Label</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    padding:20px;
}

.label-box{
    border:2px solid #000;
    padding:20px;
}

.awb{
    font-size:32px;
    font-weight:bold;
}

</style>

</head>
<body>

<div class="label-box">

<h3>
🚚 CARTENZ GLOBAL EXPRESS
</h3>

<hr>

<div class="awb">
<?= esc($shipment['awb_no']) ?>
</div>

<br>

<img
src="https://barcode.tec-it.com/barcode.ashx?data=<?= urlencode($shipment['awb_no']) ?>&code=Code128">

<hr>

<p>

<strong>Status :</strong>

<?= esc($shipment['shipment_status']) ?>

</p>

<p>

<strong>Weight :</strong>

<?= number_format(
    $shipment['chargeable_weight'] ?? 0,
    2
) ?>

Kg

</p>

</div>

<script>
window.print();
</script>

</body>
</html>