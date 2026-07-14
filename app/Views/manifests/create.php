<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">📦 Create Manifest</h3>
            <div class="text-muted">
                Manifest baru dibuat dengan status OPEN.
            </div>
        </div>

        <a
            href="<?= site_url('manifests') ?>"
            class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form
                method="post"
                action="<?= site_url('manifests/store') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label
                        for="manifest_date"
                        class="form-label">
                        Manifest Date
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="manifest_date"
                        name="manifest_date"
                        value="<?= esc(
                            old('manifest_date') ?? date('Y-m-d')
                        ) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label
                        for="remarks"
                        class="form-label">
                        Remarks
                    </label>

                    <textarea
                        class="form-control"
                        id="remarks"
                        name="remarks"
                        rows="3"
                        maxlength="1000"><?= esc(
                            old('remarks') ?? ''
                        ) ?></textarea>
                </div>

                <button
                    type="submit"
                    class="btn btn-success">
                    Create Manifest
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
