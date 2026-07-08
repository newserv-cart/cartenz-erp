<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>
🚚 Tracking Result
</h2>

<div class="alert alert-info">

AWB :
<strong>
<?= esc($shipment['awb_no']) ?>
</strong>

<br>

Current Status :

<strong>
<?= esc(
    $latestTracking['tracking_status']
    ?? $shipment['shipment_status']
) ?>
</strong>

</div>

<table class="table table-bordered">

<tr>
    <th>Date</th>
    <th>Status</th>
    <th>Location</th>
    <th>Notes</th>
</tr>

<?php foreach($tracking as $t): ?>

<tr>

    <td><?= $t['event_time'] ?></td>

    <td><?= esc($t['tracking_status']) ?></td>

    <td><?= esc($t['tracking_location']) ?></td>

    <td><?= esc($t['tracking_notes']) ?></td>

</tr>

<?php endforeach; ?>

</table>

</div>