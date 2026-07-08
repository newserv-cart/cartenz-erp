<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>🏢 AGENT DASHBOARD</h2>

<hr>

<div class="mb-3">

<a href="<?= site_url('agent/customers') ?>"
   class="btn btn-primary">
   👥 Customers
</a>

<a href="<?= site_url('agent/invoices') ?>"
   class="btn btn-warning">
   🧾 Invoices
</a>

<a href="<?= site_url('manifests') ?>"
   class="btn btn-info">
   📋 Manifests
</a>

<a href="<?= site_url('shipments') ?>"
   class="btn btn-success">
   📦 Shipments
</a>




</div>

<div class="row">

    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <h5>👥 Customers</h5>
                <h2><?= $totalCustomers ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <h5>📦 Shipments</h5>
                <h2><?= $totalShipments ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <h5>🧾 Invoices</h5>
                <h2><?= $totalInvoices ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <h5>💰 Outstanding</h5>
                <h5>
                    Rp <?= number_format($outstanding) ?>
                </h5>
            </div>
        </div>
    </div>

</div>

<div class="card mt-4">

    <div class="card-header">
        Agent Customers
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th>Customer</th>
                <th>Status</th>
            </tr>

            <?php foreach($customers as $c): ?>

            <tr>

                <td><?= esc($c['customer_name']) ?></td>

                <td><?= esc($c['account_status']) ?></td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>

</div>