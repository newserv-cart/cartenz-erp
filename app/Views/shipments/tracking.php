<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="robots" content="noindex,nofollow">

    <title>
        Tracking Shipment | CARTENZ Global Express
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>
        body {
            background:
                linear-gradient(
                    180deg,
                    #f4f7fb 0%,
                    #ffffff 50%,
                    #f4f7fb 100%
                );
            min-height: 100vh;
        }

        .tracking-shell {
            max-width: 980px;
        }

        .brand-mark {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .03em;
        }

        .tracking-search {
            box-shadow: 0 1rem 2.5rem rgba(31, 45, 61, .08);
        }

        .status-card {
            border: 0;
            box-shadow: 0 .5rem 1.5rem rgba(31, 45, 61, .07);
        }

        .progress-step {
            min-width: 145px;
            flex: 1 0 145px;
            position: relative;
            text-align: center;
        }

        .progress-dot {
            width: 38px;
            height: 38px;
            margin: 0 auto .5rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #e9ecef;
            color: #6c757d;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }

        .progress-step.completed .progress-dot,
        .progress-step.current .progress-dot {
            background: #0d6efd;
            color: #fff;
        }

        .progress-step.current .progress-dot {
            box-shadow: 0 0 0 .35rem rgba(13, 110, 253, .15);
        }

        .timeline-item {
            position: relative;
            padding-left: 2.75rem;
            padding-bottom: 2rem;
        }

        .timeline-item:not(:last-child)::before {
            content: "";
            position: absolute;
            left: .72rem;
            top: 1.6rem;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-dot {
            position: absolute;
            left: 0;
            top: .2rem;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            background: #0d6efd;
            border: .3rem solid #dbeafe;
        }

        .safe-note {
            font-size: .9rem;
        }
    </style>
</head>

<body>
<?php
$shipment = $shipment ?? null;
$publicEvents = $publicEvents ?? [];
$currentPublicStatus =
    $currentPublicStatus ?? null;
$progressStatus =
    $progressStatus ?? $currentPublicStatus;
$notFound = $notFound ?? false;
$searchedAwb = $searchedAwb ?? '';

$statusNames = [
    'CREATED'          => 'Shipment Created',
    'PICKED_UP'        => 'Picked Up',
    'IN_TRANSIT'       => 'In Transit',
    'OUT_FOR_DELIVERY' => 'Out for Delivery',
    'DELIVERED'        => 'Delivered',
    'ON_HOLD'          => 'On Hold',
    'RETURN_TO_SENDER' => 'Return to Sender',
];

$statusClasses = [
    'CREATED'          => 'secondary',
    'PICKED_UP'        => 'primary',
    'IN_TRANSIT'       => 'warning',
    'OUT_FOR_DELIVERY' => 'info',
    'DELIVERED'        => 'success',
    'ON_HOLD'          => 'danger',
    'RETURN_TO_SENDER' => 'danger',
];

$normalSteps = [
    'CREATED',
    'PICKED_UP',
    'IN_TRANSIT',
    'OUT_FOR_DELIVERY',
    'DELIVERED',
];

$currentStepIndex = array_search(
    $progressStatus,
    $normalSteps,
    true
);

if ($currentStepIndex === false) {
    $currentStepIndex = 0;
}
?>

<div class="container tracking-shell py-4 py-lg-5">
    <header class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="brand-mark text-primary">
                CARTENZ GLOBAL EXPRESS
            </div>
            <div class="text-muted small">
                Public Shipment Tracking
            </div>
        </div>
    </header>

    <div class="card tracking-search border-0 mb-4">
        <div class="card-body p-4">
            <h1 class="h4 fw-bold mb-2">
                Track Your Shipment
            </h1>

            <p class="text-muted mb-3">
                Masukkan nomor AWB untuk melihat status pengiriman.
            </p>

            <?php if (session()->getFlashdata('tracking_error')): ?>
                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('tracking_error')) ?>
                </div>
            <?php endif; ?>

            <form
                method="post"
                action="<?= site_url('track/search') ?>"
                class="row g-2">

                <?= csrf_field() ?>

                <div class="col-md">
                    <label
                        for="awb_no"
                        class="visually-hidden">
                        Nomor AWB
                    </label>

                    <input
                        type="text"
                        class="form-control form-control-lg"
                        id="awb_no"
                        name="awb_no"
                        value="<?= esc(old('awb_no') ?? $searchedAwb) ?>"
                        placeholder="Contoh: CTZ260700015"
                        maxlength="50"
                        autocomplete="off"
                        required>
                </div>

                <div class="col-md-auto d-grid">
                    <button
                        type="submit"
                        class="btn btn-primary btn-lg px-4">
                        🔎 Track Shipment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($notFound): ?>
        <div class="alert alert-warning status-card">
            <h2 class="h5 fw-bold">
                Shipment tidak ditemukan
            </h2>

            <p class="mb-0">
                AWB
                <strong><?= esc($searchedAwb ?: '-') ?></strong>
                tidak ditemukan. Periksa kembali nomor yang dimasukkan.
            </p>
        </div>
    <?php endif; ?>

    <?php if ($shipment): ?>
        <?php
        $currentClass =
            $statusClasses[$currentPublicStatus]
            ?? 'primary';

        $currentName =
            $statusNames[$currentPublicStatus]
            ?? 'Shipment Update';
        ?>

        <div class="card status-card mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">
                            Air Waybill
                        </div>

                        <h2 class="h3 fw-bold mb-0">
                            <?= esc($shipment['awb_no']) ?>
                        </h2>
                    </div>

                    <div class="text-md-end">
                        <div class="text-muted small">
                            Current Status
                        </div>

                        <span class="badge bg-<?= esc($currentClass) ?> fs-6">
                            <?= esc($currentName) ?>
                        </span>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="small text-muted">
                            Destination
                        </div>
                        <div class="fw-semibold">
                            <?= esc($shipment['country_name'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="small text-muted">
                            Service
                        </div>
                        <div class="fw-semibold">
                            <?= esc($shipment['service_name'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="small text-muted">
                            Shipment Date
                        </div>
                        <div class="fw-semibold">
                            <?= esc($shipment['shipment_date'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="small text-muted">
                            Total Colly
                        </div>
                        <div class="fw-semibold">
                            <?= esc((string) ($shipment['total_colly'] ?? 0)) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($currentPublicStatus === 'ON_HOLD'): ?>
            <div class="alert alert-danger">
                Pengiriman sedang tertunda sementara.
                Tim CARTENZ sedang menanganinya.
            </div>
        <?php elseif ($currentPublicStatus === 'RETURN_TO_SENDER'): ?>
            <div class="alert alert-danger">
                Pengiriman sedang dalam proses dikembalikan
                kepada pengirim.
            </div>
        <?php endif; ?>

        <div class="card status-card mb-4">
            <div class="card-header bg-white fw-bold">
                Shipment Progress
            </div>

            <div class="card-body overflow-auto">
                <div class="d-flex gap-2 py-2">
                    <?php foreach ($normalSteps as $index => $step): ?>
                        <?php
                        $stepState = '';

                        if ($index < $currentStepIndex) {
                            $stepState = 'completed';
                        } elseif ($index === $currentStepIndex) {
                            $stepState = 'current';
                        }
                        ?>

                        <div class="progress-step <?= esc($stepState) ?>">
                            <div class="progress-dot">
                                <?= $index + 1 ?>
                            </div>

                            <div class="fw-semibold small">
                                <?= esc($statusNames[$step]) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card status-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                    Tracking Timeline
                </span>

                <span class="badge bg-light text-dark">
                    <?= count($publicEvents) ?> Update
                </span>
            </div>

            <div class="card-body p-4">
                <?php if (empty($publicEvents)): ?>
                    <div class="alert alert-info mb-0">
                        Status pengiriman sedang diproses.
                    </div>
                <?php else: ?>
                    <?php foreach (array_reverse($publicEvents) as $event): ?>
                        <?php
                        $eventStatus =
                            $event['status_code'];

                        $eventClass =
                            $statusClasses[$eventStatus]
                            ?? 'primary';
                        ?>

                        <div class="timeline-item">
                            <span class="timeline-dot"></span>

                            <div class="d-flex flex-column flex-md-row justify-content-between gap-1">
                                <div>
                                    <span class="badge bg-<?= esc($eventClass) ?>">
                                        <?= esc($event['status_name']) ?>
                                    </span>
                                </div>

                                <div class="small text-muted">
                                    <?= esc($event['event_time'] ?? '-') ?>
                                </div>
                            </div>

                            <div class="mt-2">
                                <?= esc($event['message']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <p class="text-center text-muted safe-note mt-4 mb-0">
            Untuk keamanan, halaman publik hanya menampilkan
            status yang relevan bagi customer.
        </p>
    <?php endif; ?>

    <footer class="text-center text-muted small mt-5">
        © <?= date('Y') ?> CARTENZ Global Express
    </footer>
</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
