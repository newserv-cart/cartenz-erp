<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">

<title>
Manifest
</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

</head>

<body>

<div class="container mt-4">

    <h2>
        🚚 CARTENZ GLOBAL EXPRESS
    </h2>

<img
src="https://barcode.tec-it.com/barcode.ashx?data=<?= $manifest['manifest_no'] ?>&code=Code128"
style="height:80px;">

<a href="<?= site_url('manifest/print/'.$manifest['id']) ?>"
class="btn btn-success">

🖨 Print Manifest

</a>
    
    <h4>
        Manifest :
        <?= esc($manifest['manifest_no']) ?>
    </h4>

    <hr>

    <table class="table table-bordered">

        <tr>
            <th>No</th>
            <th>AWB</th>
            <th>Status</th>
            <th>Weight</th>
        </tr>

        <?php
        $no=1;
        $totalWeight=0;
        ?>

        <?php foreach($details as $s): ?>


            

        <?php
        $totalWeight +=
            ($s['chargeable_weight'] ?? 0);
        ?>

        <tr>

            <td><?= $no++ ?></td>

            <td><?= esc($s['awb_no']) ?></td>

            <td><?= esc($s['shipment_status']) ?></td>

            <td>
                <?= number_format(
                    ($s['chargeable_weight'] ?? 0),
                    2
                ) ?>
                Kg
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

    <hr>

    <h5>
    Total Shipment :
    <?= count($details) ?>
</h5>

    <h5>
        Total Weight :
        <?= number_format(
            $totalWeight/1000,
            2
        ) ?>
        Kg
    </h5>

</div>



<script>
window.print();
</script>

</body>
</html>