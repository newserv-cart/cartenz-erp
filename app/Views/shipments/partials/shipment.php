<?php
$currencyCode = $shipment['currency_code'] ?? 'USD';
?>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📦 Shipment Information</h5>
            </div>

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="180">AWB</th>
                                <td><?= esc($shipment['awb_no'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><?= esc($shipment['shipment_status'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Customer</th>
                                <td><?= esc($shipment['customer_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Service</th>
                                <td><?= esc($shipment['service_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Package Type</th>
                                <td><?= esc($shipment['package_type_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Commodity</th>
                                <td><?= esc($shipment['commodity_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Payment Type</th>
                                <td><?= esc($shipment['payment_type'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th width="180">Destination</th>
                                <td><?= esc($shipment['country_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Reference</th>
                                <td><?= esc($shipment['customer_reference_no'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <th>Colly</th>
                                <td><?= esc((string) ($shipment['total_colly'] ?? 0)) ?></td>
                            </tr>
                            <tr>
                                <th>Actual Weight</th>
                                <td><?= number_format((float) ($shipment['actual_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                            </tr>
                            <tr>
                                <th>Volume Weight</th>
                                <td><?= number_format((float) ($shipment['volume_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                            </tr>
                            <tr>
                                <th>Chargeable Weight</th>
                                <td><?= number_format((float) ($shipment['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                            </tr>
                            <tr>
                                <th>Declared Value</th>
                                <td>
                                    <?= esc($currencyCode) ?>
                                    <?= number_format((float) ($shipment['declared_value'] ?? 0), 2, ',', '.') ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light fw-bold">
                🏢 Sender Snapshot
            </div>
            <div class="card-body">
                <div class="fw-bold"><?= esc($shipment['sender_name'] ?? '-') ?></div>
                <div><?= esc($shipment['sender_company'] ?? '-') ?></div>
                <div><?= esc($shipment['sender_phone'] ?? '-') ?></div>
                <div><?= esc($shipment['sender_email'] ?? '-') ?></div>
                <div class="mt-2">
                    <?= esc($shipment['sender_address'] ?? '-') ?>
                    <?php if (!empty($shipment['sender_city'])): ?>
                        , <?= esc($shipment['sender_city']) ?>
                    <?php endif; ?>
                    <?php if (!empty($shipment['sender_state'])): ?>
                        , <?= esc($shipment['sender_state']) ?>
                    <?php endif; ?>
                    <?php if (!empty($shipment['sender_postal_code'])): ?>
                        <?= esc($shipment['sender_postal_code']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-light fw-bold">
                👤 Receiver Snapshot
            </div>
            <div class="card-body">
                <div class="fw-bold"><?= esc($shipment['receiver_name'] ?? '-') ?></div>
                <div><?= esc($shipment['receiver_company'] ?? '-') ?></div>
                <div><?= esc($shipment['receiver_phone'] ?? '-') ?></div>
                <div><?= esc($shipment['receiver_email'] ?? '-') ?></div>
                <div class="mt-2">
                    <?= esc($shipment['receiver_address'] ?? '-') ?>
                    <?php if (!empty($shipment['receiver_city'])): ?>
                        , <?= esc($shipment['receiver_city']) ?>
                    <?php endif; ?>
                    <?php if (!empty($shipment['receiver_state'])): ?>
                        , <?= esc($shipment['receiver_state']) ?>
                    <?php endif; ?>
                    <?php if (!empty($shipment['receiver_postal_code'])): ?>
                        <?= esc($shipment['receiver_postal_code']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">📦 Packages & Items</h5>
    </div>

    <div class="card-body">
        <?php if (empty($packages)): ?>
            <div class="alert alert-warning mb-0">
                Package belum tersedia.
            </div>
        <?php else: ?>
            <div class="accordion" id="packageAccordion">
                <?php foreach ($packages as $packageIndex => $package): ?>
                    <?php
                    $packageNo = $package['package_no'] ?? ($packageIndex + 1);
                    $collapseId = 'packageCollapse' . (int) ($package['id'] ?? $packageIndex);
                    ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button
                                class="accordion-button <?= $packageIndex === 0 ? '' : 'collapsed' ?>"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?= esc($collapseId) ?>">
                                Colly #<?= esc((string) $packageNo) ?>
                                · <?= number_format((float) ($package['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg
                            </button>
                        </h2>

                        <div
                            id="<?= esc($collapseId) ?>"
                            class="accordion-collapse collapse <?= $packageIndex === 0 ? 'show' : '' ?>"
                            data-bs-parent="#packageAccordion">
                            <div class="accordion-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Actual</div>
                                        <div class="fw-semibold">
                                            <?= number_format((float) ($package['actual_weight'] ?? 0), 2, ',', '.') ?> Kg
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Length</div>
                                        <div><?= number_format((float) ($package['length_cm'] ?? 0), 2, ',', '.') ?> Cm</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Width</div>
                                        <div><?= number_format((float) ($package['width_cm'] ?? 0), 2, ',', '.') ?> Cm</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Height</div>
                                        <div><?= number_format((float) ($package['height_cm'] ?? 0), 2, ',', '.') ?> Cm</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Volume</div>
                                        <div><?= number_format((float) ($package['volume_weight'] ?? 0), 2, ',', '.') ?> Kg</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="small text-muted">Chargeable</div>
                                        <div class="fw-bold">
                                            <?= number_format((float) ($package['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="60">#</th>
                                                <th>Description</th>
                                                <th width="90">Qty</th>
                                                <th width="100">Currency</th>
                                                <th width="140" class="text-end">Unit Value</th>
                                                <th width="140" class="text-end">Total Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($package['items'])): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        Item belum tersedia.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($package['items'] as $itemIndex => $item): ?>
                                                    <tr>
                                                        <td><?= $itemIndex + 1 ?></td>
                                                        <td><?= esc($item['item_description'] ?? '-') ?></td>
                                                        <td><?= number_format((float) ($item['quantity'] ?? 0), 0, ',', '.') ?></td>
                                                        <td><?= esc($currencyCode) ?></td>
                                                        <td class="text-end">
                                                            <?= number_format((float) ($item['unit_value'] ?? 0), 2, ',', '.') ?>
                                                        </td>
                                                        <td class="text-end fw-semibold">
                                                            <?= number_format((float) ($item['total_value'] ?? 0), 2, ',', '.') ?>
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-light fw-bold">📝 Remarks</div>
    <div class="card-body">
        <?= nl2br(esc($shipment['remarks'] ?? '-')) ?>
    </div>
</div>
