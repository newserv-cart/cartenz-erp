<h2>💰 My Invoices</h2>

<table border="1" cellpadding="8" width="100%">

<tr>
    <th>Invoice No</th>
    <th>Date</th>
    <th>Due Date</th>
    <th>Total</th>
    <th>Balance</th>
    <th>Status</th>
    <th>PDF</th>
    <th>Payment</th>
</tr>

<?php foreach($invoices as $invoice): ?>

<tr>

    <td><?= esc($invoice['invoice_no']) ?></td>

    <td><?= esc($invoice['invoice_date']) ?></td>

    <td><?= esc($invoice['due_date']) ?></td>

    <td>
        Rp <?= number_format(
            $invoice['grand_total'] ?? $invoice['total_amount'],
            0,
            ',',
            '.'
        ) ?>
    </td>

    <td>
        Rp <?= number_format(
            $invoice['balance_amount'],
            0,
            ',',
            '.'
        ) ?>
    </td>

    <td><?= esc($invoice['invoice_status']) ?></td>

    <td>
        <a href="<?= site_url('invoices/pdf/'.$invoice['id']) ?>">
            PDF
        </a>
    </td>

    <td>
        <a
            class="btn btn-sm btn-primary"
            href="<?= site_url('customer/payments/create/'.$invoice['id']) ?>">
            Upload Payment
        </a>
    </td>

</tr>

<?php endforeach; ?>

</table>