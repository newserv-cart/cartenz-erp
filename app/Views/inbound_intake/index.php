<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3>📥 Inbound Intake</h3>
        <p class="text-muted mb-0">
            Scan atau masukkan AWB untuk menerima barang di cabang.
        </p>
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

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form
                method="post"
                action="<?= site_url('inbound-intake/scan') ?>"
            >
                <?= csrf_field() ?>

                <label for="awb_no" class="form-label">
                    Scan / Input AWB
                </label>

                <div class="input-group">
                    <input
                        type="text"
                        name="awb_no"
                        id="awb_no"
                        class="form-control form-control-lg"
                        value="<?= esc(old('awb_no') ?? '') ?>"
                        placeholder="Contoh: CTZ260700036"
                        autocomplete="off"
                        autofocus
                        required
                    >

                    <button
                        type="submit"
                        class="btn btn-primary px-4"
                    >
                        Cari Shipment
                    </button>
                </div>
            </form>

        </div>
    </div>

    <?php if (!empty($shipment)): ?>

        <?php
        $status = strtoupper(
            (string) ($shipment['shipment_status'] ?? '-')
        );

        $ownerType = strtoupper(
            (string) ($shipment['owner_type'] ?? '-')
        );
        ?>

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between">
                <strong>
                    📦 <?= esc($shipment['awb_no'] ?? '-') ?>
                </strong>

                <span class="badge bg-secondary">
                    <?= esc($status) ?>
                </span>
            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-3">
                        <div class="text-muted">Shipment Owner</div>
                        <strong><?= esc($ownerType) ?></strong>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted">Planned Handover</div>
                        <strong>
                            <?= esc(
                                $shipment['planned_handover_method'] ?? '-'
                            ) ?>
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Colly</div>
                        <strong>
                            <?= (int) ($shipment['total_colly'] ?? 0) ?>
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Actual Weight</div>
                        <strong>
                            <?= number_format(
                                (float) ($shipment['actual_weight'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) ?> Kg
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Chargeable</div>
                        <strong>
                            <?= number_format(
                                (float) ($shipment['chargeable_weight'] ?? 0),
                                2,
                                ',',
                                '.'
                            ) ?> Kg
                        </strong>
                    </div>

                </div>

                <hr>

                <?php if (!empty($validationError)): ?>

                    <div class="alert alert-warning mb-0">
                        <?= esc($validationError) ?>
                    </div>

                <?php elseif ($canReceive): ?>

                    <div class="alert alert-success">
                        Shipment valid dan siap diterima di cabang.
                    </div>

                    <form
                        method="post"
                        action="<?= site_url('inbound-intake/receive') ?>"
                        onsubmit="return confirm(
                            'Terima shipment <?= esc($shipment['awb_no'] ?? '') ?> di cabang?'
                        );"
                    >
                        <?= csrf_field() ?>

                        <input
                            type="hidden"
                            name="shipment_id"
                            value="<?= (int) ($shipment['id'] ?? 0) ?>"
                        >

                        <input
                            type="hidden"
                            name="awb_no"
                            value="<?= esc($shipment['awb_no'] ?? '') ?>"
                        >

                        <div class="row g-3 mb-3">

                            <div class="col-md-3">
                                <label class="form-label">
                                    Declared Weight
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= number_format(
                                        (float) ($shipment['actual_weight'] ?? 0),
                                        2,
                                        ',',
                                        '.'
                                    ) ?> Kg"
                                    readonly
                                >
                            </div>

                            <div class="col-md-3">
                                <label for="verified_actual_weight" class="form-label">
                                    Verified Actual Weight *
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="verified_actual_weight"
                                        id="verified_actual_weight"
                                        class="form-control"
                                        value="<?= esc(
                                            number_format(
                                                (float) ($shipment['actual_weight'] ?? 0),
                                                2,
                                                '.',
                                                ''
                                            )
                                        ) ?>"
                                        min="0.01"
                                        step="0.01"
                                        required
                                    >

                                    <span class="input-group-text">Kg</span>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label for="verified_colly" class="form-label">
                                    Verified Colly *
                                </label>

                                <input
                                    type="number"
                                    name="verified_colly"
                                    id="verified_colly"
                                    class="form-control"
                                    value="<?= max(
                                        1,
                                        (int) ($shipment['total_colly'] ?? 1)
                                    ) ?>"
                                    min="1"
                                    step="1"
                                    required
                                >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Volume Weight
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="<?= number_format(
                                        (float) ($shipment['volume_weight'] ?? 0),
                                        2,
                                        ',',
                                        '.'
                                    ) ?> Kg"
                                    readonly
                                >
                            </div>

                        </div>

                        <div class="row g-3 mb-3">

                            <div class="col-md-4">
                                <label class="form-label">
                                    Package Condition
                                </label>

                                <select
                                    name="package_condition"
                                    class="form-select"
                                    required
                                >
                                    <option value="GOOD">Good / Baik</option>
                                    <option value="DAMAGED">Damaged / Rusak</option>
                                    <option value="REPACK_REQUIRED">
                                        Repack Required
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">
                                    Receiving Remarks
                                </label>

                                <input
                                    type="text"
                                    name="receiving_remarks"
                                    class="form-control"
                                    maxlength="500"
                                    placeholder="Catatan kondisi barang jika diperlukan"
                                >
                            </div>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            ✅ Receive Shipment
                        </button>
                    </form>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const awbInput = document.getElementById('awb_no');

    if (awbInput) {
        awbInput.focus();
        awbInput.select();
    }
});
</script>

<?= $this->endSection() ?>
