<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">📱 Shipment Branch Scan</h3>
            <div class="text-muted">
                Scanner barcode dapat langsung mengetik AWB ke kolom ini.
            </div>
        </div>

        <a
            href="<?= site_url('shipments') ?>"
            class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">User</div>
                    <div class="fw-bold">
                        <?= esc($profile['username'] ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Branch</div>
                    <div class="fw-bold">
                        <?= esc($profile['branch_code'] ?? '-') ?>
                        ·
                        <?= esc($profile['branch_name'] ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small">Staff Type</div>
                    <div class="fw-bold">
                        <?= esc($profile['staff_type'] ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form
                method="post"
                action="<?= site_url('operations/scan') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label
                        for="awb_no"
                        class="form-label fw-bold">
                        AWB / Barcode *
                    </label>

                    <input
                        type="text"
                        class="form-control form-control-lg"
                        id="awb_no"
                        name="awb_no"
                        value="<?= esc(old('awb_no') ?? '') ?>"
                        autocomplete="off"
                        maxlength="50"
                        autofocus
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Scan Type *
                    </label>

                    <div class="d-flex flex-wrap gap-4">
                        <?php if (
                            (int) (
                                $profile['can_scan_pickup']
                                ?? 0
                            ) === 1
                        ): ?>
                            <div class="form-check">
                                <input
                                    type="radio"
                                    class="form-check-input"
                                    id="scan_pickup"
                                    name="scan_type"
                                    value="PICKUP"
                                    required>

                                <label
                                    class="form-check-label"
                                    for="scan_pickup">
                                    🚚 Courier Pickup
                                </label>
                            </div>
                        <?php endif; ?>

                        <?php if (
                            (int) (
                                $profile['can_scan_dropoff']
                                ?? 0
                            ) === 1
                        ): ?>
                            <div class="form-check">
                                <input
                                    type="radio"
                                    class="form-check-input"
                                    id="scan_dropoff"
                                    name="scan_type"
                                    value="DROPOFF"
                                    checked
                                    required>

                                <label
                                    class="form-check-label"
                                    for="scan_dropoff">
                                    🏢 Branch Drop-off
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label
                        for="notes"
                        class="form-label">
                        Notes
                    </label>

                    <textarea
                        class="form-control"
                        id="notes"
                        name="notes"
                        rows="3"
                        maxlength="500"><?= esc(
                            old('notes') ?? ''
                        ) ?></textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-success btn-lg">
                    Process Scan
                </button>
            </form>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <strong>Pickup:</strong>
        CREATED/DRAFT → PICKED_UP.
        <br>
        <strong>Drop-off di branch:</strong>
        → RECEIVED_AT_ORIGIN_BRANCH.
        <br>
        <strong>Drop-off di JKT-HO:</strong>
        → RECEIVED_AT_MAIN_WAREHOUSE.
    </div>
</div>

<?= $this->endSection() ?>
