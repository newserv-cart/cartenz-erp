<div class="container mt-4 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Upload Bukti Pembayaran</h4>
        </div>

        <div class="card-body">

            <!-- 1. FORM SUDAH DITAMBAH ID & KONVERSI ID KE ANGKA -->
            <form
                id="customerPaymentForm"
                method="post"
                enctype="multipart/form-data"
                action="<?= site_url('customer/payments/store/' . (int) $invoice['id']) ?>"
            >
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" value="<?= esc($invoice['id'] ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label">Invoice No</label>
                    <input type="text" class="form-control" value="<?= esc($invoice['invoice_no'] ?? '-') ?>" readonly>
                </div>

                <!-- 4. FORMAT UANG SUDAH DIPERBAIKI KE STANDAR INDONESIA -->
                <div class="mb-3">
                    <label class="form-label">Total Invoice</label>
                    <input type="text" class="form-control" readonly
                           value="Rp <?= number_format(
                               (float) (
                                   ($invoice['grand_total'] ?? 0) > 0
                                       ? $invoice['grand_total']
                                       : ($invoice['total_amount'] ?? 0)
                               ),
                               0,
                               ',',
                               '.'
                           ) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Sisa Tagihan</label>
                    <input type="text" class="form-control" readonly
                           value="Rp <?= number_format(
                               (float) ($invoice['balance_amount'] ?? $invoice['total_amount'] ?? 0),
                               0,
                               ',',
                               '.'
                           ) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Transfer</label>
                    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Metode Pembayaran</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="bank_transfer">Transfer Bank</option>
                        <option value="e_wallet">E-Wallet</option>
                        <option value="qris">QRIS</option>
                        <option value="cash">Tunai</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">No Referensi Transfer</label>
                    <input type="text" name="reference_no" class="form-control" placeholder="Masukkan nomor bukti" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Transfer</label>
                    <input type="number" name="amount" class="form-control"
                           min="1000"
                           value="<?= esc($invoice['balance_amount'] ?? 0) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Opsional"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Bukti Transfer</label>
                    <input type="file" name="proof_file" class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf" required>
                    <small class="text-muted">Format: JPG, PNG, PDF. Maksimal 5MB</small>
                </div>

                <a href="<?= site_url('customer/my-invoices') ?>" class="btn btn-secondary me-2">Kembali</a>

                <!-- 2. TOMBOL SUDAH DITAMBAH ID -->
                <button
                    type="submit"
                    id="btnUploadPayment"
                    class="btn btn-primary"
                >
                    Upload Pembayaran
                </button>

            </form>

        </div>
    </div>
</div>

<!-- 3. PENGAWAL KLIK BERKALI-KALI SUDAH DITAMBAHKAN -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('customerPaymentForm');
    const button = document.getElementById('btnUploadPayment');

    if (!form || !button) {
        return;
    }

    form.addEventListener('submit', function (event) {
        if (button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.textContent = 'Mengupload...';
    });
});
</script>