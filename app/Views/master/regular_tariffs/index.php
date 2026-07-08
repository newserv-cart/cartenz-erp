<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📦 Regular Tariff Management</h4>
        </div>

        <div class="card-body">

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Country</label>
                    <select class="form-select" id="country_id">
                        <option value="">-- Select Country --</option>
                        <?php foreach($countries as $row): ?>
                            <option value="<?= esc($row['id']) ?>">
                                <?= esc($row['country_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Service</label>
                    <select class="form-select" id="service_id">
                        <?php foreach($services as $row): ?>
                            <option value="<?= esc($row['id']) ?>" 
                                <?= strtolower($row['service_name']) == 'regular' ? 'selected' : '' ?>>
                                <?= esc($row['service_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>

            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary" disabled>
                    ⚙ Weight Range
                    <span class="badge bg-warning ms-1">Soon</span>
                </button>

                <a href="<?= site_url('master/regular-tariffs/download-template') ?>"
                   class="btn btn-outline-success">
                    📥 Download Template
                </a>

                <a href="<?= site_url('master/regular-tariffs/upload') ?>"
                   class="btn btn-outline-warning">
                    📤 Upload Excel
                </a>

                <a href="<?= site_url('master/regular-tariffs/manual') ?>"
                   class="btn btn-primary">
                    🖥 Manual Input
                </a>

                <a href="<?= site_url('master/regular-tariffs/clone') ?>"
                   class="btn btn-outline-secondary">
                    📋 Clone Country
                </a>
            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>