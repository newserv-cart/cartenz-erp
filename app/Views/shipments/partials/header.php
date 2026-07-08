<div class="card shadow mb-4">

<div class="card-body">

<div class="row">

<div class="col-md-8">

<h3 class="fw-bold">

<?= esc($shipment['awb_no']) ?>

</h3>

<span class="badge bg-primary">

<?= esc($shipment['shipment_status']) ?>

</span>

</div>

<div class="col-md-4 text-end">

<h5>

Customer

</h5>

<?= esc($shipment['customer_name']) ?>

</div>

</div>

<hr>

<div class="row">

<div class="col-md-2">

<b>Destination</b><br>

<?= esc($shipment['country_name']) ?>

</div>

<div class="col-md-2">

<b>Service</b><br>

<?= esc($shipment['service_name']) ?>

</div>

<div class="col-md-2">

<b>Vendor</b><br>

<?= esc($shipment['vendor_name'] ?? '-') ?>

</div>

<div class="col-md-2">

<b>Chargeable</b><br>

<?= $shipment['chargeable_weight'] ?>

Kg

</div>

<div class="col-md-2">

<b>Customer Total</b><br>

Rp <?= number_format($shipment['total_charge'] ?? 0) ?>

</div>

<div class="col-md-2">

<b>Profit</b><br>

Rp<?= number_format($shipment['profit_amount'] ?? 0) ?>

</div>

</div>

</div>

</div>