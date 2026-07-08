<!DOCTYPE html>
<html>
<head>
    <title>Tracking Shipment</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2>
        Tracking AWB :
        <?= esc($shipment['awb_no']) ?>
    </h2>

    <hr>

    <?php foreach($tracking as $row): ?>

        <div class="card mb-3">

            <div class="card-body">

                <h5>
                    <?= esc($row['tracking_status']) ?>
                </h5>

                <p>
                    <?= esc($row['tracking_notes']) ?>
                </p>

                <small>

                    <?= esc($row['tracking_location']) ?>

                    <br>

                    <?= esc($row['event_time']) ?>

                </small>

            </div>

        </div>

    <?php endforeach; ?>

</div>

</body>
</html>