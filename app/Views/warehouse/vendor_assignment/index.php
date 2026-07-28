<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                🚚 Vendor Assignment
            </h3>

            <div class="text-muted">
                Alokasikan shipment di main warehouse kepada vendor.
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

    <form
        method="post"
        action="<?= site_url(
            'warehouse/vendor-assignment/assign'
        ) ?>"
        id="vendorAssignmentForm"
    >
        <?= csrf_field() ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body">

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

                                $vendorCode = $vendor['vendor_code']
                                    ?? $vendor['code']
                                    ?? '';
                                ?>

                                <option
                                    value="<?= (int) $vendor['id'] ?>"
                                    <?= (string) old('vendor_id') === (string) $vendor['id']
                                        ? 'selected'
                                        : '' ?>
                                >
                                    <?= esc(
                                        trim(
                                            $vendorCode
                                            . ' · '
                                            . $vendorName,
                                            ' ·'
                                        )
                                    ) ?>
                                </option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted">
                            Selected Shipment
                        </div>

                        <div class="fs-4 fw-bold" id="selectedCount">
                            0
                        </div>
                    </div>

                    <div class="col-md-3 text-md-end">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Assign to Vendor
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <div class="card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table
                        class="table table-hover align-middle mb-0"
                    >
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45px;">
                                    <input
                                        type="checkbox"
                                        id="selectAll"
                                        class="form-check-input"
                                    >
                                </th>

                                <th>AWB</th>
                                <th>Owner</th>
                                <th>Destination</th>
                                <th>Service</th>
                                <th>Package</th>
                                <th class="text-center">Colly</th>
                                <th class="text-end">Chargeable</th>
                                <th class="text-end">Selling Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (empty($shipments)): ?>

                                <tr>
                                    <td
                                        colspan="10"
                                        class="text-center py-5 text-muted"
                                    >
                                        Tidak ada shipment yang menunggu
                                        vendor assignment.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($shipments as $shipment): ?>

                                    <tr>
                                        <td>
                                            <input
                                                type="checkbox"
                                                name="shipment_ids[]"
                                                value="<?= (int) $shipment['id'] ?>"
                                                class="form-check-input shipment-checkbox"
                                            >
                                        </td>

                                        <td>
                                            <strong>
                                                <?= esc(
                                                    $shipment['awb_no']
                                                    ?? '-'
                                                ) ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['owner_type']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['country_name']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['service_name']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= esc(
                                                $shipment['package_name']
                                                ?? '-'
                                            ) ?>
                                        </td>

                                        <td class="text-center">
                                            <?= (int) (
                                                $shipment['total_colly']
                                                ?? 0
                                            ) ?>
                                        </td>

                                        <td class="text-end">
                                            <?= number_format(
                                                (float) (
                                                    $shipment[
                                                        'chargeable_weight'
                                                    ] ?? 0
                                                ),
                                                2,
                                                ',',
                                                '.'
                                            ) ?> Kg
                                        </td>

                                        <td class="text-end">
                                            Rp <?= number_format(
                                                (float) (
                                                    $shipment['total_charge']
                                                    ?? 0
                                                ),
                                                0,
                                                ',',
                                                '.'
                                            ) ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-info">
                                                <?= esc(
                                                    $shipment[
                                                        'shipment_status'
                                                    ] ?? '-'
                                                ) ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>
                    </table>

                </div>

            </div>
        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');

    const checkboxes = Array.from(
        document.querySelectorAll('.shipment-checkbox')
    );

    const selectedCount = document.getElementById(
        'selectedCount'
    );

    function updateSelectedCount() {
        const checkedCount = checkboxes.filter(
            checkbox => checkbox.checked
        ).length;

        selectedCount.textContent = checkedCount;

        if (selectAll) {
            selectAll.checked =
                checkboxes.length > 0
                && checkedCount === checkboxes.length;

            selectAll.indeterminate =
                checkedCount > 0
                && checkedCount < checkboxes.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });

            updateSelectedCount();
        });
    }

    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener(
            'change',
            updateSelectedCount
        );
    });

    const form = document.getElementById(
        'vendorAssignmentForm'
    );

    if (form) {
        form.addEventListener('submit', function (event) {
            const selected = checkboxes.filter(
                checkbox => checkbox.checked
            );

            const vendor = document.getElementById(
                'vendor_id'
            );

            if (selected.length === 0) {
                event.preventDefault();
                alert('Pilih minimal satu shipment.');
                return;
            }

            if (!vendor || !vendor.value) {
                event.preventDefault();
                alert('Pilih vendor terlebih dahulu.');
                return;
            }

            const confirmed = confirm(
                'Assign '
                + selected.length
                + ' shipment ke vendor yang dipilih?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    }

    updateSelectedCount();
});
</script>

<?= $this->endSection() ?>
