<?php
$isAgentView = false;

try {
    $actorId = (int) (auth()->id() ?? 0);

    if ($actorId > 0) {
        $context = new \App\Services\BranchOperationalContextService();
        $profile = $context->profileForUser($actorId);

        $isAgentView = is_array($profile)
            && !$context->profileIsMain($profile)
            && (int) ($profile['agent_id'] ?? 0) > 0;
    }
} catch (\Throwable $e) {
    $isAgentView = false;
}

$title = $isAgentView ? 'Tagihan Agen' : 'Biaya Pengiriman';
$total = (float) ($shipment['total_charge'] ?? $shipment['selling_amount'] ?? 0);
$agentInvoiceId = (int) ($shipment['agent_invoice_id'] ?? 0);
$isAgentInvoiced = (int) ($shipment['is_agent_invoiced'] ?? 0) === 1;
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">💳 <?= esc($title) ?></h5>
    </div>

    <div class="card-body">
        <div class="alert alert-light border">
            Hanya biaya pihak yang sedang login yang ditampilkan. Vendor cost, profit,
            margin, dan rekomendasi vendor disembunyikan.
        </div>

        <table class="table table-sm align-middle mb-3">
            <tr>
                <th width="260"><?= $isAgentView ? 'Freight Agen' : 'Freight Customer' ?></th>
                <td class="text-end">Rp <?= number_format((float) ($shipment['freight_charge'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Insurance</th>
                <td class="text-end">Rp <?= number_format((float) ($shipment['insurance_amount'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Packing</th>
                <td class="text-end">Rp <?= number_format((float) ($shipment['packing_charge'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Additional Charge</th>
                <td class="text-end">Rp <?= number_format((float) ($shipment['additional_charge'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr>
                <th>Discount</th>
                <td class="text-end">- Rp <?= number_format((float) ($shipment['discount_amount'] ?? 0), 0, ',', '.') ?></td>
            </tr>
            <tr class="table-primary fw-bold">
                <th><?= $isAgentView ? 'Total yang Harus Dibayar Agen' : 'Total yang Harus Dibayar' ?></th>
                <td class="text-end">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </table>

        <?php if ($isAgentView): ?>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php if ($isAgentInvoiced && $agentInvoiceId > 0): ?>
                    <span class="badge bg-success">Sudah masuk Agent Invoice</span>
                    <a href="<?= site_url('agent/self-billing/invoice/' . $agentInvoiceId) ?>"
                       class="btn btn-sm btn-outline-primary">Lihat Invoice</a>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Belum Ditagihkan</span>
                <?php endif; ?>

                <a href="<?= site_url('agent/self-billing') ?>" class="btn btn-sm btn-primary">
                    🧾 Buka Self Billing
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
