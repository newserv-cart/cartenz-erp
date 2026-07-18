<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($invoice['invoice_no'] ?? 'Agent Invoice') ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 32px; }
        .header { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 28px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 8px; font-size: 12px; }
        th { background: #f1f1f1; }
        .text-end { text-align: right; }
        .totals { width: 420px; margin-left: auto; margin-top: 20px; }
        @media print { .no-print { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <button type="button" class="no-print" onclick="window.print()">Print / Save PDF</button>

    <div class="header">
        <div>
            <h2>AGENT INVOICE</h2>
            <strong><?= esc($agent['agent_name'] ?? '-') ?></strong>
            <div><?= esc($agent['agent_code'] ?? '-') ?></div>
        </div>
        <div>
            <table>
                <tr><th>Invoice No</th><td><?= esc($invoice['invoice_no'] ?? '-') ?></td></tr>
                <tr><th>Invoice Date</th><td><?= esc($invoice['invoice_date'] ?? '-') ?></td></tr>
                <tr><th>Due Date</th><td><?= esc($invoice['due_date'] ?? '-') ?></td></tr>
                <tr><th>Status</th><td><?= esc($invoice['invoice_status'] ?? '-') ?></td></tr>
            </table>
        </div>
    </div>

    <table>
        <thead><tr><th>AWB</th><th>Date</th><th class="text-end">Weight</th><th class="text-end">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($invoice['details'] as $detail): ?>
            <tr>
                <td><?= esc($detail['awb_no'] ?? '-') ?></td>
                <td><?= esc($detail['shipment_date'] ?? '-') ?></td>
                <td class="text-end"><?= number_format((float) ($detail['chargeable_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                <td class="text-end">Rp <?= number_format((float) ($detail['line_total'] ?? 0), 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table class="totals">
        <tr><th>Total</th><td class="text-end">Rp <?= number_format((float) ($invoice['total_amount'] ?? 0), 0, ',', '.') ?></td></tr>
        <tr><th>Paid</th><td class="text-end">Rp <?= number_format((float) ($invoice['paid_amount'] ?? 0), 0, ',', '.') ?></td></tr>
        <tr><th>Balance</th><td class="text-end">Rp <?= number_format((float) ($invoice['balance_amount'] ?? 0), 0, ',', '.') ?></td></tr>
    </table>
</body>
</html>
