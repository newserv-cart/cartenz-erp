<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h3 class="mb-4">🏢 Agent Payments</h3>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr>
                        <th>Payment</th><th>Invoice</th><th>Agent</th><th>Date</th>
                        <th>Method</th><th>Reference</th><th class="text-end">Amount</th>
                        <th>Proof</th><th>Status</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                    <?php if ($payments === []): ?>
                        <tr><td colspan="10" class="text-center text-muted py-4">Belum ada agent payment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= esc($payment['payment_no'] ?? '-') ?></td>
                                <td><?= esc($payment['invoice_no'] ?? '-') ?></td>
                                <td><?= esc($payment['agent_name'] ?? '-') ?></td>
                                <td><?= esc($payment['payment_date'] ?? '-') ?></td>
                                <td><?= esc($payment['payment_method'] ?? '-') ?></td>
                                <td><?= esc($payment['reference_no'] ?? '-') ?></td>
                                <td class="text-end">Rp <?= number_format((float) ($payment['amount'] ?? 0), 0, ',', '.') ?></td>
                                <td>
                                    <?php if (!empty($payment['proof_file'])): ?>
                                        <a href="<?= base_url('uploads/payments/' . rawurlencode($payment['proof_file'])) ?>"
                                           target="_blank" rel="noopener">View Proof</a>
                                    <?php else: ?>-<?php endif; ?>
                                </td>
                                <td><?= esc($payment['status'] ?? '-') ?></td>
                                <td>
                                    <?php if (strtoupper((string) ($payment['status'] ?? '')) === 'PENDING'): ?>
                                        <a href="<?= site_url('finance/agent-payments/approve/' . (int) $payment['id']) ?>"
                                           class="btn btn-sm btn-success">Approve</a>
                                        <a href="<?= site_url('finance/agent-payments/reject/' . (int) $payment['id']) ?>"
                                           class="btn btn-sm btn-outline-danger">Reject</a>
                                    <?php else: ?>-<?php endif; ?>
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
