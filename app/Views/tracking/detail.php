

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        CARTENZ TRACKING -
        <?= esc($shipment['awb_no']) ?>
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body>

<?php

$badge = [
    'CREATED'          => 'secondary',
    'PICKED_UP'        => 'primary',
    'ARRIVED_HUB'      => 'info',
    'IN_TRANSIT'       => 'warning',
    'OUT_FOR_DELIVERY' => 'dark',
    'DELIVERED'        => 'success',
    'RETURN_TO_SENDER' => 'danger',
    'ON_HOLD'          => 'dark',
];

if (!empty($tracking)) {
    usort($tracking, function ($a, $b) {
        return strtotime($b['event_time']) - strtotime($a['event_time']);
    });
}

$currentStatus = $shipment['shipment_status'];

$displayStatus = [
    'DRAFT'            => 'Shipment Created',
    'PICKED_UP'        => 'Picked Up',
    'MANIFESTED'       => 'In Transit',
    'IN_TRANSIT_HUB'   => 'In Transit',
    'ARRIVED_HUB'      => 'In Transit',
    'OUT_FOR_DELIVERY' => 'Out For Delivery',
    'DELIVERED'        => 'Delivered',
    'ON_HOLD'          => 'On Hold',
    'RETURN_TO_SENDER' => 'Return To Sender'
];

$currentStatusCode = $shipment['shipment_status'];

$currentStatus =
    $displayStatus[$currentStatusCode]
    ?? $currentStatusCode;



?>

<div class="container mt-4">

    <div class="text-center mb-4">

        <h1 class="fw-bold text-primary">
            🚚 CARTENZ TRACKING
        </h1>

        <p class="text-muted">
            International Express & Logistics
        </p>

    </div>

    <div class="row mb-4">

        <div class="col-md-3 mb-2">
            <div class="card">
                <div class="card-body text-center">
                    <small>AWB</small>
<h6>
<?= esc($shipment['awb_no']) ?>
</h6>
                </div>
            </div>
        </div>

      <div class="col-md-3 mb-2">
    <div class="card">
        <div class="card-body text-center">
            <small>Weight</small>
            <h6>
                <?= $shipment['chargeable_weight'] ?? $shipment['actual_weight'] ?>
                Kg
            </h6>
        </div>
    </div>
</div>

        <div class="col-md-3 mb-2">
            <div class="card">
                <div class="card-body text-center">
                    <small>Status</small>
<h6>
<?= esc(
    $displayStatus[$shipment['shipment_status']]
    ?? $shipment['shipment_status']
) ?>
</h6>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <div class="card">
                <div class="card-body text-center">
                    <small>Shipment Type</small>
                    <h6>
                        <?= esc($shipment['shipment_mode'] ?? '-') ?>
                    </h6>
                </div>
            </div>
        </div>

    </div>

    <div class="alert alert-success text-center">

        <h5 class="mb-2">
            Current Shipment Status
        </h5>

        <h3>

            <span class="badge bg-<?= $badge[$currentStatusCode] ?? 'secondary' ?>">

                <?= esc($currentStatus) ?>

            </span>

        </h3>

    </div>

    <h4 class="mb-3">
        📍 Tracking Timeline
    </h4>

    <?php if (!empty($tracking)): ?>

        <?php foreach ($tracking as $row): ?>

            <div class="card mb-2">

                <div class="card-body">

                    <h5>

                        <span class="badge bg-<?= $badge[$row['tracking_status']] ?? 'secondary' ?>">

                            <?= esc(
    $displayStatus[$row['tracking_status']]
    ?? $row['tracking_status']
) ?>

                        </span>

                    </h5>

                    <p class="mb-1">

                        <?= esc($row['tracking_notes'] ?? '-') ?>

                    </p>

                    <small class="text-muted">

                        <?= esc($row['tracking_location'] ?? '-') ?>

                        |

                        <?= esc($row['event_time'] ?? '-') ?>

                    </small>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="alert alert-warning">
            No Tracking History Found
        </div>

    <?php endif; ?>

</div>

</body>
</html>