<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">Express Rate Details</h3>
            <div class="text-muted">
                <?= esc($vendor['vendor_name']) ?>
                ·
                <?= esc($service['service_name'] ?? '-') ?>
                ·
                <?= esc($header['effective_date']) ?>
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

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-primary text-white">
            Add Express Rate Detail
        </div>

        <div class="card-body">
            <form
                method="post"
                action="<?= site_url(
                    'master/vendors/' .
                    $vendor['id'] .
                    '/express-rates/' .
                    $header['id'] .
                    '/details/store'
                ) ?>">

                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Shipment Type *</label>
                        <select
                            class="form-select"
                            name="shipment_type"
                            required>

                            <option value="DOCUMENT">DOCUMENT</option>
                            <option value="PACKAGE">PACKAGE</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Zone *</label>
                        <input
                            type="number"
                            class="form-control"
                            name="zone"
                            min="1"
                            max="99"
                            value="<?= esc(old('zone') ?? 1) ?>"
                            required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Weight *</label>
                        <input
                            type="number"
                            class="form-control"
                            name="weight"
                            min="0.01"
                            step="0.01"
                            value="<?= esc(old('weight') ?? 0.5) ?>"
                            required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Price *</label>
                        <input
                            type="number"
                            class="form-control"
                            name="price"
                            min="0"
                            step="0.01"
                            value="<?= esc(old('price') ?? 0) ?>"
                            required>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-success mt-3">
                    Add Detail
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Shipment Type</th>
                            <th>Zone</th>
                            <th class="text-end">Weight</th>
                            <th class="text-end">Price</th>
                            <th width="90">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($details)): ?>
                            <tr>
                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4">
                                    Belum ada Express Rate Detail.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($details as $detail): ?>
                                <tr>
                                    <td>
                                        <?= esc($detail['shipment_type']) ?>
                                    </td>
                                    <td>
                                        <?= (int) $detail['zone'] ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) $detail['weight'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format(
                                            (float) $detail['price'],
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                    <td>
                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'master/vendors/' .
                                                $vendor['id'] .
                                                '/express-rates/' .
                                                $header['id'] .
                                                '/details/delete/' .
                                                $detail['id']
                                            ) ?>"
                                            onsubmit="return confirm(
                                                'Hapus Express Rate Detail ini?'
                                            );">

                                            <?= csrf_field() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
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
