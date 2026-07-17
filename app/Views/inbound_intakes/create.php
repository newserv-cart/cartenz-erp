<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">📥 Receive Unregistered Agent Packages</h3>
            <div class="text-muted">Catat barang yang tiba tanpa AWB sebelum admin pusat membuat shipment.</div>
        </div>
        <a href="<?= site_url('inbound-intakes') ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('inbound-intakes/store') ?>" id="intakeForm">
        <?= csrf_field() ?>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white fw-bold">Inbound Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Agent *</label>
                        <select name="agent_id" class="form-select" required>
                            <option value="">-- Select Agent --</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= (int) $agent['id'] ?>" <?= (string) old('agent_id') === (string) $agent['id'] ? 'selected' : '' ?>>
                                    <?= esc($agent['agent_code']) ?> — <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Arrival Date *</label>
                        <input type="date" name="arrival_date" class="form-control" value="<?= esc(old('arrival_date') ?? date('Y-m-d')) ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Agent Reference</label>
                        <input type="text" name="agent_reference_no" class="form-control" value="<?= esc(old('agent_reference_no') ?? '') ?>" placeholder="Karung / surat jalan / reference">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Origin Branch</label>
                        <select name="origin_branch_id" class="form-select">
                            <option value="">-- Optional --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int) $branch['id'] ?>"><?= esc($branch['_label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Receiving Branch</label>
                        <select name="receiving_branch_id" class="form-select">
                            <option value="">-- Optional --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= (int) $branch['id'] ?>"><?= esc($branch['_label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Receiving Location *</label>
                        <input type="text" name="receiving_location" class="form-control" value="<?= esc(old('receiving_location') ?? 'Cartenz Main Warehouse Jakarta') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Declared Weight by Agent</label>
                        <input type="number" name="declared_weight" class="form-control" min="0" step="0.01" value="<?= esc(old('declared_weight') ?? 0) ?>">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" value="<?= esc(old('notes') ?? 'Agent belum membuat resi.') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <span class="fw-bold">Packages Received</span>
                <button type="button" class="btn btn-sm btn-success" id="addPackage">+ Add Package</button>
            </div>
            <div class="card-body" id="packageContainer"></div>
        </div>

        <button type="submit" class="btn btn-success">Save Inbound Intake</button>
    </form>
</div>

<template id="packageTemplate">
    <div class="card border-secondary mb-3 package-row">
        <div class="card-header d-flex justify-content-between">
            <strong class="package-title">Package</strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-package">Remove</button>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2"><label class="form-label">Weight *</label><input type="number" class="form-control field-weight" min="0.01" step="0.01" required></div>
                <div class="col-md-2"><label class="form-label">Length</label><input type="number" class="form-control field-length" min="0" step="0.01"></div>
                <div class="col-md-2"><label class="form-label">Width</label><input type="number" class="form-control field-width" min="0" step="0.01"></div>
                <div class="col-md-2"><label class="form-label">Height</label><input type="number" class="form-control field-height" min="0" step="0.01"></div>
                <div class="col-md-2"><label class="form-label">Barcode</label><input type="text" class="form-control field-barcode"></div>
                <div class="col-md-2"><label class="form-label">Remarks</label><input type="text" class="form-control field-remarks"></div>
            </div>
        </div>
    </div>
</template>

<script>
(function () {
    const container = document.getElementById('packageContainer');
    const template = document.getElementById('packageTemplate');
    const addButton = document.getElementById('addPackage');

    function renumber() {
        container.querySelectorAll('.package-row').forEach((row, index) => {
            row.querySelector('.package-title').textContent = 'Package #' + (index + 1);
            row.querySelector('.field-weight').name = `packages[${index}][actual_weight]`;
            row.querySelector('.field-length').name = `packages[${index}][length_cm]`;
            row.querySelector('.field-width').name = `packages[${index}][width_cm]`;
            row.querySelector('.field-height').name = `packages[${index}][height_cm]`;
            row.querySelector('.field-barcode').name = `packages[${index}][package_barcode]`;
            row.querySelector('.field-remarks').name = `packages[${index}][remarks]`;
        });
    }

    function addPackage() {
        const fragment = template.content.cloneNode(true);
        container.appendChild(fragment);
        renumber();
    }

    addButton.addEventListener('click', addPackage);
    container.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-package')) return;
        if (container.querySelectorAll('.package-row').length <= 1) return;
        event.target.closest('.package-row').remove();
        renumber();
    });

    addPackage();
})();
</script>

<?= $this->endSection() ?>
