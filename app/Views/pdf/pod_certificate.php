<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>
        POD Certificate <?= esc($shipment['awb_no'] ?? '') ?>
    </title>

    <style>
        @page {
            margin: 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 21px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            margin-top: 5px;
            color: #666;
        }

        .status {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 10px;
            border: 1px solid #18864b;
            color: #18864b;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            width: 50%;
            padding: 5px 8px;
            vertical-align: top;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 7px 9px;
        }

        .details td {
            border: 1px solid #ddd;
            padding: 7px 9px;
            vertical-align: top;
        }

        .label {
            color: #666;
            font-size: 9px;
            margin-bottom: 3px;
        }

        .value {
            font-weight: bold;
        }

        .address {
            line-height: 1.5;
        }

        .pod-image {
            max-width: 100%;
            max-height: 330px;
            border: 1px solid #ccc;
            padding: 4px;
        }

        .signature {
            max-width: 260px;
            max-height: 120px;
            border: 1px solid #ccc;
            padding: 4px;
        }

        .footer {
            margin-top: 22px;
            border-top: 1px solid #aaa;
            padding-top: 8px;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="header">
    <div class="title">CARTENZ Proof of Delivery</div>

    <div class="subtitle">
        Official delivery confirmation certificate
    </div>

    <div class="status">DELIVERED</div>
</div>

<table class="summary">
    <tr>
        <td>
            <div class="label">AWB CARTENZ</div>
            <div class="value">
                <?= esc($shipment['awb_no'] ?? '-') ?>
            </div>
        </td>

        <td>
            <div class="label">Shipment Status</div>
            <div class="value">
                <?= esc($shipment['shipment_status'] ?? 'DELIVERED') ?>
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="label">Service</div>
            <div class="value">
                <?= esc($shipment['service_name'] ?? '-') ?>
            </div>
        </td>

        <td>
            <div class="label">Destination</div>
            <div class="value">
                <?= esc($shipment['country_name'] ?? '-') ?>
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <div class="label">Total Colly</div>
            <div class="value">
                <?= (int) ($shipment['total_colly'] ?? 0) ?>
            </div>
        </td>

        <td>
            <div class="label">Chargeable Weight</div>
            <div class="value">
                <?= number_format(
                    (float) ($shipment['chargeable_weight'] ?? 0),
                    2,
                    ',',
                    '.'
                ) ?> Kg
            </div>
        </td>
    </tr>
</table>

<div class="section">
    <div class="section-title">Sender & Receiver</div>

    <table class="details">
        <tr>
            <td width="50%">
                <div class="label">Sender</div>

                <div class="value">
                    <?= esc($shipment['sender_name'] ?? '-') ?>
                </div>

                <div>
                    <?= esc($shipment['sender_company'] ?? '') ?>
                </div>

                <div>
                    <?= esc($shipment['sender_phone'] ?? '') ?>
                </div>

                <div class="address">
                    <?= esc($shipment['sender_address'] ?? '') ?><br>

                    <?= esc(
                        implode(', ', array_filter([
                            $shipment['sender_city'] ?? null,
                            $shipment['sender_state'] ?? null,
                            $shipment['sender_postal_code'] ?? null,
                        ]))
                    ) ?>
                </div>
            </td>

            <td width="50%">
                <div class="label">Receiver</div>

                <div class="value">
                    <?= esc($shipment['receiver_name'] ?? '-') ?>
                </div>

                <div>
                    <?= esc($shipment['receiver_company'] ?? '') ?>
                </div>

                <div>
                    <?= esc($shipment['receiver_phone'] ?? '') ?>
                </div>

                <div class="address">
                    <?= esc($shipment['receiver_address'] ?? '') ?><br>

                    <?= esc(
                        implode(', ', array_filter([
                            $shipment['receiver_city'] ?? null,
                            $shipment['receiver_state'] ?? null,
                            $shipment['receiver_postal_code'] ?? null,
                        ]))
                    ) ?>
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Delivery Confirmation</div>

    <table class="details">
        <tr>
            <td>
                <div class="label">Delivered To</div>

                <div class="value">
                    <?= esc($shipment['delivered_to'] ?? '-') ?>
                </div>
            </td>

            <td>
                <div class="label">Relation</div>

                <div class="value">
                    <?= esc($shipment['delivery_relation'] ?? '-') ?>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="label">Delivered At</div>

                <div class="value">
                    <?= esc($shipment['delivered_at'] ?? '-') ?>
                </div>
            </td>

            <td>
                <div class="label">Recorded By</div>

                <div class="value">
                    CARTENZ Operations
                </div>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <div class="label">Delivery Notes</div>

                <div>
                    <?= nl2br(
                        esc($shipment['delivery_notes'] ?? '-')
                    ) ?>
                </div>
            </td>
        </tr>

        <?php if (
            !empty($shipment['delivery_latitude'])
            && !empty($shipment['delivery_longitude'])
        ): ?>

            <tr>
                <td colspan="2">
                    <div class="label">Delivery Location Verified</div>

                    <div class="value">
                        GPS location recorded by CARTENZ Operations
                    </div>
                </td>
            </tr>

        <?php endif; ?>
    </table>
</div>

<div class="section">
    <div class="section-title">Proof of Delivery Photo</div>

    <div style="padding-top: 10px; text-align: center;">
        <?php if (!empty($photoDataUri)): ?>
            <img
                src="<?= $photoDataUri ?>"
                alt="Proof of Delivery"
                class="pod-image"
            >
        <?php else: ?>
            <div>Photo is not available.</div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($signatureDataUri)): ?>
    <div class="section">
        <div class="section-title">Receiver Signature</div>

        <div style="padding-top: 10px;">
            <img
                src="<?= $signatureDataUri ?>"
                alt="Receiver Signature"
                class="signature"
            >
        </div>
    </div>
<?php endif; ?>

<div class="footer">
    Generated electronically by CARTENZ ERP on
    <?= esc($generatedAt ?? date('Y-m-d H:i:s')) ?>.

    This certificate confirms the delivery information recorded in the CARTENZ system.
</div>

</body>
</html>
