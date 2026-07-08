<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php if(session()->getFlashdata('error')): ?>
<div class="alert alert-danger">
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-success text-white">
            <h4>➕ Add Regular Tariff</h4>
        </div>

        <div class="card-body">

            <form method="post" action="<?= site_url('master/regular-tariffs/store') ?>">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Country *</label>
                        <select name="country_id" class="form-select" required>
                            <option value="">-- Select Country --</option>
                            <?php foreach($countries as $row): ?>
                                <option value="<?= esc($row['id']) ?>" 
                                    <?= (($country_id ?? '') == $row['id']) ? 'selected' : '' ?>>
                                    <?= esc($row['country_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Service *</label>
                        <select name="service_id" class="form-select" required>
                            <option value="">-- Select Service --</option>
                            <?php foreach($services as $row): ?>
                                <option value="<?= esc($row['id']) ?>">
                                    <?= esc($row['service_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agent Type *</label>
                        <select name="agent_type_id" class="form-select" required>
                            <option value="">-- Select Agent Type --</option>
                            <?php foreach($agentTypes as $row): ?>
                                <option value="<?= esc($row['id']) ?>">
                                    <?= esc($row['agent_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delivery Type</label>
                        <select name="delivery_type_id" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach($deliveryTypes as $row): ?>
                                <option value="<?= esc($row['id']) ?>">
                                    <?= esc($row['delivery_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Package Type</label>
                        <select name="package_type_id" class="form-select">
                            <option value="">-- Select --</option>
                            <?php foreach($packageTypes as $row): ?>
                                <option value="<?= esc($row['id']) ?>">
                                    <?= esc($row['package_type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <hr>

                <h5 class="mb-3">📊 Weight Range Builder</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="tariffTable">
                        <thead class="table-dark">
                            <tr>
                                <th width="20%">Weight From (kg)</th>
                                <th width="20%">Weight To (kg)</th>
                                <th width="30%">Selling Price (Rp/kg)</th>
                                <th width="20%">Minimum Charge (Rp)</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="number" step="0.01" min="0.01"
                                           name="weight_from[]" class="form-control text-end" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01"
                                           name="weight_to[]" class="form-control text-end" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="selling_price[]" class="form-control text-end" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" value="0"
                                           name="minimum_charge[]" class="form-control text-end">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-success btnAddRow me-1">➕</button>
                                    <button type="button" class="btn btn-sm btn-danger btnDeleteRow">🗑</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">💾 Save All Tariff</button>
                    <a href="<?= site_url('master/regular-tariffs') ?>" class="btn btn-secondary ms-2">Back</a>
                </div>

            </form>

        </div>

    </div>

</div>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/regular-tariff.js') ?>"></script>
<?= $this->endSection() ?>