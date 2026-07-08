<!DOCTYPE html>
<html>
<head>
    <title>Manifest List</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h2>📦 Manifest List</h2>

    <div class="mb-3">

    <a
       href="<?= base_url('manifests/create') ?>"
       class="btn btn-primary">

       ➕ Create Manifest

    </a>

    <a
       href="<?= base_url('manifests/scan') ?>"
       class="btn btn-success">

       📱 Scan Manifest

    </a>

</div>

    <table class="table table-bordered">

        <tr>
<th>ID</th>
    <th>Manifest No</th>
    <th>Status</th>
    <th>Date</th>
    <th>Action</th>
        </tr>

        <?php foreach($manifests as $row): ?>

       <tr>

    <td><?= $row['id'] ?></td>

    <td><?= $row['manifest_no'] ?></td>

    <td><?= $row['status'] ?></td>

    <td><?= $row['manifest_date'] ?></td>

    <td>

        <a
            href="<?= base_url('manifests/view/'.$row['id']) ?>"
            class="btn btn-primary btn-sm">

            Open

        </a>

    </td>

</tr>

        <?php endforeach; ?>

    </table>

</div>

</body>
</html>