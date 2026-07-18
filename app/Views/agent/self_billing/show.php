<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<?php
$approvalStatus = strtoupper((string) ($invoice['approval_status'] ?? '-'));
$invoiceStatus  = strtoupper((string) ($invoice['invoice_status'] ?? '-'));
$canPay = $approvalStatus === 'APPROVED'
    && in_array($invoiceStatus, ['UNPAID', 'PARTIAL'], true)
    && (float) ($invoice['balance_amount'] ?? 0) > 0;
?>

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">🧾 <?= esc($invoice['invoice_no'] ?? 'Agent Invoice') ?></h3>
            <div class="text-muted"><?= esc($agent['agent_code'] ?? '-') ?> · <?= esc($agent['agent_name'] ?? '-') ?></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('agent/self-billing') ?>" class="btn btn-outline-secondary">← Kembali</a>
            <a href="<?= site_url('agent/self-billing/invoice/' . (int) $invoice['id'] . '/print') ?>"
               target="_blank" class="btn btn-outline-primary">🖨 Print / Save PDF</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white">Invoice Information</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="180">Invoice Date</th><td><?= esc($invoice['invoice_date'] ?? '-') ?></td></tr>
                        <tr><th>Due Date</th><td><?= esc($invoice['due_date'] ?? '-') ?></td></tr>
                        <tr><th>Source</th><td><?= esc($invoice['source'] ?? '-') ?></td></tr>
                        <tr><th>Approval</th><td><span class="badge bg-info text-dark"><?= esc($approvalStatus) ?></span></td></tr>
                        <tr><th>Invoice Status</th><td><span class="badge bg-secondary"><?= esc($invoiceStatus) ?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light fw-bold">Amount</div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-md-4"><div class="border rounded p-3 h-100">
                            <div class="text-muted">Total</div>
                            <h4>Rp <?= number_format((float) ($invoice['total_amount'] ?? 0), 0, ',', '.') ?></h4>
                        </div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100">
                            <div class="text-muted">Paid</div>
                            <h4>Rp <?= number_format((float) ($invoice['paid_amount'] ?? 0), 0, ',', '.') ?></h4>
                        </div></div>
                        <div class="col-md-4"><div class="border rounded p-3 h-100">
                            <div class="text-muted">Balance</div>
                            <h4>Rp <?= number_format((float) ($invoice['balance_amount'] ?? 0), 0, ',', '.') ?></h4>
                        </div></div>
                    </div>

                    <div class="mt-3">
                        <?php if ($canPay): ?>
                            <a href="<?= site_url('agent/payment/upload/' . (int) $invoice['id']) ?>"
                               class="btn btn-success">💳 Upload Payment</a>
                        <?php elseif ($approvalStatus === 'PENDING_APPROVAL'): ?>
                            <div class="alert alert-warning mb-0">Invoice menunggu approval Finance sebelum dapat dibayar.</div>
                        <?php elseif ($approvalStatus === 'REJECTED'): ?>
                            <div class="alert alert-danger mb-0">
                                Invoice ditolak: <?= esc($invoice['rejection_reason'] ?? '-') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light"><h5 class="mb-0">Shipment Snapshot</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr>
                        <th>AWB</th><th>Date</th><th class="text-end">Weight</th>
                        <th class="text-end">Freight</th><th class="text-end">Insurance</th>
                        <th class="text-end">Packing</th><th class="text-end">Additional</th>
                        <th class="text-end">Discount</th><th class="text-end">Line Total</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($invoice['details'] as $detail): ?>
                        <tr>
                            <td><?= esc($detail['awb_no'] ?? '-') ?></td>
                            <td><?= esc($detail['shipment_date'] ?? '-') ?></td>
                            <td class="text-end"><?= number_format((float) ($detail['chargeable_weight'] ?? 0), 2, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['freight_amount'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['insurance_amount'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['packing_amount'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['additional_amount'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['discount_amount'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-end">Rp <?= number_format((float) ($detail['line_total'] ?? 0), 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
