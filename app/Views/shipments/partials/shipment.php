<div class="card shadow border-0">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            📦 Shipment Information

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <table class="table table-borderless">

                    <tr>
                        <th width="180">AWB</th>
                        <td><?= esc($shipment['awb_no']) ?></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-primary">
                                <?= esc($shipment['shipment_status']) ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>Customer</th>
                        <td><?= esc($shipment['customer_name']) ?></td>
                    </tr>

                    <tr>
                        <th>Service</th>
                        <td><?= esc($shipment['service_name'] ?? '-') ?></td>
                    </tr>

                    <tr>
                        <th>Destination</th>
                        <td><?= esc($shipment['country_name'] ?? '-') ?></td>
                    </tr>

                    <tr>
                        <th>Reference No</th>
                        <td><?= esc($shipment['customer_reference_no'] ?? '-') ?></td>
                    </tr>

                </table>

            </div>

            <div class="col-md-6">

                <table class="table table-borderless">

                    <tr>
                        <th width="180">Colly</th>
                        <td><?= $shipment['total_colly'] ?></td>
                    </tr>

                    <tr>
                        <th>Actual Weight</th>
                        <td><?= number_format($shipment['actual_weight'],2) ?> Kg</td>
                    </tr>

                    <tr>
                        <th>Volume Weight</th>
                        <td><?= number_format($shipment['volume_weight'],2) ?> Kg</td>
                    </tr>

                    <tr>
                        <th>Chargeable Weight</th>
                        <td><?= number_format($shipment['chargeable_weight'],2) ?> Kg</td>
                    </tr>

                    <tr>
                        <th>Declared Value</th>
                        <td>Rp <?= number_format($shipment['declared_value']) ?></td>
                    </tr>

                </table>

            </div>

        </div>

        <hr>

        <h5>📝 Remarks</h5>

        <div class="alert alert-light">

            <?= nl2br(esc($shipment['remarks'] ?? '-')) ?>

        </div>

    </div>

</div>


<div class="card shadow mb-3">

    <div class="card-header bg-primary text-white">

        📦 Shipment Information

    </div>

    <div class="card-body">

        <table class="table table-borderless">

            <tr>
                <th width="180">AWB</th>
                <td><?= esc($shipment['awb_no']) ?></td>
            </tr>

            <tr>
                <th>Status</th>
                <td>

                    <span class="badge bg-primary">

                        <?= esc($shipment['shipment_status']) ?>

                    </span>

                </td>
            </tr>

            <tr>
                <th>Reference</th>
                <td><?= esc($shipment['customer_reference_no'] ?? '-') ?></td>
            </tr>

            <tr>
                <th>Commodity</th>
                <td><?= esc($shipment['commodity_type'] ?? '-') ?></td>
            </tr>

        </table>

    </div>

</div>

<div class="card shadow mb-3">

<div class="card-header bg-success text-white">

📦 Package Information

</div>

<div class="card-body">

<table class="table table-borderless">

<tr>

<th width="180">

Colly

</th>

<td>

<?= $shipment['total_colly'] ?>

</td>

</tr>

<tr>

<th>

Actual Weight

</th>

<td>

<?= number_format($shipment['actual_weight'],2) ?> Kg

</td>

</tr>

<tr>

<th>

Volume Weight

</th>

<td>

<?= number_format($shipment['volume_weight'],2) ?> Kg

</td>

</tr>

<tr>

<th>

Chargeable

</th>

<td>

<?= number_format($shipment['chargeable_weight'],2) ?> Kg

</td>

</tr>

</table>

</div>

</div>



<div class="card shadow">

<div class="card-header bg-warning">

💰 Charges

</div>

<div class="card-body">

<table class="table table-sm">

<tr>

<th>Freight</th>

<td class="text-end">

Rp <?= number_format($shipment['selling_amount'] ?? 0) ?>

</td>

</tr>

<tr>

<th>Insurance</th>

<td class="text-end">

Rp <?= number_format($shipment['insurance_amount'] ?? 0) ?>

</td>

</tr>

<tr>

<th>Packing</th>

<td class="text-end">

Rp <?= number_format($shipment['packing_charge'] ?? 0) ?>

</td>

</tr>

<tr>

<th>Additional</th>

<td class="text-end">

Rp <?= number_format($shipment['additional_charge'] ?? 0) ?>

</td>

</tr>

<tr class="table-primary">

<th>Total</th>

<td class="text-end">

Rp <?= number_format($shipment['total_charge'] ?? 0) ?>

</td>

</tr>

</table>

</div>

</div>