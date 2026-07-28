<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                📦 Vendor Manifests
            </h3>

            <div class="text-muted">
                Buat manifest vendor, tambahkan shipment, lalu handover.
            </div>
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

            <h5 class="card-title mb-3">
                Buat Manifest Baru
            </h5>

            <form
                method="post"
                action="<?= site_url('warehouse/vendor-manifests/store') ?>"
            >
                <?= csrf_field() ?>

                <div class="row g-3 align-items-end">

                    <div class="col-md-6">
                        <label
                            for="vendor_id"
                            class="form-label"
                        >
                            Vendor *
                        </label>

                        <select
                            name="vendor_id"
                            id="vendor_id"
                            class="form-select"
                            required
                        >
                            <option value="">
                                -- Select Vendor --
                            </option>

                            <?php foreach ($vendors as $vendor): ?>
                                <?php
                                $vendorName = $vendor['vendor_name']
                                    ?? $vendor['name']
                                    ?? 'Vendor #' . $vendor['id'];
                                ?>

                                <option
                                    value="<?= (int) $vendor['id'] ?>"
                                    <?= (string) old('vendor_id') === (string) $vendor['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc($vendorName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Create Manifest
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Manifest No</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th class="text-center">Shipment</th>
                            <th class="text-center">Colly</th>
                            <th class="text-end">Weight (Kg)</th>
                            <th class="text-end">Vendor Cost</th>
                            <th>Created By</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($manifests)): ?>
                            <tr>
                                <td
                                    colspan="9"
                                    class="text-center py-5 text-muted"
                                >
                                    Belum ada vendor manifest.
                                </td>
                            </tr>
                        <?php else: ?>

                            <?php foreach ($manifests as $manifest): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= esc(
                                                $manifest['manifest_no']
                                                ?? '-'
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $manifest['vendor_name']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-<?= (
                                            $manifest['status'] === 'OPEN'
                                                ? 'info'
                                                : (
                                                    $manifest['status']
                                                    === 'DISPATCHED'
                                                        ? 'success'
                                                        : 'secondary'
                                                )
                                        ) ?>">
                                            <?= esc(
                                                $manifest['status'] ?? '-'
                                            ) ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) (
                                            $manifest['total_shipment'] ?? 0
                                        ) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= (int) (
                                            $manifest['total_colly'] ?? 0
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        <?= number_format(
                                            (float) (
                                                $manifest['total_weight'] ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td class="text-end">
                                        Rp <?= number_format(
                                            (float) (
                                                $manifest[
                                                    'total_vendor_cost'
                                                ] ?? 0
                                            ),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $manifest['created_by_username']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <a
                                            href="<?= site_url(
                                                'warehouse/vendor-manifests/'
                                                . (int) $manifest['id']
                                            ) ?>"
                                            class="btn btn-sm btn-outline-primary"
                                        >
                                            Detail
                                        </a>
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
