<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$status = strtoupper(
    (string) ($manifest['status'] ?? 'OPEN')
);

$statusBadge = match ($status) {
    'DISPATCHED' => 'warning',
    'RECEIVED'   => 'success',
    'CLOSED'     => 'dark',
    default      => 'primary',
};
?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
        <div>
            <h3 class="mb-1">
                📦 <?= esc($manifest['manifest_no']) ?>
            </h3>

            <span class="badge bg-<?= esc($statusBadge) ?>">
                <?= esc($status) ?>
            </span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a
                href="<?= site_url(
                    'manifests/print/' . $manifest['id']
                ) ?>"
                target="_blank"
                class="btn btn-outline-success">
                🖨 Print PDF
            </a>

            <a
                href="<?= site_url('manifests') ?>"
                class="btn btn-outline-secondary">
                Back
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Manifest Date</div>
                    <div class="fw-bold">
                        <?= esc($manifest['manifest_date']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Shipment</div>
                    <div class="fw-bold fs-4">
                        <?= number_format(
                            (int) $liveTotals['total_shipment']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Live Total Weight</div>
                    <div class="fw-bold fs-4">
                        <?= number_format(
                            (float) $liveTotals['total_weight'],
                            2,
                            ',',
                            '.'
                        ) ?> Kg
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-light fw-bold">
            Manifest Workflow
        </div>

        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php if ($status === 'OPEN'): ?>
                    <form
                        method="post"
                        action="<?= site_url(
                            'manifests/dispatch/' .
                            $manifest['id']
                        ) ?>"
                        onsubmit="return confirm(
                            'Dispatch manifest ini? Shipment akan dikunci dan status internal diperbarui.'
                        );">

                        <?= csrf_field() ?>

                        <button
                            type="submit"
                            class="btn btn-warning"
                            <?= empty($details) ? 'disabled' : '' ?>>
                            🚚 Dispatch Manifest
                        </button>
                    </form>
                <?php elseif ($status === 'DISPATCHED'): ?>
                    <form
                        method="post"
                        action="<?= site_url(
                            'manifests/receive/' .
                            $manifest['id']
                        ) ?>"
                        onsubmit="return confirm(
                            'Konfirmasi manifest sudah diterima di main warehouse?'
                        );">

                        <?= csrf_field() ?>

                        <button
                            type="submit"
                            class="btn btn-success">
                            🏢 Receive Manifest
                        </button>
                    </form>
                <?php elseif ($status === 'RECEIVED'): ?>
                    <form
                        method="post"
                        action="<?= site_url(
                            'manifests/close/' .
                            $manifest['id']
                        ) ?>"
                        onsubmit="return confirm(
                            'Tutup manifest? Setelah CLOSED detail tidak dapat diubah.'
                        );">

                        <?= csrf_field() ?>

                        <button
                            type="submit"
                            class="btn btn-dark">
                            🔒 Close Manifest
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        Manifest sudah CLOSED dan seluruh detail terkunci.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($status === 'OPEN'): ?>
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white">
                Add Eligible Shipments
            </div>

            <div class="card-body">
                <?php if (empty($eligibleShipments)): ?>
                    <div class="alert alert-info mb-0">
                        Tidak ada shipment eligible.
                    </div>
                <?php else: ?>
                    <form
                        method="post"
                        action="<?= site_url(
                            'manifests/add-shipment/' .
                            $manifest['id']
                        ) ?>">

                        <?= csrf_field() ?>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">Select</th>
                                        <th>AWB</th>
                                        <th>Status</th>
                                        <th>Destination ID</th>
                                        <th class="text-end">Chargeable</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($eligibleShipments as $shipment): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input
                                                    type="checkbox"
                                                    name="shipment_ids[]"
                                                    value="<?= (int) $shipment['id'] ?>">
                                            </td>
                                            <td class="fw-semibold">
                                                <?= esc($shipment['awb_no']) ?>
                                            </td>
                                            <td>
                                                <?= esc($shipment['shipment_status']) ?>
                                            </td>
                                            <td>
                                                <?= esc((string) ($shipment['country_id'] ?? '-')) ?>
                                            </td>
                                            <td class="text-end">
                                                <?= number_format(
                                                    (float) ($shipment['chargeable_weight'] ?? 0),
                                                    2,
                                                    ',',
                                                    '.'
                                                ) ?> Kg
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-success">
                            📦 Add Selected Shipments
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light fw-bold">
            Shipments in Manifest
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>AWB</th>
                            <th>Internal Status</th>
                            <th>Public Status</th>
                            <th class="text-end">Live Weight</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($details)): ?>
                            <tr>
                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4">
                                    Manifest belum mempunyai shipment.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($details as $detail): ?>
                                <?php
                                $liveWeight =
                                    (float) ($detail['chargeable_weight'] ?? 0) > 0
                                    ? (float) $detail['chargeable_weight']
                                    : (float) (
                                        $detail['shipment_actual_weight']
                                        ?? $detail['actual_weight']
                                        ?? 0
                                    );
                                ?>

                                <tr>
                                    <td class="fw-semibold">
                                        <a
                                            href="<?= site_url(
                                                'shipments/detail/' .
                                                $detail['shipment_id']
                                            ) ?>">
                                            <?= esc($detail['awb_no']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?= esc($detail['shipment_status']) ?>
                                    </td>
                                    <td>
                                        <?= esc(
                                            $detail['public_tracking_status']
                                            ?? '-'
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            $liveWeight,
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </td>
                                    <td>
                                        <?php if ($status === 'OPEN'): ?>
                                            <form
                                                method="post"
                                                action="<?= site_url(
                                                    'manifests/remove-shipment/' .
                                                    $manifest['id'] . '/' .
                                                    $detail['shipment_id']
                                                ) ?>"
                                                onsubmit="return confirm(
                                                    'Lepas shipment ini dari manifest?'
                                                );">

                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger">
                                                    Remove
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">
                                                Locked
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
