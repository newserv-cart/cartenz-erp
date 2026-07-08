<h2>
✏ Edit Address
</h2>

<form
method="post"
action="<?= base_url(
'customer/addresses/update/' .
$address['id']
) ?>">

<?= csrf_field() ?>

<p>Label</p>

<input
type="text"
name="address_label"
value="<?= $address['address_label'] ?>">

<p>Contact Name</p>

<input
type="text"
name="contact_name"
value="<?= $address['contact_name'] ?>">

<p>Company</p>

<input
type="text"
name="company_name"
value="<?= $address['company_name'] ?>">

<p>Phone</p>

<input
type="text"
name="phone"
value="<?= $address['phone'] ?>">

<p>Email</p>

<input
type="email"
name="email"
value="<?= $address['email'] ?>">

<p>Address</p>

<textarea
name="address"><?= $address['address'] ?></textarea>

<br><br>

<button>

💾 Update

</button>

</form>