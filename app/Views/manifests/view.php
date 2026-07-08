<h2>
📦 <?= $manifest['manifest_no'] ?>
</h2>

<?php if($manifest['status']=='DISPATCHED'): ?>

<div style="
background:#fff3cd;
padding:15px;
margin:15px 0;
border:1px solid #ffeeba;
font-size:20px;
font-weight:bold;
">

🚚 Manifest sedang dalam perjalanan dan menunggu diterima.

</div>

<?php endif; ?>

<p>
Status :
<strong><?= $manifest['status'] ?></strong>
</p>

<p>
Total Shipment :
<?= $manifest['total_shipment'] ?>
</p>

<p>
Total Weight :
<?= $manifest['total_weight'] ?> Kg
</p>

<br>

<!-- OPEN -->
<?php if($manifest['status'] == 'OPEN'): ?>

<a
    href="<?= base_url('manifests/dispatch/'.$manifest['id']) ?>"
    class="btn btn-warning">

    🚚 Dispatch Manifest

</a>

<?php endif; ?>


<!-- DISPATCHED -->
<?php if($manifest['status'] == 'DISPATCHED'): ?>

<br><br>

<a
    href="<?= site_url('manifests/receive/'.$manifest['id']) ?>"
    style="
        display:inline-block;
        background:#28a745;
        color:white;
        padding:20px 40px;
        font-size:24px;
        font-weight:bold;
        text-decoration:none;
        border-radius:8px;
    ">

    🏢 RECEIVE MANIFEST

</a>

<?php endif; ?>

<!-- RECEIVED -->
<?php if($manifest['status'] == 'RECEIVED'): ?>

<a
    href="<?= base_url('manifests/close/'.$manifest['id']) ?>"
    class="btn btn-dark">

    🔒 Close Manifest

</a>

<?php endif; ?>


<a href="<?= site_url(
'manifest/print/'.$manifest['id']
) ?>"
class="btn btn-success">

🖨 Print Manifest

</a>

<?php if($manifest['status']=='DISPATCHED'): ?>

<a href="<?= site_url(
'manifests/receive/'.$manifest['id']
) ?>"
class="btn btn-success">

🏢 RECEIVE MANIFEST

</a>

<?php endif; ?>


<hr>

<?php if($manifest['status'] == 'OPEN'): ?>

<form
    method="post"
    action="<?= base_url('manifests/add-shipment/'.$manifest['id']) ?>">

    <?= csrf_field() ?>

    <table border="1" cellpadding="5">

        <tr>
            <th>Pilih</th>
            <th>AWB</th>
            <th>Status</th>
            <th>Weight</th>
        </tr>

        <?php foreach($shipments as $shipment): ?>

        <tr>

            <td>
                <input
                    type="checkbox"
                    name="shipment_ids[]"
                    value="<?= $shipment['id'] ?>">
            </td>

            <td><?= $shipment['awb_no'] ?></td>

            <td><?= $shipment['shipment_status'] ?></td>

            <td><?= $shipment['actual_weight'] ?></td>

        </tr>

        <?php endforeach; ?>

    </table>

    <br>

    <button class="btn btn-success">

        📦 Add To Manifest

    </button>

</form>

<hr>

<?php endif; ?>


<h3>
📦 Shipment Dalam Manifest
</h3>

<table border="1" cellpadding="5">

<tr>
    <th>AWB</th>
    <th>Weight</th>
</tr>

<?php foreach($details as $detail): ?>

<tr>

    <td><?= $detail['awb_no'] ?></td>

    <td><?= $detail['actual_weight'] ?></td>

</tr>

<?php endforeach; ?>

</table>