<h2>Create Shipment</h2>

<form method="post">

<?= csrf_field() ?>

<p>
Weight (KG)
</p>

<input
    type="number"
    step="0.01"
    name="actual_weight"
    required>

<br><br>

<p>
Remarks
</p>

<textarea
    name="remarks"></textarea>

<br><br>

<button type="submit">
📦 Create Shipment
</button>

</form>