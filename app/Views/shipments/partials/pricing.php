<?php
$profitValue = (float) ($profit ?? $shipment['profit_amount'] ?? 0);
$marginValue = (float) ($margin ?? $shipment['margin_percent'] ?? 0);
$markupValue = (float) ($shipment['markup_percent'] ?? 0);

$profitClass = $profitValue < 0
    ? 'danger'
    : ($marginValue < 10 ? 'warning' : 'success');

$customerRows = $customerCosts ?? [];
$vendorRows   = $vendorCosts ?? [];
?>

<?php if ($profitValue < 0): ?>
    <div class="alert alert-danger">
        🚨 <strong>Warning:</strong>
        Shipment ini mengalami kerugian sebesar
        Rp <?= number_format(abs($profitValue), 0, ',', '.') ?>.
    </div>
<?php elseif ($marginValue < 10): ?>
    <div class="alert alert-warning">
        ⚠️ Margin shipment hanya
        <?= number_format($marginValue, 2, ',', '.') ?>%.
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">💰 Customer Charge</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Component</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customerRows)): ?>
                                <tr>
                                    <td>Freight Customer</td>
                                    <td class="text-end">
                                        Rp <?= number_format((float) ($shipment['freight_charge'] ?? $shipment['selling_amount'] ?? 0), 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customerRows as $row): ?>
                                    <?php $amount = (float) ($row['amount'] ?? 0); ?>
                                    <tr>
                                        <td><?= esc($row['component_name'] ?? $row['component_code'] ?? '-') ?></td>
                                        <td class="text-end <?= $amount < 0 ? 'text-danger' : '' ?>">
                                            Rp <?= number_format($amount, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th>Total Customer</th>
                                <th class="text-end">
                                    Rp <?= number_format((float) ($customerTotal ?? $shipment['total_charge'] ?? 0), 0, ',', '.') ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🏢 Vendor Cost</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Component</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vendorRows)): ?>
                                <tr>
                                    <td>Base Vendor Cost</td>
                                    <td class="text-end">
                                        Rp <?= number_format((float) ($shipment['vendor_cost'] ?? 0), 0, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($vendorRows as $row): ?>
                                    <tr>
                                        <td><?= esc($row['component_name'] ?? $row['component_code'] ?? '-') ?></td>
                                        <td class="text-end">
                                            Rp <?= number_format((float) ($row['amount'] ?? 0), 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-success">
                            <tr>
                                <th>Total Vendor</th>
                                <th class="text-end">
                                    Rp <?= number_format((float) ($vendorTotal ?? $shipment['vendor_total'] ?? 0), 0, ',', '.') ?>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-warning">
        <h5 class="mb-0">📈 Profit Analysis</h5>
    </div>

    <div class="card-body">
        <div class="row text-center g-3">
            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Customer Total</div>
                    <div class="fw-bold fs-5">
                        Rp <?= number_format((float) ($customerTotal ?? $shipment['total_charge'] ?? 0), 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Vendor Total</div>
                    <div class="fw-bold fs-5">
                        Rp <?= number_format((float) ($vendorTotal ?? $shipment['vendor_total'] ?? 0), 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border border-<?= esc($profitClass) ?> rounded p-3 h-100">
                    <div class="text-muted small">Net Profit</div>
                    <div class="fw-bold fs-5 text-<?= esc($profitClass) ?>">
                        Rp <?= number_format($profitValue, 0, ',', '.') ?>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small">Margin / Markup</div>
                    <div class="fw-bold fs-5">
                        <?= number_format($marginValue, 2, ',', '.') ?>%
                    </div>
                    <div class="small text-muted">
                        Markup <?= number_format($markupValue, 2, ',', '.') ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
