<!DOCTYPE html>
<html>
<head>
    <title>Invoice List</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <h3>Daftar Invoice</h3>

    <table class="table table-bordered">

        <thead>

        <tr>
            <th>No Invoice</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        </thead>

        <tbody>

        <?php foreach($invoices as $row): ?>

        <tr>

            <td><?= esc($row['invoice_no']) ?></td>

            <td><?= esc($row['invoice_date']) ?></td>

            <td>
                <?= number_format(
                    $row['total_amount'],
                    0,
                    ',',
                    '.'
                ) ?>
            </td>

            <td><?= esc($row['invoice_status']) ?></td>

            <td>

                <a
                    href="<?= base_url('invoices/show/'.$row['id']) ?>"
                    class="btn btn-primary btn-sm">

                    Detail

                </a>

            </td>

        </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

</body>
</html>