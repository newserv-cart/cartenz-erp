<h2>🚚 Pickup Requests</h2>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Shipment</th>
            <th>Date</th>
            <th>Contact</th>
            <th>Driver</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($pickups as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['shipment_id'] ?></td>
            <td><?= $row['pickup_date'] ?></td>
            <td><?= $row['contact_name'] ?></td>
            <td><?= $row['driver_name'] ?? '-' ?></td>
            <td><?= $row['pickup_status'] ?></td>
            <td>
                <?php if($row['pickup_status'] == 'REQUESTED'): ?>
                    <a href="<?= site_url('pickups/assign/' . $row['id']) ?>" class="btn btn-warning btn-sm">
                        🚚 Assign Driver
                    </a>
                <?php endif; ?>

                <?php if($row['pickup_status'] == 'ASSIGNED'): ?>
                    <a href="<?= site_url('pickups/done/' . $row['id']) ?>" class="btn btn-success btn-sm">
                        ✅ Pickup Done
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>