<?php
$vendorName = $shipment['vendor_name'] ?? '-';
?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🏢 Vendor Information</h5>
            </div>

            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="180">Vendor</th>
                        <td><?= esc($vendorName) ?></td>
                    </tr>
                    <tr>
                        <th>Vendor AWB</th>
                        <td><?= esc($shipment['vendor_awb_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Master AWB</th>
                        <td><?= esc($shipment['master_awb_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Vendor Tracking</th>
                        <td><?= esc($shipment['tracking_no_vendor'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Submitted to Vendor</th>
                        <td>
                            <?= !empty($shipment['vendor_awb_no']) ? 'Yes' : 'No' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light fw-bold">
                Vendor Cost Summary
            </div>

            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th>Vendor Freight</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['vendor_freight'] ?? $shipment['vendor_cost'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Fuel</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['fuel_charge'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Remote Area</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['vendor_remote_charge'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Go Green</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['go_green_charge'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Handling</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['handling_charge'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>VAT</th>
                        <td class="text-end">
                            Rp <?= number_format((float) ($shipment['vat_amount'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr class="table-success">
                        <th>Total Vendor</th>
                        <th class="text-end">
                            Rp <?= number_format((float) ($vendorTotal ?? $shipment['vendor_total'] ?? 0), 0, ',', '.') ?>
                        </th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
