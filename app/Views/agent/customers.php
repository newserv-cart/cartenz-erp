<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">

<h2>👥 Agent Customers</h2>

<a href="<?= site_url('agent/dashboard') ?>"
   class="btn btn-secondary mb-3">
   Back Dashboard
</a>

<table class="table table-bordered">

<thead>

<tr>
    <th>Customer</th>
<th>Status</th>
<th>Billing</th>
<th>Outstanding</th>
<th>Action</th>


</tr>

</thead>

<tbody>

<?php foreach($customers as $c): ?>

<tr>

    <td>
        <?= esc($c['customer_name']) ?>
    </td>

    <td>

        <?php if($c['account_status']=='LOCKED'): ?>

            <span class="badge bg-danger">
                LOCKED
            </span>

        <?php else: ?>

            <span class="badge bg-success">
                ACTIVE
            </span>

        <?php endif; ?>

    </td>

    <td>
        <?= esc($c['billing_type']) ?>
    </td>

    <td>
        Rp <?= number_format($c['outstanding']) ?>
    </td>

 <td>
        <a href="<?= site_url('agent/customer/'.$c['id']) ?>"
           class="btn btn-sm btn-primary">
           View
        </a>
    </td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>