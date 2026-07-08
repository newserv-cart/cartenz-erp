<?php
/**
 * File: pricing.php
 * Lokasi: app/Views/shipments/partials/pricing.php
 * Fungsi: Rincian biaya lengkap dengan ikon, format lokal, ringkasan, dan audit trail
 */
?>

<div class="row">

    <!-- CUSTOMER CHARGE -->
    <div class="col-lg-6">
        <div class="card shadow mb-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">💰 Customer Charge</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-bordered mb-0">
                    <tr>
                        <th>🚚 Freight</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['freight'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>⛽ Fuel Surcharge</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['fuel'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>📍 Remote Area</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['remote'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>🌱 Go Green</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['gogreen'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>🛡️ Insurance</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['insurance'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>📦 Packing</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['packing'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>💎 Commodity / Special</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['commodity'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>➕ Additional Charge</th>
                        <td class="text-end">Rp <?= number_format($pricing_breakdown['additional'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>🏷️ Discount</th>
                        <td class="text-end text-danger">- Rp <?= number_format($pricing_breakdown['discount'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="table-primary">
                        <th><strong>TOTAL CUSTOMER</strong></th>
                        <th class="text-end"><strong>Rp <?= number_format($pricing_breakdown['total'] ?? 0, 0, ',', '.') ?></strong></th>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- VENDOR COST -->
    <div class="col-lg-6">
        <div class="card shadow mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🏢 Vendor Cost</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-bordered mb-0">
                    <tr>
                        <th>🚚 Freight</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['freight'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>⛽ Fuel Surcharge</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['fuel'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>📍 Remote Area</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['remote'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>🌱 Go Green</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['gogreen'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>⚙️ Handling Fee</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['handling'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>💳 VAT / Tax</th>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['vat'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="table-success">
                        <th><strong>TOTAL VENDOR</strong></th>
                        <th class="text-end"><strong>Rp <?= number_format($vendor_breakdown['total'] ?? 0, 0, ',', '.') ?></strong></th>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
$profit = $profit ?? 0;
$margin = $margin ?? 0;
$markup = $markup ?? 0;
$recommendation = $recommendation ?? [];
$bestVendor = !empty($recommendation) ? $recommendation[0] : null;

// Warna status
$profitColor = $profit < 0 ? 'danger' : ($profit < 100000 ? 'warning' : 'success');
$marginColor = $margin < 10 ? 'text-danger' : 'text-primary';
$barColor = $margin < 10 ? 'bg-danger' : ($margin < 20 ? 'bg-warning' : 'bg-success');
$percent = min(100, max(0, $margin));
?>

<!-- ⚠️ PERINGATAN -->
<?php if ($profit < 0): ?>
    <div class="alert alert-danger mb-3">
        🚨 <strong>PERINGATAN:</strong> Pengiriman ini mengalami kerugian. Biaya lebih tinggi dari harga jual.
    </div>
<?php elseif ($margin < 10 && $profit > 0): ?>
    <div class="alert alert-warning mb-3">
        ⚠️ <strong>PERINGATAN:</strong> Margin keuntungan hanya <?= number_format($margin, 1, ',', '.') ?>% (di bawah batas aman 10%).
    </div>
<?php endif; ?>

<!-- 📈 PROFIT ANALYSIS DENGAN BACKGROUND -->
<div class="card shadow mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">📈 Profit Analysis</h5>
    </div>
    <div class="card-body">

        <div class="row text-center g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-<?= $profitColor ?> bg-light">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-2">Net Profit</h6>
                        <h3 class="text-<?= $profitColor ?> fw-bold mb-0">
                            Rp <?= number_format($profit, 0, ',', '.') ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info bg-light">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-2">Margin</h6>
                        <h3 class="<?= $marginColor ?> fw-bold mb-0">
                            <?= number_format($margin, 2, ',', '.') ?> %
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-secondary bg-light">
                    <div class="card-body p-3">
                        <h6 class="text-muted mb-2">Markup</h6>
                        <h3 class="text-secondary fw-bold mb-0">
                            <?= number_format($markup, 2, ',', '.') ?> %
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar Margin -->
        <div class="progress mb-4" style="height: 28px;">
            <div class="progress-bar <?= $barColor ?>"
                 role="progressbar"
                 style="width: <?= $percent ?>%; font-weight: bold;"
                 aria-valuenow="<?= $percent ?>"
                 aria-valuemin="0"
                 aria-valuemax="100">
                Margin: <?= number_format($margin, 1, ',', '.') ?> %
            </div>
        </div>

        <!-- 📋 PRICING SUMMARY -->
        <div class="card border-primary mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">📋 Pricing Summary</h5>
            </div>
            <div class="card-body p-3">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td>Total Customer Charge</td>
                        <td class="text-end fw-bold">Rp <?= number_format($pricing_breakdown['total'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <td>Total Vendor Cost</td>
                        <td class="text-end">Rp <?= number_format($vendor_breakdown['total'] ?? 0, 0, ',', '.') ?></td>
                    </tr>
                    <tr class="border-top pt-2">
                        <td><strong>Net Profit</strong></td>
                        <td class="text-end fw-bold fs-5 text-<?= $profitColor ?>">
                            Rp <?= number_format($profit, 0, ',', '.') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Margin</td>
                        <td class="text-end <?= $marginColor ?> fw-bold">
                            <?= number_format($margin, 2, ',', '.') ?> %
                        </td>
                    </tr>
                    <?php if ($bestVendor): ?>
                    <tr>
                        <td>Recommended Vendor</td>
                        <td class="text-end fw-bold text-success">
                            <?= $bestVendor['vendor_name'] ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- 📝 PRICING SOURCE / AUDIT TRAIL -->
        <div class="card border-secondary mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">📝 Pricing Source</h5>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Service Type:</strong> <?= strtoupper($rate_source['service_type'] ?? '-') ?></p>
                        <p class="mb-1"><strong>Vendor:</strong> <?= $rate_source['vendor_name'] ?? '-' ?></p>
                        <p class="mb-1"><strong>Rate Version:</strong> <?= $rate_source['version'] ?? 'July 2026' ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Charge Weight:</strong> <?= number_format($charge_weight ?? 0, 2, ',', '.') ?> Kg</p>
                        <p class="mb-1"><strong>Zone:</strong> <?= $zone ?? '-' ?></p>
                        <p class="mb-1"><strong>Shipment Type:</strong> <?= strtoupper($shipment_type ?? 'PACKAGE') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🏆 BEST VENDOR RECOMMENDATION -->
        <?php if ($bestVendor): ?>
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🏆 Best Vendor Recommendation</h5>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><?= $bestVendor['star_rating'] ?> <?= $bestVendor['vendor_name'] ?></h4>
                        <p class="text-muted mb-2"><?= $bestVendor['reason'] ?? 'Recommended based on profit & performance' ?></p>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td>Vendor Cost:</td>
                                <td class="fw-bold">Rp <?= number_format($bestVendor['vendor_cost'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Profit:</td>
                                <td class="fw-bold text-success">Rp <?= number_format($bestVendor['profit'], 0, ',', '.') ?></td>
                            </tr>
                            <tr>
                                <td>Margin:</td>
                                <td class="fw-bold <?= $marginColor ?>"><?= number_format($bestVendor['margin'], 2, ',', '.') ?> %</td>
                            </tr>
                        </table>
                    </div>
                    <div>
                        <button type="button" class="btn btn-lg btn-primary use-vendor" data-vendor-id="<?= $bestVendor['vendor_id'] ?>">
                            ✅ Use This Vendor
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            ℹ️ Lengkapi data pengiriman untuk melihat rekomendasi vendor terbaik.
        </div>
        <?php endif; ?>

    </div>
</div>