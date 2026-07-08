<h2>
📦 Pending Shipment Requests
</h2>

<table border="1" cellpadding="5">

<tr>
    <th>AWB</th>
    <th>Customer</th>
    <th>Weight</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php foreach($shipments as $shipment): ?>

<tr>

<td>
<a href="<?= base_url(
'shipments/view/' .
$shipment['id']
) ?>">

<?= $shipment['awb_no'] ?>

</a>
</td>

<td>

<b>
<?= $shipment['customer_name'] ?>
</b>

<br>

<?= $shipment['company_name'] ?>

</td>

<td> <?= $shipment['phone'] ?> 

</td> 

<td> <?= $shipment['email'] ?> 

</td>

<td>
<?= $shipment['actual_weight'] ?>
</td>

<td>
<?= $shipment['shipment_status'] ?>
</td>

<td>

<a href="<?= base_url(
'shipments/approve/'.$shipment['id']
) ?>">

✅ Approve

</a>

|

<a href="<?= base_url(
    'shipments/reject/' .
    $shipment['id']
) ?>"
onclick="return confirm(
'Reject shipment ini?'
)">
    ❌ Reject
</a>

</td>

</tr>

<?php endforeach; ?>

</table>