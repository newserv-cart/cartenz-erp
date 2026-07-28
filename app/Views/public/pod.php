<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Proof of Delivery <?= esc($pod['awb_no'] ?? '') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-4" style="max-width: 960px;">

    <div class="card shadow-sm">

        <div class="card-header bg-success text-white py-3">

            <h4 class="mb-1">
                CARTENZ Proof of Delivery
            </h4>

            <div>
                Official delivery confirmation
            </div>

        </div>

        <div class="card-body">

            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <div class="text-muted">AWB CARTENZ</div>
                    <strong>
                        <?= esc($pod['awb_no'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Shipment Status</div>
                    <strong>
                        <?= esc(
                            $pod['shipment_status'] ?? 'DELIVERED'
                        ) ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Destination</div>
                    <strong>
                        <?= esc($pod['country_name'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Service</div>
                    <strong>
                        <?= esc($pod['service_name'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Total Colly</div>
                    <strong>
                        <?= (int) ($pod['total_colly'] ?? 0) ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Chargeable Weight</div>
                    <strong>
                        <?= number_format(
                            (float) (
                                $pod['chargeable_weight'] ?? 0
                            ),
                            2,
                            ',',
                            '.'
                        ) ?> Kg
                    </strong>
                </div>

            </div>

            <hr>

            <div class="row g-4 mb-4">

                <div class="col-md-6">
                    <h6>Sender</h6>

                    <strong>
                        <?= esc($pod['sender_name'] ?? '-') ?>
                    </strong>

                    <div>
                        <?= esc($pod['sender_company'] ?? '') ?>
                    </div>

                    <div>
                        <?= esc($pod['sender_phone'] ?? '') ?>
                    </div>

                    <div class="mt-2">
                        <?= esc($pod['sender_address'] ?? '') ?>
                    </div>

                    <div>
                        <?= esc(
                            implode(', ', array_filter([
                                $pod['sender_city'] ?? null,
                                $pod['sender_state'] ?? null,
                                $pod['sender_postal_code'] ?? null,
                            ]))
                        ) ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6>Receiver</h6>

                    <strong>
                        <?= esc($pod['receiver_name'] ?? '-') ?>
                    </strong>

                    <div>
                        <?= esc($pod['receiver_company'] ?? '') ?>
                    </div>

                    <div>
                        <?= esc($pod['receiver_phone'] ?? '') ?>
                    </div>

                    <div class="mt-2">
                        <?= esc($pod['receiver_address'] ?? '') ?>
                    </div>

                    <div>
                        <?= esc(
                            implode(', ', array_filter([
                                $pod['receiver_city'] ?? null,
                                $pod['receiver_state'] ?? null,
                                $pod['receiver_postal_code'] ?? null,
                            ]))
                        ) ?>
                    </div>
                </div>

            </div>

            <hr>

            <h5 class="mb-3">Delivery Confirmation</h5>

            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <div class="text-muted">Delivered To</div>
                    <strong>
                        <?= esc($pod['delivered_to'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Relation</div>
                    <strong>
                        <?= esc(
                            $pod['delivery_relation'] ?? '-'
                        ) ?>
                    </strong>
                </div>

                <div class="col-md-4">
                    <div class="text-muted">Delivered At</div>
                    <strong>
                        <?= esc($pod['delivered_at'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-12">
                    <div class="text-muted">Delivery Notes</div>

                    <div>
                        <?= nl2br(
                            esc($pod['delivery_notes'] ?? '-')
                        ) ?>
                    </div>
                </div>

            </div>

            <div class="mb-4">
                <div class="text-muted mb-2">
                    Proof of Delivery Photo
                </div>

                <a
                    href="<?= site_url(
                        'pod/public/'
                        . $token
                        . '/file/photo'
                    ) ?>"
                    target="_blank"
                >
                    <img
                        src="<?= site_url(
                            'pod/public/'
                            . $token
                            . '/file/photo'
                        ) ?>"
                        alt="Proof of Delivery"
                        class="img-fluid rounded border"
                        style="max-height: 520px;"
                    >
                </a>
            </div>

            <?php if (
                !empty($pod['pod_signature_path'])
            ): ?>

                <div class="mb-4">
                    <div class="text-muted mb-2">
                        Receiver Signature
                    </div>

                    <img
                        src="<?= site_url(
                            'pod/public/'
                            . $token
                            . '/file/signature'
                        ) ?>"
                        alt="Receiver Signature"
                        class="img-fluid rounded border"
                        style="max-height: 220px;"
                    >
                </div>

            <?php endif; ?>

            <a
                href="<?= site_url(
                    'pod/public/' . $token . '/pdf'
                ) ?>"
                target="_blank"
                class="btn btn-danger"
            >
                📄 Open POD Certificate
            </a>

        </div>
    </div>

    <div class="text-center text-muted small mt-3">
        This secure document is electronically generated
        by CARTENZ ERP.
    </div>

</div>

</body>
</html>
