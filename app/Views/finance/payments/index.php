<h2>Pending Payments</h2>

<table border="1" cellpadding="8" cellspacing="0">

    <tr>
        
<th>Reference</th>        <th>Amount</th>
        <th>Proof</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php foreach($payments as $payment): ?>

    <tr>

        <td>
<?= $payment['payment_type']=='CUSTOMER'
    ? $payment['invoice_id']
    : $payment['agent_invoice_id']; ?>
</td>

        <td><?= number_format($payment['amount']) ?></td>

        <td>
            <a target="_blank"
               href="<?= base_url('uploads/payments/'.$payment['proof_file']) ?>">
               View Proof
            </a>
        </td>

        <td><?= $payment['status'] ?></td>

        <td>

            <a href="<?= site_url('finance/payments/approve/'.$payment['id']) ?>">
                Approve
            </a>

            |

            <a href="<?= site_url('finance/payments/reject/'.$payment['id']) ?>">
                Reject
            </a>

        </td>

    </tr>

    <?php endforeach; ?>

</table>