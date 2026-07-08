<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<a href="<?= site_url('agent/customers') ?>"
   class="btn btn-secondary mb-3">
   Back
</a>

<div class="card mb-4">

    <div class="card-header">
        Customer Information
    </div>

    <div class="card-body">

        <h4><?= esc($customer['customer_name']) ?></h4>

        <p>
            Status :
            <strong><?= esc($customer['account_status']) ?></strong>
        </p>

        <p>
            Billing :
            <?= esc($customer['billing_type']) ?>
        </p>

    </div>

</div>
<div class="card mb-4">

    <div class="card-header">
        📦 Shipments
    </div>

    <div class="card-body">

        <table class="table table-bordered">

          <tr>
    <th>AWB</th>
    <th>Status</th>
    <th>Action</th>
</tr>

            <?php foreach($shipments as $s): ?>

            <tr>

                <td>
                    <?= esc($s['awb_no']) ?>
                </td>

                <td>
                    <?= esc($s['shipment_status']) ?>
                </td>
<td>
    <a href="<?= site_url('agent/shipment/'.$s['id']) ?>"
       class="btn btn-sm btn-primary">
       View
    </a>
</td>
            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>

<div class="card">

    <div class="card-header">
        🧾 Invoices
    </div>

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th>Invoice No</th>
                <th>Total</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>

            <?php foreach($invoices as $inv): ?>

            <tr>

                <td>
                    <?= esc($inv['invoice_no']) ?>
                </td>

                <td>
                    Rp <?= number_format($inv['total_amount']) ?>
                </td>

                <td>
                    Rp <?= number_format($inv['balance_amount']) ?>
                </td>

                <td>
                    <?= esc($inv['invoice_status']) ?>
                </td>

            </tr>

            <?php endforeach; ?>

        </table>

    </div>

</div>