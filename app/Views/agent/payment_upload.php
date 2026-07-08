<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>💳 Upload Payment</h2>

<div class="card">

<div class="card-body">

<p>
Invoice :
<b><?= $invoice['invoice_no'] ?></b>
</p>

<p>
Outstanding :
Rp <?= number_format($invoice['balance_amount']) ?>
</p>

<form
method="post"
enctype="multipart/form-data">

<div class="mb-3">

<label>Amount</label>

<input
type="number"
name="amount"
class="form-control"
value="<?= $invoice['balance_amount'] ?>"
required>

</div>

<div class="mb-3">

<label>Payment Method</label>

<input
type="text"
name="payment_method"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Reference No</label>

<input
type="text"
name="reference_no"
class="form-control">

</div>

<div class="mb-3">

<label>Remarks</label>

<textarea
name="remarks"
class="form-control"></textarea>

</div>

<div class="mb-3">

<label>Bukti Transfer</label>

<input
type="file"
name="proof_file"
class="form-control"
required>

</div>

<button
class="btn btn-success">

Upload Payment

</button>

</form>

</div>

</div>

</div>