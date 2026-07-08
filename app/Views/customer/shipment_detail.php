<h2>
📦 <?= $shipment['awb_no'] ?>
</h2>

<p>
Status :
<b><?= $shipment['shipment_status'] ?></b>
</p>

<hr>

<h3>
Tracking Timeline
</h3>

<?php foreach($tracking as $row): ?>

<div style="
margin-bottom:15px;
padding:10px;
border-left:4px solid #0d6efd;
background:#f8f9fa;
">

<b><?= $row['tracking_status'] ?></b>

<br>

<?= $row['tracking_notes'] ?>

<br>

<small>
<?= $row['event_time'] ?>
</small>

</div>

<?php endforeach; ?>