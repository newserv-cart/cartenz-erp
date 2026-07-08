<h2>
📦 My Shipments
</h2>

<table border="1" cellpadding="5">

<tr>
    <th>AWB</th>
    <th>Status</th>
    <th>Weight</th>
    <th>Detail</th>
</tr>

<?php foreach($shipments as $shipment): ?>

<tr>

<td>
<?= $shipment['awb_no'] ?>
</td>

<td>
<?= $shipment['shipment_status'] ?>
</td>

<td>
<?= $shipment['actual_weight'] ?>
</td>


<td>

<a
href="<?= site_url(
'customer/shipment/'.$shipment['id']
) ?>"
class="btn btn-sm btn-primary">

📍 Tracking

</a>

</td>

</tr>

<?php endforeach; ?>

</table>