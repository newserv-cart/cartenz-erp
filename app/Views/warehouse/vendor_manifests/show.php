<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                📦 Vendor Manifest Detail
            </h3>

            <div class="text-muted">
                <?= esc($manifest['manifest_no'] ?? '-') ?> ·
                <?= esc($manifest['vendor_name'] ?? '-') ?> ·
                <span class="badge bg-<?= (
                    $manifest['status'] === 'OPEN'
                        ? 'info'
                        : (
                            $manifest['status'] === 'DISPATCHED'
                                ? 'success'
                                : 'secondary'
                        )
                ) ?>">
                    <?= esc($manifest['status'] ?? '-') ?>
                </span>
            </div>
        </div>

        <a
            href="<?= site_url('warehouse/vendor-manifests') ?>"
            class="btn btn-outline-secondary btn-sm"
        >
            Back to List
        </a>
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

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">
                        Total Shipment
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= (int) ($manifest['total_shipment'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">
                        Total Colly
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= (int) ($manifest['total_colly'] ?? 0) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">
                        Total Weight
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= number_format(
                            (float) ($manifest['total_weight'] ?? 0),
                            2,
                            ',',
                            '.'
                        ) ?> Kg
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-muted small">
                        Total Vendor Cost
                    </div>

                    <div class="fs-3 fw-bold">
                        Rp <?= number_format(
                            (float) (
                                $manifest['total_vendor_cost'] ?? 0
                            ),
                            0,
                            ',',
                            '.'
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>AWB</th>
                            <th>Destination</th>
                            <th>Service</th>
                            <th class="text-center">Colly</th>
                            <th class="text-end">Chargeable</th>
                            <th class="text-end">Vendor Cost</th>
                            <th class="text-end">Total Charge</th>
                            <th>Status</th>
                            <?php if ($manifest['status'] === 'OPEN'): ?>
                                <th style="width: 90px;"></th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($details)): ?>
                            <tr>
                                <td
                                    colspan="<?= $manifest['status'] === 'OPEN' ? 9 : 8 ?>"
                                    class="text-center py-5 text-muted"
                                >
                                    Belum ada shipment dalam manifest.
                                </td>
                            </tr>
                        <?php else: ?>

                            <?php foreach ($details as $detail): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= esc($detail['awb_no'] ?? '-') ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $detail['country_name']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $detail['service_name']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) ($detail['total_colly'] ?? 0) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) (
                                                $detail['chargeable_weight']
                                                ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </td>

                                    <td class="text-end">
                                        Rp <?= number_format(
                                            (float) ($detail['vendor_cost'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        Rp <?= number_format(
                                            (float) ($detail['total_charge'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-info">
                                            <?= esc(
                                                $detail['shipment_status']
                                                ?? '-'
                                            ) ?>
                                        </span>
                                    </td>

                                    <?php if ($manifest['status'] === 'OPEN'): ?>
                                        <td>
                                            <form
                                                method="post"
                                                action="<?= site_url(
                                                    'warehouse/vendor-manifests/'
                                                    . (int) $manifest['id']
                                                    . '/remove-shipment/'
                                                    . (int) $detail['detail_id']
                                                ) ?>"
                                                onsubmit="return confirm('Hapus shipment dari manifest?');"
                                            >
                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                >
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>
                </table>

            </div>

        </div>
    </div>

    <?php if (
        ($manifest['status'] ?? '') === 'OPEN'
        && !empty($details)
    ): ?>
        <div class="card shadow-sm mb-4 border-success">
            <div class="card-body text-end">
                <form
                    method="post"
                    action="<?= site_url(
                        'warehouse/vendor-manifests/'
                        . (int) $manifest['id']
                        . '/dispatch'
                    ) ?>"
                    onsubmit="return confirm('Serahkan seluruh shipment dalam manifest ini kepada vendor?');"
                >
                    <?= csrf_field() ?>

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        🚚 Dispatch & Handover to Vendor
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if (
        ($manifest['status'] ?? '') === 'OPEN'
        && !empty($eligibleShipments)
    ): ?>
        <div class="card shadow-sm">
            <div class="card-body">

                <h5 class="card-title mb-3">
                    Eligible Shipment untuk Vendor Ini
                </h5>

                <form
                    method="post"
                    action="<?= site_url(
                        'warehouse/vendor-manifests/'
                        . (int) $manifest['id']
                        . '/add-shipment'
                    ) ?>"
                    id="addShipmentForm"
                >
                    <?= csrf_field() ?>

                    <div class="table-responsive mb-3">

                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;">
                                        <input
                                            type="checkbox"
                                            id="selectAll"
                                            class="form-check-input"
                                        >
                                    </th>

                                    <th>AWB</th>
                                    <th>Destination</th>
                                    <th>Service</th>
                                    <th class="text-center">Colly</th>
                                    <th class="text-end">Chargeable</th>
                                    <th class="text-end">Vendor Cost</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($eligibleShipments as $shipment): ?>
                                    <tr>
                                        <td>
                                            <input
                                                type="checkbox"
                                                name="shipment_ids[]"
                                                value="<?= (int) $shipment['id'] ?>"
                                                class="form-check-input shipment-checkbox"
                                            >
                                        </td>

                                        <td>
                                            <strong>
                                                <?= esc($shipment['awb_no'] ?? '-') ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['country_name']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['service_name']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td class="text-center">
                                            <?= (int) (
                                                $shipment['total_colly']
                                                ?? 0
                                            ) ?>
                                        </td>

                                        <td class="text-end">
                                            <?= number_format(
                                                (float) (
                                                    $shipment[
                                                        'chargeable_weight'
                                                    ] ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            ) ?> Kg
                                        </td>

                                        <td class="text-end">
                                            Rp <?= number_format(
                                                (float) (
                                                    $shipment['vendor_cost']
                                                    ?? 0
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            </tbody>
                        </table>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Selected Shipment
                    </button>

                </form>

            </div>
        </div>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');

    const checkboxes = Array.from(
        document.querySelectorAll('.shipment-checkbox')
    );

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    const form = document.getElementById('addShipmentForm');

    if (form) {
        form.addEventListener('submit', function (event) {
            const selected = checkboxes.filter(
                checkbox => checkbox.checked
            );

            if (selected.length === 0) {
                event.preventDefault();
                alert('Pilih minimal satu shipment.');
            }
        });
    }
});
</script>

<?= $this->endSection() ?>
