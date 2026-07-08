<h2>🏢 Agent Payments</h2>

<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        <th>Payment No</th>
        <th>Invoice ID</th>
        <th>Amount</th>
        <th>Proof</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php foreach ($payments as $payment): ?>

    <tr>

        <td><?= esc($payment['payment_no']) ?></td>

        <td><?= esc($payment['invoice_id']) ?></td>

        <td><?= number_format($payment['amount']) ?></td>

        <td>
            <a target="_blank"
               href="<?= base_url('uploads/payments/'.$payment['proof_file']) ?>">
                View Proof
            </a>
        </td>

        <td><?= $payment['payment_type']=='AGENT'
        ? $payment['agent_invoice_id']
        : $payment['invoice_id']; ?></td>

        <td>
            <a href="<?= site_url('finance/agent-payments/approve/'.$payment['id']) ?>">
                Approve
            </a>
            |
            <a href="<?= site_url('finance/agent-payments/reject/'.$payment['id']) ?>">
                Reject
            </a>
        </td>

    </tr>

    <?php endforeach; ?>

</table>