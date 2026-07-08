<h2>Customer Registration</h2>

<form method="post">

<?= csrf_field() ?>

<label>Customer Name</label>
<input type="text"
       name="customer_name"
       required>

<br><br>

<label>Company Name</label>
<input type="text"
       name="company_name">

<br><br>

<label>Email</label>
<input type="email"
       name="email"
       required>

<br><br>

<label>Phone</label>
<input type="text"
       name="phone"
       required>

<br><br>

<label>Password</label>
<input type="password"
       name="password"
       required>

<br><br>

<button type="submit">
Register
</button>

</form>