<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-1">
            ✈️ International Transit
        </h3>

        <div class="text-muted">
            Konfirmasi keberangkatan shipment yang sudah
            dibooking kepada vendor.
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
                            <th>Vendor AWB</th>
                            <th>Manifest</th>
                            <th>Destination</th>
                            <th class="text-end">
                                Chargeable
                            </th>
                            <th style="min-width: 620px;">
                                Departure Confirmation
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
                                    keberangkatan internasional.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($shipments as $shipment): ?>

                                <tr>
                                    <td>
                                        <strong>
                                            <?= esc(
                                                $shipment['awb_no']
                                                ?? '-'
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
                                            $shipment['vendor_name']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= esc(
                                                $shipment['vendor_awb']
                                                ?? '-'
                                            ) ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['manifest_no']
                                            ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= esc(
                                            $shipment['country_name']
                                            ?? '-'
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
                                                'warehouse/'
                                                . 'international-transit/'
                                                . $shipment['id']
                                                . '/depart'
                                            ) ?>"
                                            class="row g-2"
                                            onsubmit="return confirm(
                                                'Tandai shipment ini '
                                                + 'sudah diberangkatkan '
                                                + 'oleh vendor?'
                                            );"
                                        >
                                            <?= csrf_field() ?>

                                            <div class="col-md-2">
                                                <input
                                                    type="date"
                                                    name="departure_date"
                                                    class="form-control"
                                                    value="<?= date(
                                                        'Y-m-d'
                                                    ) ?>"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-2">
                                                <input
                                                    type="time"
                                                    name="departure_time"
                                                    class="form-control"
                                                    value="<?= date(
                                                        'H:i'
                                                    ) ?>"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-3">
                                                <input
                                                    type="text"
                                                    name="vendor_origin_location"
                                                    class="form-control"
                                                    placeholder="Origin location *"
                                                    maxlength="150"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-3">
                                                <input
                                                    type="text"
                                                    name="vendor_transport_reference"
                                                    class="form-control"
                                                    placeholder="Flight / Voyage / Transport Ref"
                                                    maxlength="100"
                                                >
                                            </div>

                                            <div class="col-md-2 d-grid">
                                                <button
                                                    type="submit"
                                                    class="btn btn-success"
                                                >
                                                    Mark In Transit
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
