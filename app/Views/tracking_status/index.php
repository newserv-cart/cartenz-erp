<h2>Tracking Status Master</h2>

<table class="table table-bordered">

<tr>
    <th>Code</th>
    <th>Name</th>
    <th>Color</th>
</tr>

<?php foreach($statuses as $row): ?>

<tr>

    <td><?= $row['status_code'] ?></td>

    <td><?= $row['status_name'] ?></td>

    <td><?= $row['status_color'] ?></td>

</tr>

<?php endforeach; ?>

</table>