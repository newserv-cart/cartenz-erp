<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <h3 class="mb-4">👤 Customer Payments</h3>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Payment No</th>
                            <th>Invoice No</th>
                            <th>Customer Name</th>
                            <th>Payment Date</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Belum ada data pembayaran pelanggan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <?php
                            $status = strtoupper(trim((string) ($payment['status'] ?? '')));
                            $badgeClass = match($status) {
                                'APPROVED' => 'bg-success',
                                'REJECTED' => 'bg-danger',
                                'PENDING' => 'bg-warning text-dark',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr>
                                <td><?= esc($payment['payment_no'] ?? '-') ?></td>
                                <td><?= esc($payment['invoice_no'] ?? '-') ?></td>
                                <td><?= esc($payment['customer_name'] ?? '-') ?></td>
                                <td><?= !empty($payment['payment_date']) ? date('d-m-Y', strtotime($payment['payment_date'])) : '-' ?></td>
                                <td><?= esc(ucwords(str_replace('_', ' ', $payment['payment_method'] ?? '-'))) ?></td>
                                <td><?= esc($payment['reference_no'] ?? '-') ?></td>
                                <td class="text-end fw-semibold">
                                    Rp <?= number_format((float) ($payment['amount'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if (!empty($payment['proof_file'])): ?>
                                        <a href="<?= base_url('uploads/payments/' . rawurlencode($payment['proof_file'])) ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="text-decoration-none">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= esc($status ?: '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($status === 'PENDING'): ?>
                                        <a href="<?= site_url('finance/customer-payments/approve/' . (int) $payment['id']) ?>"
                                           class="btn btn-sm btn-success me-1">Setujui</a>
                                        <a href="<?= site_url('finance/customer-payments/reject/' . (int) $payment['id']) ?>"
                                           class="btn btn-sm btn-outline-danger">Tolak</a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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