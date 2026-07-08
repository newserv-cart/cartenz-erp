<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🌱 Go Green Zones</h4>
            <a href="<?= site_url('master/gogreen-zones/create') ?>" class="btn btn-light">
                ➕ Add New Zone
            </a>
        </div>

        <div class="card-body">

            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th width="50">#</th>
                        <th>Zone Code</th>
                        <th>Zone Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($zones)): ?>
                        <?php foreach($zones as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= esc($row['zone_code']) ?></strong></td>
                                <td><?= esc($row['zone_name']) ?></td>
                                <td><?= $row['description'] ? esc($row['description']) : '-' ?></td>
                                <td>
                                    <?php if($row['status'] == 1): ?>
                                        <span class="badge bg-success">ACTIVE</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">INACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= site_url('master/gogreen-zones/edit/'.$row['id']) ?>" 
                                    <a href="<?= site_url('master/gogreen-zones/edit/'.$row['id']) ?>"
                                    class="btn btn-warning btn-sm">

                                    ✏ Edit

                                    </a>

                                    <a href="<?= site_url('master/gogreen-zones/delete/'.$row['id']) ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin ingin menonaktifkan zone ini?')">

                                    🗑 Delete

                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No Go Green Zones found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if(isset($pager)): ?>
                <div class="mt-3">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?= $this->endSection() ?>