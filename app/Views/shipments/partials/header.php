<?php
$status = strtoupper((string) ($shipment['shipment_status'] ?? 'DRAFT'));

$statusClass = match ($status) {
    'DELIVERED', 'CLOSED', 'PAID' => 'success',
    'CANCELLED', 'REJECTED', 'FAILED' => 'danger',
    'IN_TRANSIT', 'DISPATCHED', 'OUT_FOR_DELIVERY' => 'warning',
    default => 'primary',
};

$profitValue = (float) ($profit ?? $shipment['profit_amount'] ?? 0);
$profitClass = $profitValue < 0 ? 'text-danger' : 'text-success';
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h3 class="fw-bold mb-0">
                        <?= esc($shipment['awb_no'] ?? '-') ?>
                    </h3>

                    <span class="badge bg-<?= esc($statusClass) ?>">
                        <?= esc($status) ?>
                    </span>
                </div>

                <div class="text-muted mt-2">
                    Shipment ID #<?= esc((string) ($shipment['id'] ?? '-')) ?>
                    · <?= esc($shipment['shipment_date'] ?? '-') ?>
                </div>
            </div>

            <div class="text-lg-end">
                <div class="small text-muted">Customer</div>
                <div class="fw-bold">
                    <?= esc($shipment['customer_name'] ?? '-') ?>
                </div>
                <div class="small text-muted">
                    <?= esc($shipment['customer_company'] ?? '') ?>
                </div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Destination</div>
                <div class="fw-semibold">
                    <?= esc($shipment['country_name'] ?? '-') ?>
                </div>
            </div>

            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Service</div>
                <div class="fw-semibold">
                    <?= esc($shipment['service_name'] ?? '-') ?>
                </div>
            </div>

            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Vendor</div>
                <div class="fw-semibold">
                    <?= esc($shipment['vendor_name'] ?? '-') ?>
                </div>
            </div>

            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Chargeable</div>
                <div class="fw-semibold">
                    <?= number_format((float) ($shipment['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg
                </div>
            </div>

            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Customer Total</div>
                <div class="fw-semibold">
                    Rp <?= number_format((float) ($customerTotal ?? $shipment['total_charge'] ?? 0), 0, ',', '.') ?>
                </div>
            </div>

            <div class="col-6 col-md-3 col-xl-2">
                <div class="small text-muted">Profit</div>
                <div class="fw-bold <?= esc($profitClass) ?>">
                    Rp <?= number_format($profitValue, 0, ',', '.') ?>
                </div>
            </div>
        </div>
    </div>
</div>
