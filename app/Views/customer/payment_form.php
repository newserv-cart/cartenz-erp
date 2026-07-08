
<div class="container mt-4">

    <div class="card">
        <div class="card-header">
            <h4>Upload Bukti Pembayaran</h4>
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= site_url('customer/payments/store') ?>"
                  enctype="multipart/form-data">

                <input type="hidden"
                       name="invoice_id"
                       value="<?= $invoice['id'] ?>">

                <div class="mb-3">
                    <label>Invoice No</label>
                    <input type="text"
                           class="form-control"
                           value="<?= $invoice['invoice_no'] ?>"
                           readonly>
                </div>

                <div class="mb-3">
                    <label>Total Invoice</label>
                    <input type="text"
                           class="form-control"
                           value="Rp <?= number_format($invoice['total_amount']) ?>"
                           readonly>
                </div>

                <div class="mb-3">
                    <label>Tanggal Transfer</label>
                    <input type="date"
                           name="payment_date"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method"
                            class="form-control">
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="Cash">Cash</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>No Referensi Transfer</label>
                    <input type="text"
                           name="reference_no"
                           class="form-control">
                </div>

                <div class="mb-3">
                    <label>Jumlah Transfer</label>
                    <input type="number"
                           name="amount"
                           class="form-control"
                           required>
                </div>

                <div class="mb-3">
                    <label>Keterangan</label>
                    <textarea name="remarks"
                              class="form-control"></textarea>
                </div>

                <div class="mb-3">
                    <label>Bukti Transfer</label>
                    <input type="file"
                           name="proof_file"
                           class="form-control"
                           accept=".jpg,.jpeg,.png,.pdf"
                           required>
                </div>

                <button type="submit"
                        class="btn btn-success">
                    Upload Pembayaran
                </button>

            </form>

        </div>
    </div>

</div>

