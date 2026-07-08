<!DOCTYPE html>
<html>
<head>
    <title>Shipment Module</title>
</head>
<body>



<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between mb-3">

        <h3>Shipment List 🚚</h3>

        <a href="<?= base_url('shipments/create') ?>"
           class="btn btn-primary">

            + Create Shipment

        </a>

    </div>

    <?php if(session()->getFlashdata('success')): ?>

        <div class="alert alert-success">

            <?= session()->getFlashdata('success') ?>

        </div>

    <?php endif; ?>

    <div class="card">

        <div class="card-body">

            <table class="table table-bordered table-striped">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>AWB</th>
                        <th>Colly</th>
                        <th>Weight</th>
                        <th>Chargeable</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>

                </thead>

                <tbody>

                <?php foreach($shipments as $row): ?>

                    <tr>

                        <td><?= $row['id'] ?></td>

                        <td>
                            <strong>
                                <?= $row['awb_no'] ?>
                            </strong>
                        </td>

                        <td><?= $row['total_colly'] ?></td>

                        <td><?= $row['actual_weight'] ?></td>

                        <td><?= $row['chargeable_weight'] ?></td>

                        <td><?= number_format($row['declared_value'],2) ?></td>

                        <td>
                            <span class="badge bg-secondary">

                               <?php

$color = 'secondary';

switch($row['shipment_status'])
{
    case 'CREATED':
        $color = 'primary';
        break;

    case 'PICKED_UP':
        $color = 'info';
        break;

    case 'IN_TRANSIT':
        $color = 'warning';
        break;

    case 'OUT_FOR_DELIVERY':
        $color = 'dark';
        break;

    case 'DELIVERED':
        $color = 'success';
        break;
}

?>

<span class="badge bg-<?= $color ?>">
    <?= $row['shipment_status'] ?>
</span>

                            </span>
                        </td>

                       
                <td>
    <a href="<?= site_url('shipments/detail/'.$row['id']) ?>"
   class="btn btn-sm btn-info">

    👁 Detail

</a>
    <a href="<?= base_url('shipments/edit/'.$row['id']) ?>"
       class="btn btn-sm btn-warning">
       Edit
    </a>

    <a href="<?= base_url('shipments/delete/'.$row['id']) ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Delete Shipment?')">
       Delete
    </a>

</td>

                    </tr>

                <?php endforeach; ?>




                </tbody>

            </table>

        </div>

    </div>

</div>



</body>
</html>