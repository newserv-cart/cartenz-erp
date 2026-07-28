<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-1">
            🔗 Vendor Booking & AWB
        </h3>

        <div class="text-muted">
            Masukkan nomor AWB yang diterbitkan oleh vendor.
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

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>AWB CARTENZ</th>
                            <th>Vendor</th>
                            <th>Manifest</th>
                            <th>Destination</th>
                            <th>Service</th>
                            <th class="text-end">Chargeable</th>
                            <th style="min-width: 500px;">
                                Vendor Booking
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($shipments)): ?>

                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >
                                    Tidak ada shipment yang menunggu
                                    Vendor AWB.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($shipments as $shipment): ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= esc(
                                                $shipment['awb_no'] ?? '-'
                                            ) ?>
                                        </strong>

                                        <div class="small text-muted">
                                            <?= esc(
                                                $shipment[
                                                    'shipment_status'
                                                ] ?? '-'
                                            ) ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['vendor_name'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['manifest_no'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['country_name'] ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['service_name'] ?? '-'
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

                                    <td>
                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'warehouse/vendor-bookings/'
                                                . $shipment['id']
                                                . '/book'
                                            ) ?>"
                                            class="row g-2"
                                            onsubmit="return confirm(
                                                'Simpan Vendor AWB untuk shipment ini?'
                                            );"
                                        >
                                            <?= csrf_field() ?>

                                            <div class="col-md-4">
                                                <input
                                                    type="text"
                                                    name="vendor_awb"
                                                    class="form-control"
                                                    placeholder="Vendor AWB *"
                                                    maxlength="100"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-3">
                                                <input
                                                    type="text"
                                                    name="vendor_booking_reference"
                                                    class="form-control"
                                                    placeholder="Booking Reference"
                                                    maxlength="100"
                                                >
                                            </div>

                                            <div class="col-md-3">
                                                <input
                                                    type="text"
                                                    name="vendor_service_code"
                                                    class="form-control"
                                                    placeholder="Service Code"
                                                    maxlength="50"
                                                >
                                            </div>

                                            <div class="col-md-2 d-grid">
                                                <button
                                                    type="submit"
                                                    class="btn btn-primary"
                                                >
                                                    Book
                                                </button>
                                            </div>
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
