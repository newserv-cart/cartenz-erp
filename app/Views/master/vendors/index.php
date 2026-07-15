<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">🏢 Vendor Master</h3>
            <div class="text-muted">
                Vendor dan fondasi tarif Regular/Express.
            </div>
        </div>

        <a
            href="<?= site_url('master/vendors/create') ?>"
            class="btn btn-primary">
            ➕ Add Vendor
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

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Vendor</th>
                            <th>Category / Type</th>
                            <th>Priority</th>
                            <th>API</th>
                            <th>Status</th>
                            <th width="210">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($vendors)): ?>
                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4">
                                    Belum ada vendor. Tambahkan vendor pertama.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendors as $vendor): ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <?= esc($vendor['vendor_code']) ?>
                                    </td>
                                    <td>
                                        <?= esc($vendor['vendor_name']) ?>
                                    </td>
                                    <td>
                                        <?= esc($vendor['vendor_category'] ?? '-') ?>
                                        /
                                        <?= esc($vendor['vendor_type'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= (int) ($vendor['vendor_priority'] ?? 0) ?>
                                    </td>
                                    <td>
                                        <?= (int) ($vendor['api_available'] ?? 0) === 1
                                            ? 'Yes'
                                            : 'No' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= (int) ($vendor['status'] ?? 0) === 1
                                            ? 'success'
                                            : 'secondary' ?>">
                                            <?= (int) ($vendor['status'] ?? 0) === 1
                                                ? 'ACTIVE'
                                                : 'INACTIVE' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a
                                                href="<?= site_url(
                                                    'master/vendors/' .
                                                    $vendor['id']
                                                ) ?>"
                                                class="btn btn-sm btn-primary">
                                                Open
                                            </a>

                                            <a
                                                href="<?= site_url(
                                                    'master/vendors/edit/' .
                                                    $vendor['id']
                                                ) ?>"
                                                class="btn btn-sm btn-warning">
                                                Edit
                                            </a>

                                            <form
                                                method="post"
                                                action="<?= site_url(
                                                    'master/vendors/toggle-status/' .
                                                    $vendor['id']
                                                ) ?>">

                                                <?= csrf_field() ?>

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-secondary">
                                                    Toggle
                                                </button>
                                            </form>
                                        </div>
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
