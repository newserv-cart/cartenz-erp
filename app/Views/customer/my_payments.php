<h3 class="mb-3">💳 My Payments</h3>

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th>Payment No</th>
                <th>Invoice No</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
                <th>Proof</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($payments)): ?>

                <?php foreach ($payments as $payment): ?>
                    <?php
                    $status = strtoupper(
                        trim((string) ($payment['status'] ?? ''))
                    );

                    $badgeClass = match ($status) {
                        'APPROVED' => 'bg-success',
                        'REJECTED' => 'bg-danger',
                        'PENDING'  => 'bg-warning text-dark',
                        default    => 'bg-secondary',
                    };
                    ?>

                    <tr>
                        <td>
                            <?= esc($payment['payment_no'] ?? '-') ?>
                        </td>

                        <td>
                            <?= esc($payment['invoice_no'] ?? '-') ?>
                        </td>

                        <td class="text-end">
                            Rp <?= number_format(
                                (float) ($payment['amount'] ?? 0),
                                0,
                                ',',
                                '.'
                            ) ?>
                        </td>

                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= esc($status ?: '-') ?>
                            </span>
                        </td>

                        <td>
                            <?php if (!empty($payment['proof_file'])): ?>
                                <a
                                    href="<?= base_url(
                                        'uploads/payments/' .
                                        rawurlencode(
                                            basename(
                                                (string) $payment['proof_file']
                                            )
                                        )
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View Proof
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td
                        colspan="5"
                        class="text-center text-muted"
                    >
                        Belum ada riwayat pembayaran.
                    </td>
                </tr>

            <?php endif; ?>
        </tbody>
    </table>
</div>