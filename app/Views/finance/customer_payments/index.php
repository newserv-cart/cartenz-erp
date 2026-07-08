<h2>👤 Customer Payments</h2>
<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        <th>Payment No</th>
        <th>Reference</th>
        <th>Amount</th>
        <th>Proof</th>
        <th>Status</th>
        <th>Action</th>
    </tr>



    <?php foreach($payments as $payment): ?>

    <tr>

        <td><?= $payment['payment_no'] ?></td>

        <td><?= esc($payment['invoice_no']) ?></td>

        <td><?= number_format($payment['amount']) ?></td>

        <td>
            <a target="_blank"
               href="<?= base_url('uploads/payments/'.$payment['proof_file']) ?>">
               View Proof
            </a>
        </td>

        <td><?= $payment['status'] ?></td>

        <td>

<?php if ($payment['status'] == 'PENDING'): ?>

    <a href="<?= site_url('finance/customer-payments/approve/'.$payment['id']) ?>">
        Approve
    </a>

    |

    <a href="<?= site_url('finance/customer-payments/reject/'.$payment['id']) ?>">
        Reject
    </a>

<?php elseif ($payment['status'] == 'APPROVED'): ?>

    <span style="color:green;font-weight:bold;">
        ✓ Approved
    </span>

<?php else: ?>

    <span style="color:red;font-weight:bold;">
        ✗ Rejected
    </span>

<?php endif; ?>

</td>

    </tr>

    <?php endforeach; ?>

</table>