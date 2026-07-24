<h2 class="mb-4">⏳ Pending Payments</h2>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice / Ref ID</th>
                        <th class="text-end">Amount</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach($payments as $payment): ?>
                        <tr>
                            <td>
                                <?= esc(
                                    $payment['payment_type'] === 'CUSTOMER'
                                        ? ($payment['invoice_no'] ?? $payment['invoice_id'] ?? '-')
                                        : ($payment['invoice_no'] ?? $payment['agent_invoice_id'] ?? '-')
                                ) ?>
                                <small class="text-muted d-block">
                                    <?= esc(ucfirst(strtolower($payment['payment_type'] ?? ''))) ?>
                                </small>
                            </td>
                            <td class="text-end fw-semibold">
                                Rp <?= number_format(
                                    (float) ($payment['amount'] ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>
                            <td>
                                <?php if (!empty($payment['proof_file'])): ?>
                                    <a target="_blank" rel="noopener noreferrer"
                                       href="<?= base_url('uploads/payments/' . rawurlencode($payment['proof_file'])) ?>"
                                       class="text-decoration-none">Lihat Bukti</a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    <?= esc($payment['status'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= site_url('finance/payments/approve/' . (int) $payment['id']) ?>"
                                   class="btn btn-sm btn-success me-1">Setujui</a>
                                <a href="<?= site_url('finance/payments/reject/' . (int) $payment['id']) ?>"
                                   class="btn btn-sm btn-outline-danger">Tolak</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada pembayaran yang menunggu verifikasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>