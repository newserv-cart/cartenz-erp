<h1>💰 Finance Dashboard</h1>

<h3>Total Invoice :
Rp <?= number_format($totalInvoice) ?></h3>

<h3>Total Paid :
Rp <?= number_format($totalPaid) ?></h3>

<h3>Total Unpaid :
Rp <?= number_format($totalUnpaid) ?></h3>

<h3>Pending Payments :
<?= $pendingPayments ?></h3>