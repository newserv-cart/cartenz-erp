<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">Add Express Rate Header</h3>
            <div class="text-muted">
                <?= esc($vendor['vendor_name']) ?>
            </div>
        </div>

        <a
            href="<?= site_url(
                'master/vendors/' . $vendor['id']
            ) ?>"
            class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form
        method="post"
        action="<?= esc($formAction) ?>">

        <?= csrf_field() ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Express Service *</label>
                        <select
                            class="form-select"
                            name="service_id"
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
                            class="form-select"
                            name="currency_id"
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
                            class="form-control"
                            name="effective_date"
                            value="<?= esc(
                                old('effective_date')
                                ?? date('Y-m-d')
                            ) ?>"
                            required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea
                            class="form-control"
                            name="remarks"
                            rows="3"><?= esc(
                                old('remarks') ?? ''
                            ) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button
                    type="submit"
                    class="btn btn-success">
                    Create Header
                </button>
            </div>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
