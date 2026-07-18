<?= $this->extend('admin/layouts/app') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white"><h4 class="mb-0">💳 Upload Agent Payment</h4></div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif; ?>

                    <div class="alert alert-light border">
                        <strong><?= esc($invoice['invoice_no'] ?? '-') ?></strong><br>
                        Outstanding: <strong>Rp <?= number_format((float) ($invoice['balance_amount'] ?? 0), 0, ',', '.') ?></strong>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" name="amount" class="form-control" min="1"
                                   max="<?= esc((string) ($invoice['balance_amount'] ?? 0)) ?>" step="0.01"
                                   value="<?= esc(old('amount', (string) ($invoice['balance_amount'] ?? 0))) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <input type="text" name="payment_method" class="form-control"
                                   value="<?= esc(old('payment_method', 'Transfer Bank')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" value="<?= esc(old('reference_no')) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"><?= esc(old('remarks')) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bukti Transfer *</label>
                            <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div class="form-text">JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.</div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">Upload Payment</button>
                            <a href="<?= site_url('agent/self-billing/invoice/' . (int) $invoice['id']) ?>"
                               class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
