<?php
$isInvoiced = (int) ($shipment['is_invoiced'] ?? 0) === 1;
$invoiceId  = $shipment['invoice_id'] ?? null;
$paymentType = strtoupper((string) ($shipment['payment_type'] ?? '-'));

$billingStatus = $isInvoiced
    ? 'INVOICED'
    : ($paymentType === 'CASH' ? 'CASH' : 'NOT INVOICED');

$billingClass = $isInvoiced
    ? 'success'
    : ($paymentType === 'CASH' ? 'primary' : 'warning');
?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">💳 Billing Status</h5>
            </div>

            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th width="180">Payment Type</th>
                        <td><?= esc($paymentType) ?></td>
                    </tr>
                    <tr>
                        <th>Invoice Status</th>
                        <td>
                            <span class="badge bg-<?= esc($billingClass) ?>">
                                <?= esc($billingStatus) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Invoice ID</th>
                        <td><?= esc((string) ($invoiceId ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>Customer Total</th>
                        <td>
                            Rp <?= number_format((float) ($customerTotal ?? $shipment['total_charge'] ?? 0), 0, ',', '.') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light fw-bold">
                Billing Information
            </div>

            <div class="card-body">
                <?php if ($isInvoiced): ?>
                    <div class="alert alert-success mb-0">
                        Shipment sudah terhubung ke invoice ID
                        <strong><?= esc((string) $invoiceId) ?></strong>.
                    </div>
                <?php elseif ($paymentType === 'INVOICE'): ?>
                    <div class="alert alert-warning mb-0">
                        Shipment menggunakan pembayaran INVOICE tetapi belum dimasukkan ke invoice.
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        Shipment ini belum memiliki dokumen invoice.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
