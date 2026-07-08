<div class="col-lg-6">

<div class="card shadow mb-3">

<div class="card-header bg-success text-white">

🏢 Vendor Cost

</div>

<table class="table table-striped mb-0">

<tr>
<th>Vendor Freight</th>
<td class="text-end">
Rp <?= number_format($shipment['vendor_cost'] ?? 0) ?>
</td>
</tr>

<tr>
<th>Fuel</th>
<td class="text-end">
Rp <?= number_format($shipment['fuel_charge'] ?? 0) ?>
</td>
</tr>

<tr>
<th>Remote Area</th>
<td class="text-end">
Rp <?= number_format($shipment['vendor_remote_charge'] ?? 0) ?>
</td>
</tr>

<tr>
<th>Go Green</th>
<td class="text-end">
Rp <?= number_format($shipment['go_green_charge'] ?? 0) ?>
</td>
</tr>

<tr>
<th>VAT</th>
<td class="text-end">
Rp <?= number_format($shipment['vat_amount'] ?? 0) ?>
</td>
</tr>

<tr class="table-success">

<th>TOTAL VENDOR</th>

<th class="text-end">

Rp <?= number_format($shipment['vendor_total'] ?? 0) ?>

</th>

</tr>

</table>

</div>

</div>