<h2>
🚚 Pickup Request
</h2>

<form
method="post"
action="<?= site_url('pickup/store') ?>">

<input
type="hidden"
name="shipment_id"
value="<?= $shipmentId ?>">

<p>

Pickup Date

<input
type="date"
name="pickup_date"
class="form-control">

</p>

<p>

Pickup Time

<input
type="text"
name="pickup_time"
class="form-control">

</p>

<p>

Contact Name

<input
type="text"
name="contact_name"
class="form-control">

</p>

<p>

Phone

<input
type="text"
name="contact_phone"
class="form-control">

</p>

<p>

Address

<textarea
name="pickup_address"
class="form-control">
</textarea>

</p>

<button
class="btn btn-success">

Submit Pickup

</button>

</form>