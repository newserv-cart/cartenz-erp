<h2>Pending Customers</h2>

<table border="1" cellpadding="5">

<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Action</th>
</tr>

<?php foreach($customers as $c): ?>

<tr>

<td>
<?= $c['customer_name'] ?>
</td>

<td>
<?= $c['email'] ?>
</td>

<td>

<a href="<?= base_url(
'customers/approve/'.$c['id']
) ?>">

✅ Approve

</a>

</td>

</tr>

<?php endforeach; ?>

</table>