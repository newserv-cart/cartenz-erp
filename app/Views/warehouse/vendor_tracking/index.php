<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-1">
            📍 Vendor Tracking Update
        </h3>

        <div class="text-muted">
            Cari menggunakan AWB CARTENZ atau Vendor AWB.
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

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form
                method="get"
                action="<?= site_url(
                    'warehouse/vendor-tracking'
                ) ?>"
            >
                <label for="q" class="form-label">
                    AWB CARTENZ / Vendor AWB
                </label>

                <div class="input-group">
                    <input
                        type="text"
                        name="q"
                        id="q"
                        class="form-control form-control-lg"
                        value="<?= esc($search ?? '') ?>"
                        placeholder="CTZ260700030 atau MARS2607260001"
                        autocomplete="off"
                        autofocus
                        required
                    >

                    <button
                        type="submit"
                        class="btn btn-primary px-4"
                    >
                        Search Shipment
                    </button>
                </div>
            </form>

        </div>
    </div>

    <?php if (
        !empty($search)
        && empty($shipment)
    ): ?>
        <div class="alert alert-warning">
            Shipment tidak ditemukan.
        </div>
    <?php endif; ?>

    <?php if (!empty($shipment)): ?>

        <div class="card shadow-sm mb-4">

            <div
                class="card-header d-flex justify-content-between align-items-center"
            >
                <strong>
                    📦 <?= esc($shipment['awb_no'] ?? '-') ?>
                </strong>

                <span class="badge bg-primary">
                    <?= esc(
                        $shipment['shipment_status'] ?? '-'
                    ) ?>
                </span>
            </div>

            <div class="card-body">

                <div class="row g-3 mb-4">

                    <div class="col-md-3">
                        <div class="text-muted">Vendor</div>
                        <strong>
                            <?= esc(
                                $shipment['vendor_name'] ?? '-'
                            ) ?>
                        </strong>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted">Vendor AWB</div>
                        <strong>
                            <?= esc(
                                $shipment['vendor_awb'] ?? '-'
                            ) ?>
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Destination</div>
                        <strong>
                            <?= esc(
                                $shipment['country_name'] ?? '-'
                            ) ?>
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Service</div>
                        <strong>
                            <?= esc(
                                $shipment['service_name'] ?? '-'
                            ) ?>
                        </strong>
                    </div>

                    <div class="col-md-2">
                        <div class="text-muted">Chargeable</div>
                        <strong>
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
                        </strong>
                    </div>

                </div>

                <?php if (!empty($allowedNextStatuses)): ?>

                    <form
                        method="post"
                        action="<?= site_url(
                            'warehouse/vendor-tracking/'
                            . $shipment['id']
                            . '/update'
                        ) ?>"
                        onsubmit="return confirm(
                            'Simpan pembaruan tracking ini?'
                        );"
                    >
                        <?= csrf_field() ?>

                        <div class="row g-3">

                            <div class="col-md-3">
                                <label class="form-label">
                                    Next Status *
                                </label>

                                <select
                                    name="shipment_status"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        -- Select Status --
                                    </option>

                                    <?php foreach (
                                        $allowedNextStatuses as $status
                                    ): ?>
                                        <option
                                            value="<?= esc($status) ?>"
                                        >
                                            <?= esc($status) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Event Location *
                                </label>

                                <input
                                    type="text"
                                    name="location"
                                    class="form-control"
                                    placeholder="Contoh: Kuala Lumpur Hub"
                                    maxlength="150"
                                    required
                                >
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    Event Time *
                                </label>

                                <input
                                    type="datetime-local"
                                    name="event_at"
                                    class="form-control"
                                    value="<?= date(
                                        'Y-m-d\TH:i'
                                    ) ?>"
                                    required
                                >
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button
                                    type="submit"
                                    class="btn btn-success w-100"
                                >
                                    Update Tracking
                                </button>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    Description
                                </label>

                                <textarea
                                    name="description"
                                    class="form-control"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Kosongkan untuk memakai keterangan otomatis."
                                ></textarea>
                            </div>

                        </div>
                    </form>

                <?php else: ?>

                    <div class="alert alert-info mb-0">
                        Tidak ada transisi status berikutnya
                        untuk shipment ini.
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <strong>Tracking Timeline</strong>
            </div>

            <div class="card-body">

                <?php if (empty($tracking)): ?>

                    <div class="text-muted">
                        Belum ada tracking.
                    </div>

                <?php else: ?>

                    <?php foreach ($tracking as $event): ?>

                        <div class="border-start ps-3 pb-4">

                            <strong>
                                <?= esc($event['status'] ?? '-') ?>
                            </strong>

                            <div class="small text-muted">
                                <?= esc($event['location'] ?? '-') ?>
                                ·
                                <?= esc($event['created_at'] ?? '-') ?>
                            </div>

                            <div class="mt-1">
                                <?= esc(
                                    $event['description'] ?? ''
                                ) ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>
