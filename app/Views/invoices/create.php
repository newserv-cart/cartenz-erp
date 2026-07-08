<h2>Generate Invoice</h2>

<form
method="post"
action="<?= site_url('invoices/generate') ?>">

<label>Customer</label>

<select
name="customer_id"
class="form-control">

<?php foreach($customers as $customer): ?>

<option value="<?= $customer['id'] ?>">
    <?= esc($customer['customer_name']) ?>
</option>

<?php endforeach; ?>

</select>

<br>

<label>Date From</label>

<input
type="date"
name="date_from"
class="form-control">

<br>

<label>Date To</label>

<input
type="date"
name="date_to"
class="form-control">

<br>

<label>VAT (%)</label>

<input
 type="number"
    step="0.01"
    name="vat_percent"
    value="11"
    class="form-control">

<br>

<label>Extra Charge</label>

<input
type="number"
    name="extra_charge"
    value="0"
    class="form-control">

<br>

<button
class="btn btn-success">

💰 Generate Invoice

</button>

</form>