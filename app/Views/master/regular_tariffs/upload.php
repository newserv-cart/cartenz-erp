<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container">

    <div class="card shadow">

        <div class="card-header bg-warning text-dark">
            <h4>📤 Upload Regular Tariff Excel</h4>
        </div>

        <div class="card-body">

            <form
                method="post"
                enctype="multipart/form-data"
                action="<?= site_url('master/regular-tariffs/import') ?>">

                <div class="row mb-3">

                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select
                            name="country_id"
                            class="form-select"
                            required>
                            <option value="">-- Select Country --</option>
                            <?php foreach($countries as $row): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= esc($row['country_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Service</label>
                        <select
                            name="service_id"
                            class="form-select"
                            required>
                            <?php foreach($services as $row): ?>
                                <option
                                    value="<?= $row['id'] ?>"
                                    <?= strtolower($row['service_name']) == 'regular' ? 'selected' : '' ?>>
                                    <?= esc($row['service_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="mb-4">
                    <label class="form-label">Excel File</label>
                    <input
                        type="file"
                        name="excel"
                        class="form-control"
                        accept=".xlsx,.xls"
                        required>
                </div>

                <button type="submit" class="btn btn-success">
                    📤 Upload Tariff
                </button>

            </form>

        </div>

    </div>

</div>

<?= $this->endSection() ?>