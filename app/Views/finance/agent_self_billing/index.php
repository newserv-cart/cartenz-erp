<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="mb-1">Agent Self Billing Approval</h3>
        <div class="text-muted">Review invoice yang dibuat sendiri oleh agent.</div>
    </div>

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
                        <th>Invoice</th><th>Agent</th><th>Date</th><th>Due Date</th>
                        <th>Approval</th><th>Status</th><th class="text-end">Total</th><th>Action</th>
                    </tr></thead>
                    <tbody>
                    <?php if ($invoices === []): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada Self Billing agent.</td></tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><?= esc($invoice['invoice_no'] ?? '-') ?></td>
                                <td><strong><?= esc($invoice['agent_name'] ?? '-') ?></strong><br>
                                    <small class="text-muted"><?= esc($invoice['agent_code'] ?? '-') ?></small></td>
                                <td><?= esc($invoice['invoice_date'] ?? '-') ?></td>
                                <td><?= esc($invoice['due_date'] ?? '-') ?></td>
                                <td><?= esc($invoice['approval_status'] ?? '-') ?></td>
                                <td><?= esc($invoice['invoice_status'] ?? '-') ?></td>
                                <td class="text-end">Rp <?= number_format((float) ($invoice['total_amount'] ?? 0), 0, ',', '.') ?></td>
                                <td>
                                    <?php if (strtoupper((string) ($invoice['approval_status'] ?? '')) === 'PENDING_APPROVAL'): ?>
                                        <form method="post" action="<?= site_url('finance/agent-self-billing/approve/' . (int) $invoice['id']) ?>"
                                              class="mb-2" onsubmit="return confirm('Approve dan terbitkan invoice ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-success">Approve & Issue</button>
                                        </form>
                                        <form method="post" action="<?= site_url('finance/agent-self-billing/reject/' . (int) $invoice['id']) ?>">
                                            <?= csrf_field() ?>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="rejection_reason" class="form-control"
                                                       minlength="10" placeholder="Alasan penolakan" required>
                                                <button type="submit" class="btn btn-outline-danger">Reject</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Sudah diproses</span>
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
