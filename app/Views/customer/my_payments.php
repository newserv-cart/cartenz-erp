<h2>💳 My Payments</h2>

<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        <th>Payment No</th>
        <th>Invoice ID</th>
        <th>Amount</th>
        <th>Status</th>
        <th>Proof</th>
    </tr>

    <?php foreach($payments as $payment): ?>

    <tr>

        <td><?= $payment['payment_no'] ?></td>

        <td><?= $payment['invoice_id'] ?></td>

        <td>Rp <?= number_format($payment['amount']) ?></td>

        <td>
            <?php if($payment['status']=='APPROVED'): ?>
                ✅ APPROVED
            <?php elseif($payment['status']=='REJECTED'): ?>
                ❌ REJECTED
            <?php else: ?>
                ⏳ PENDING
            <?php endif; ?>
        </td>

        <td>
            <a target="_blank"
               href="<?= base_url('uploads/payments/'.$payment['proof_file']) ?>">
               View Proof
            </a>
        </td>

    </tr>

    <?php endforeach; ?>

</table>