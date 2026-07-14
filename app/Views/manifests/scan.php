<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">📱 Scan / Search Manifest</h3>

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
                action="<?= site_url('manifests/scan') ?>">

                <?= csrf_field() ?>

                <label
                    for="manifest_no"
                    class="form-label">
                    Manifest Number
                </label>

                <div class="input-group input-group-lg">
                    <input
                        type="text"
                        class="form-control"
                        id="manifest_no"
                        name="manifest_no"
                        maxlength="30"
                        autocomplete="off"
                        autofocus
                        required>

                    <button
                        type="submit"
                        class="btn btn-success">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
