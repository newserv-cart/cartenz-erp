<h2>💰 My Invoices</h2>

<table border="1" cellpadding="8" width="100%">

    <thead>
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
    </thead>

    <tbody>

        <?php if (!empty($invoices)): ?>

            <?php foreach ($invoices as $invoice): ?>

                <?php
                $invoiceStatus = strtoupper(
                    trim(
                        (string) (
                            $invoice['invoice_status']
                            ?? 'UNPAID'
                        )
                    )
                );

                $grandTotal = (float) (
                    ($invoice['grand_total'] ?? 0) > 0
                        ? $invoice['grand_total']
                        : ($invoice['total_amount'] ?? 0)
                );

                $balanceAmount = round(
                    (float) (
                        $invoice['balance_amount']
                        ?? $grandTotal
                    ),
                    2
                );

                $canUploadPayment =
                    $invoiceStatus !== 'PAID'
                    && $balanceAmount > 0;
                ?>

                <tr>

                    <td>
                        <?= esc(
                            $invoice['invoice_no'] ?? '-'
                        ) ?>
                    </td>

                    <td>
                        <?= esc(
                            $invoice['invoice_date'] ?? '-'
                        ) ?>
                    </td>

                    <td>
                        <?= esc(
                            $invoice['due_date'] ?? '-'
                        ) ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $grandTotal,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </td>

                    <td>
                        Rp <?= number_format(
                            $balanceAmount,
                            0,
                            ',',
                            '.'
                        ) ?>
                    </td>

                    <td>
                        <?= esc($invoiceStatus) ?>
                    </td>

                    <td>
                        <a
                            href="<?= site_url(
                                'invoices/pdf/' .
                                (int) $invoice['id']
                            ) ?>"
                            target="_blank"
                        >
                            PDF
                        </a>
                    </td>

                    <td>

                        <?php if ($canUploadPayment): ?>

                            <a
                                class="btn btn-sm btn-primary"
                                href="<?= site_url(
                                    'customer/payments/create/' .
                                    (int) $invoice['id']
                                ) ?>"
                            >
                                Upload Payment
                            </a>

                        <?php else: ?>

                            <span
                                style="
                                    color: green;
                                    font-weight: bold;
                                "
                            >
                                ✓ Lunas
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>
                <td
                    colspan="8"
                    style="text-align: center;"
                >
                    Belum ada invoice.
                </td>
            </tr>

        <?php endif; ?>

    </tbody>

</table>