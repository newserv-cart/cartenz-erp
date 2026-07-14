<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h1 {
            text-align: center;
            margin-bottom: 4px;
        }

        .subtitle {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #222;
            padding: 6px;
        }

        th {
            background: #efefef;
        }

        .text-right {
            text-align: right;
        }

        .meta {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <h1>CARTENZ GLOBAL EXPRESS</h1>

    <div class="subtitle">
        SHIPPING MANIFEST
    </div>

    <div class="meta">
        <strong>Manifest:</strong>
        <?= esc($manifest['manifest_no']) ?><br>

        <strong>Date:</strong>
        <?= esc($manifest['manifest_date']) ?><br>

        <strong>Status:</strong>
        <?= esc($manifest['status']) ?><br>

        <strong>Total Shipment:</strong>
        <?= number_format(
            (int) $liveTotals['total_shipment']
        ) ?><br>

        <strong>Total Weight:</strong>
        <?= number_format(
            (float) $liveTotals['total_weight'],
            2,
            '.',
            ','
        ) ?> Kg
    </div>

    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>AWB</th>
                <th>Internal Status</th>
                <th width="100">Weight</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($details as $index => $detail): ?>
                <?php
                $weight =
                    (float) ($detail['chargeable_weight'] ?? 0) > 0
                    ? (float) $detail['chargeable_weight']
                    : (float) (
                        $detail['shipment_actual_weight']
                        ?? $detail['actual_weight']
                        ?? 0
                    );
                ?>

                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($detail['awb_no']) ?></td>
                    <td><?= esc($detail['shipment_status']) ?></td>
                    <td class="text-right">
                        <?= number_format($weight, 2, '.', ',') ?> Kg
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
