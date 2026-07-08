<h2>
📦 <?= $shipment['awb_no'] ?>
</h2>

<hr>

<h3>Customer</h3>

<p>
<?= $shipment['customer_name'] ?>
</p>

<p>
<?= $shipment['company_name'] ?>
</p>

<p>
<?= $shipment['phone'] ?>
</p>

<p>
<?= $shipment['email'] ?>
</p>

<hr>

<h3>Shipment</h3>

<p>
Weight :
<?= $shipment['actual_weight'] ?> Kg
</p>

<p>
Status :
<?= $shipment['shipment_status'] ?>
</p>

<p>
Remarks :
<?= $shipment['remarks'] ?>
</p>

<hr>


<div class="card mb-3">
    <div class="card-header">
        Tracking Update
    </div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<div class="mb-3">

    <div class="mb-3">

<?php foreach($statuses as $status): ?>

    <a
        href="<?= base_url(
            'shipments/quick-status/' .
            $shipment['id'] . '/' .
            $status['status_code']
        ) ?>"
        class="btn btn-<?= $status['status_color'] ?> btn-sm mb-1">

        <?= $status['status_name'] ?>

    </a>

<?php endforeach; ?>

</div>

</div>



    <div class="card-body">

        <form
            method="post"
            action="<?= base_url('shipments/tracking/add/'.$shipment['id']) ?>">

            <div class="mb-2">

                <label>Status</label>

                <select
                    name="tracking_status"
                    class="form-select">
                    <?php foreach($statuses as $status): ?>

                    <option
                     value="<?= $status['status_code'] ?>">

                            <?= $status['status_name'] ?>

                    </option>

                    <?php endforeach; ?>

                   

                </select>

            </div>

            <div class="mb-2">

                <label>Location</label>

                <input
                    type="text"
                    name="tracking_location"
                    class="form-control">

            </div>

            <div class="mb-2">

                <label>Notes</label>

                <textarea
                    name="tracking_notes"
                    class="form-control"></textarea>

            </div>

            <button
                class="btn btn-success">

                🚚 Add Tracking

            </button>

        </form>

    </div>
</div>



<hr>

<a
href="<?= site_url(
'pickup/request/' .
$shipment['id']
) ?>"
class="btn btn-primary">

🚚 Request Pickup

</a>



<div class="card">

    <div class="card-header">
        Tracking History
    </div>

    <div class="card-body">

        <?php foreach($tracking as $row): ?>

            <div class="border-start border-5 border-primary ps-3 mb-3">
                <h5 class="mb-1">
🚚 <?= $row['tracking_status'] ?>
</h5>

                <br>

                <?= $row['tracking_notes'] ?>

                <br>

                <small>

                    <?= $row['tracking_location'] ?>

                    |

                    <?= $row['event_time'] ?>

                </small>

            </div>

        <?php endforeach; ?>

    </div>

</div>



<p>
Weight :
<?= $shipment['actual_weight'] ?>
</p>

<p>
Chargeable :
<?= $shipment['chargeable_weight'] ?>
</p>

<p>
Value :
<?= $shipment['declared_value'] ?>
</p>

<hr>

<?php foreach($packages as $package): ?>

<h4>
COLLY #<?= $package['package_no'] ?>
</h4>

<ul>

<li>
Weight :
<?= $package['actual_weight'] ?>
</li>

<li>
Dimension :
<?= $package['length_cm'] ?>
x
<?= $package['width_cm'] ?>
x
<?= $package['height_cm'] ?>
</li>

<li>
Volume :
<?= $package['volume_weight'] ?>
</li>

</ul>

<table border="1" cellpadding="5">

<tr>
<th>Description</th>
<th>Qty</th>
<th>Value</th>
</tr>

<?php foreach($package['items'] as $item): ?>

<tr>
<td><?= $item['item_description'] ?></td>
<td><?= $item['quantity'] ?></td>
<td><?= $item['total_value'] ?></td>
</tr>

<?php endforeach; ?>

</table>

<hr>

<?php endforeach; ?>