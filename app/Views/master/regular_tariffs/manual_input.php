<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="card shadow">

        <div class="card-header bg-success text-white">
            <h4>🖥 Manual Regular Tariff</h4>
        </div>

        <div class="card-body">

            <form method="post" 
                  action="<?= site_url('master/regular-tariffs/save-manual') ?>"
                  id="formTariff">

                <!-- Keamanan CSRF -->
                <?= csrf_field() ?>

                <!-- Bagian Pilihan Negara & Layanan -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <select name="country_id" class="form-select" required>
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
                        <select name="service_id" class="form-select" required>
                            <?php foreach($services as $row): ?>
                                <option value="<?= esc($row['id']) ?>" 
                                    <?= strtoupper($row['service_code']) === 'REGULAR' ? 'selected' : '' ?>>
                                    <?= esc($row['service_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Tariff Weight & Price List</h5>
                    <div>
                        <a id="btnDownloadTemplate" 
                           href="<?= site_url('master/regular-tariffs/download-template') ?>" 
                           class="btn btn-primary btn-sm me-1">
                            📥 Download Template
                        </a>
                        <a href="<?= site_url('master/regular-tariffs/upload') ?>" 
                           class="btn btn-warning btn-sm me-1">
                            📤 Upload Excel
                        </a>
                        <button type="button" class="btn btn-success btn-sm" id="btnAddRange">
                            ➕ Add Weight Range
                        </button>
                    </div>
                </div>

                <?php
                $defaultRanges = [
                    ['from' => 1, 'to' => 1],
                    ['from' => 2, 'to' => 5],
                    ['from' => 6, 'to' => 29],
                    ['from' => 30, 'to' => 99],
                    ['from' => 100, 'to' => 299]
                ];
                ?>

                <div class="table-responsive">
                    <table id="tariffTable" class="table table-bordered table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th width="100">Weight From (kg)</th>
                                <th width="100">Weight To (kg)</th>
                                <th>Agent A (Rp/kg)</th>
                                <th>Agent B (Rp/kg)</th>
                                <th>Agent C (Rp/kg)</th>
                                <th>Publish Price (Rp/kg)</th>
                                <th>Vendor Cost (Rp/kg)</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($defaultRanges as $r): ?>
                            <tr>
                                <td>
                                    <input type="number" step="0.01" min="0.01"
                                           name="weight_from[]" class="form-control text-end"
                                           value="<?= esc($r['from']) ?>" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="<?= esc($r['from']) ?>"
                                           name="weight_to[]" class="form-control text-end"
                                           value="<?= esc($r['to']) ?>" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="agent_a_price[]" class="form-control text-end"
                                           value="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="agent_b_price[]" class="form-control text-end"
                                           value="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="agent_c_price[]" class="form-control text-end"
                                           value="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="publish_price[]" class="form-control text-end"
                                           value="0" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0"
                                           name="vendor_cost[]" class="form-control text-end"
                                           value="0" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm removeRow">🗑</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success">💾 Save All Tariff</button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
const BASEURL = "<?= site_url() ?>";
</script>
<script src="<?= base_url('js/modules/master/regular-tariff-manual.js') ?>"></script>

<?= $this->endSection() ?>