<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">📥 Import Express Rate Excel</h3>
            <div class="text-muted">
                Vendor: <?= esc($vendor['vendor_name']) ?>
                (<?= esc($vendor['vendor_code']) ?>)
            </div>
        </div>

        <div class="d-flex gap-2">
            <a
                href="<?= site_url(
                    'master/vendors/express-import/template'
                ) ?>"
                class="btn btn-outline-success">
                Download Template
            </a>

            <a
                href="<?= site_url(
                    'master/vendors/' . $vendor['id']
                ) ?>"
                class="btn btn-outline-secondary">
                Back
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info">
        Importer menerima dua format:
        <strong>IMPORT_READY</strong>
        (SHIPMENT_TYPE, ZONE, WEIGHT, PRICE)
        atau matriks vendor
        <strong>WEIGHT × Zone</strong>.
        Currency untuk file ini pilih <strong>IDR</strong>.
    </div>

    <form
        method="post"
        action="<?= site_url(
            'master/vendors/' .
            $vendor['id'] .
            '/express-import/preview'
        ) ?>"
        enctype="multipart/form-data">

        <?= csrf_field() ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Express Service *</label>
                        <select
                            name="service_id"
                            class="form-select"
                            required>

                            <option value="">-- Select --</option>

                            <?php foreach ($services as $service): ?>
                                <option
                                    value="<?= (int) $service['id'] ?>"
                                    <?= (string) old('service_id')
                                        === (string) $service['id']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($service['service_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Currency *</label>
                        <select
                            name="currency_id"
                            class="form-select"
                            required>

                            <option value="">-- Select --</option>

                            <?php foreach ($currencies as $currency): ?>
                                <?php
                                $selectedCurrency = (string) (
                                    old('currency_id')
                                    ?? $defaultCurrencyId
                                    ?? ''
                                );
                                ?>

                                <option
                                    value="<?= (int) $currency['id'] ?>"
                                    <?= $selectedCurrency
                                        === (string) $currency['id']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($currency['_label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Effective Date *</label>
                        <input
                            type="date"
                            name="effective_date"
                            class="form-control"
                            value="<?= esc(
                                old('effective_date')
                                ?? date('Y-m-d')
                            ) ?>"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Import Mode *</label>
                        <select
                            name="import_mode"
                            class="form-select"
                            required>

                            <option
                                value="REPLACE"
                                <?= old('import_mode', 'REPLACE') === 'REPLACE'
                                    ? 'selected'
                                    : '' ?>>
                                REPLACE — ganti seluruh detail header tanggal yang sama
                            </option>

                            <option
                                value="APPEND"
                                <?= old('import_mode') === 'APPEND'
                                    ? 'selected'
                                    : '' ?>>
                                APPEND — tambah detail baru, duplikat ditolak
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Excel File (.xlsx) *</label>
                        <input
                            type="file"
                            name="rate_file"
                            class="form-control"
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            required>

                        <div class="form-text">
                            Maksimal 10 MB. Preview dibuat sebelum database berubah.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea
                            name="remarks"
                            class="form-control"
                            rows="3"><?= esc(
                                old('remarks')
                                ?? 'Express rate import IDR'
                            ) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    Parse & Preview Excel
                </button>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
