<?php
$vendorName = $shipment['vendor_name'] ?? '-';
$vendorRecommendations = $vendorRecommendations ?? [];
$vendorActionState = $vendorActionState ?? [];
$vendorTrackingEvents = $vendorTrackingEvents ?? [];
$vendorTrackingStatusOptions = $vendorTrackingStatusOptions ?? [];
$shipmentId = (int) ($shipment['id'] ?? 0);
$submitted = !empty($vendorActionState['submitted']);
$assigned = !empty($vendorActionState['assigned']);
$currentShipmentStatus = strtoupper(
    (string) ($shipment['shipment_status'] ?? '')
);
$vendorTrackingTerminal = in_array(
    $currentShipmentStatus,
    ['DELIVERED', 'RETURN_TO_SENDER', 'CANCELLED'],
    true
);

$canManageVendorWorkflow = false;

try {
    $actorId = (int) (auth()->id() ?? 0);

    if ($actorId > 0) {
        $branchContext =
            new \App\Services\BranchOperationalContextService();

        $profile =
            $branchContext->profileForUser($actorId);

        $canManageVendorWorkflow =
            is_array($profile)
            && $branchContext->profileIsMain($profile);
    }
} catch (\Throwable $e) {
    $canManageVendorWorkflow = false;
}
?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">🏢 Vendor Assignment</h5>
            </div>

            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="190">Vendor</th><td><?= esc($vendorName) ?></td></tr>
                    <tr><th>Assignment Status</th><td><?= esc($shipment['vendor_assignment_status'] ?? 'UNASSIGNED') ?></td></tr>
                    <tr><th>Assignment Source</th><td><?= esc($shipment['vendor_assignment_source'] ?? '-') ?></td></tr>
                    <tr><th>Rate Source</th><td><?= esc($shipment['vendor_rate_source'] ?? '-') ?></td></tr>
                    <tr><th>Locked Rate Weight</th><td><?= isset($shipment['vendor_rate_weight']) ? number_format((float) $shipment['vendor_rate_weight'], 2, ',', '.') . ' Kg' : '-' ?></td></tr>
                    <tr><th>Vendor Cost Locked</th><td><?= !empty($shipment['vendor_cost_locked_at']) ? esc($shipment['vendor_cost_locked_at']) : 'No' ?></td></tr>
                    <tr><th>Submission</th><td><?= esc($shipment['vendor_submission_status'] ?? 'NOT_SUBMITTED') ?></td></tr>
                    <tr><th>Vendor AWB</th><td><?= esc($shipment['vendor_awb_no'] ?? '-') ?></td></tr>
                    <tr><th>Master AWB</th><td><?= esc($shipment['master_awb_no'] ?? '-') ?></td></tr>
                    <tr><th>Vendor Tracking</th><td><?= esc($shipment['tracking_no_vendor'] ?? '-') ?></td></tr>
                </table>

                <?php if (!empty($shipment['vendor_override_reason'])): ?>
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Override Reason:</strong><br>
                        <?= esc($shipment['vendor_override_reason']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-light fw-bold">Vendor Cost Lock</div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <tr><th>Vendor Freight</th><td class="text-end">Rp <?= number_format((float) ($shipment['vendor_freight'] ?? $shipment['vendor_cost'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr><th>Fuel</th><td class="text-end">Rp <?= number_format((float) ($shipment['fuel_charge'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr><th>Remote Area</th><td class="text-end">Rp <?= number_format((float) ($shipment['vendor_remote_charge'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr><th>Go Green</th><td class="text-end">Rp <?= number_format((float) ($shipment['go_green_charge'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr><th>Handling</th><td class="text-end">Rp <?= number_format((float) ($shipment['handling_charge'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr><th>VAT</th><td class="text-end">Rp <?= number_format((float) ($shipment['vat_amount'] ?? 0), 0, ',', '.') ?></td></tr>
                    <tr class="table-success"><th>Total Vendor</th><th class="text-end">Rp <?= number_format((float) ($vendorTotal ?? $shipment['vendor_total'] ?? 0), 0, ',', '.') ?></th></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <div class="fw-bold">Vendor Recommendations</div>
            <div class="small text-muted">Ranking menggunakan vendor cost terendah, lalu vendor priority.</div>
        </div>

        <?php if (!empty($vendorActionState['can_generate'])): ?>
            <form method="post" action="<?= site_url('shipments/vendor/' . $shipmentId . '/recommendations/generate') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">🔄 Generate Recommendations</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Rank</th><th>Vendor</th><th>Rate</th><th>Weight</th>
                        <th class="text-end">Vendor Cost</th><th class="text-end">Profit</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vendorRecommendations)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada rekomendasi vendor.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vendorRecommendations as $recommendation): ?>
                            <tr class="<?= (int) ($recommendation['recommended'] ?? 0) === 1 ? 'table-success' : '' ?>">
                                <td>
                                    #<?= (int) ($recommendation['rank_no'] ?? 0) ?>
                                    <?php if ((int) ($recommendation['recommended'] ?? 0) === 1): ?>
                                        <span class="badge bg-success">BEST</span>
                                    <?php endif; ?>
                                </td>
                                <td><div class="fw-semibold"><?= esc($recommendation['vendor_name']) ?></div><div class="small text-muted"><?= esc($recommendation['vendor_code'] ?? '') ?></div></td>
                                <td><div><?= esc($recommendation['rate_source'] ?? '-') ?></div><div class="small text-muted"><?= esc($recommendation['currency_code'] ?? 'IDR') ?> · <?= esc($recommendation['rate_effective_date'] ?? '-') ?></div></td>
                                <td><?= number_format((float) ($recommendation['chargeable_weight'] ?? 0), 2, ',', '.') ?> → <?= number_format((float) ($recommendation['rate_weight'] ?? 0), 2, ',', '.') ?> Kg</td>
                                <td class="text-end">Rp <?= number_format((float) $recommendation['vendor_cost'], 0, ',', '.') ?></td>
                                <td class="text-end <?= (float) $recommendation['profit'] < 0 ? 'text-danger' : 'text-success' ?>">Rp <?= number_format((float) $recommendation['profit'], 0, ',', '.') ?></td>
                                <td style="min-width:260px">
                                    <?php if (!empty($vendorActionState['can_assign']) && $canManageVendorWorkflow): ?>
                                        <form method="post" action="<?= site_url('shipments/vendor/' . $shipmentId . '/assign') ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="recommendation_id" value="<?= (int) $recommendation['id'] ?>">
                                            <?php if ((int) ($recommendation['recommended'] ?? 0) !== 1): ?>
                                                <input type="text" class="form-control form-control-sm mb-1" name="override_reason" minlength="10" maxlength="1000" placeholder="Override reason (min. 10 chars)" required>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-warning">Assign & Lock Cost</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= $submitted ? 'Locked after submission' : 'Unavailable' ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mt-3">
    <div class="card-header bg-dark text-white fw-bold">Submit to Vendor</div>
    <div class="card-body">
        <?php if (!empty($vendorActionState['can_submit']) && $canManageVendorWorkflow): ?>
            <form method="post" action="<?= site_url('shipments/vendor/' . $shipmentId . '/submit') ?>" onsubmit="return confirm('Submit shipment ke vendor dan kunci assignment?');">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Vendor AWB *</label><input type="text" class="form-control" name="vendor_awb_no" maxlength="100" required></div>
                    <div class="col-md-4"><label class="form-label">Master AWB</label><input type="text" class="form-control" name="master_awb_no" maxlength="100"></div>
                    <div class="col-md-4"><label class="form-label">Vendor Tracking No</label><input type="text" class="form-control" name="tracking_no_vendor" maxlength="100"></div>
                </div>
                <button type="submit" class="btn btn-dark mt-3">🚀 Submit & Lock Vendor</button>
            </form>
        <?php elseif ($submitted): ?>
            <div class="alert alert-success mb-0">Shipment sudah disubmit ke vendor. Assignment, vendor cost, dan package vendor terkunci.</div>
        <?php elseif (!$canManageVendorWorkflow): ?>
            <div class="alert alert-secondary mb-0">
                Submit ke vendor hanya dapat dilakukan oleh
                JKT-HO / Head Office.
            </div>
        <?php else: ?>
            <div class="alert alert-info mb-0"><?= esc($vendorActionState['submit_block_reason'] ?? 'Assign vendor terlebih dahulu.') ?></div>
        <?php endif; ?>
    </div>
</div>
<div
    id="add-vendor-tracking"
    class="card shadow-sm border-0 mt-3"
    style="scroll-margin-top: 100px;"
>
    <div class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <div class="fw-bold">🌍 Vendor & Multi-Country Transit Tracking</div>
            <div class="small">
                Transit hub dapat dicatat berkali-kali berdasarkan event time dan lokasi.
            </div>
        </div>
        <span class="badge bg-light text-primary align-self-md-center">
            <?= count($vendorTrackingEvents) ?> Event
        </span>
    </div>

    <div class="card-body">
        <?php if (!$submitted): ?>
            <div class="alert alert-info mb-0">
                Submit shipment ke vendor terlebih dahulu agar vendor tracking aktif.
            </div>
        <?php elseif ($vendorTrackingTerminal): ?>
            <div class="alert alert-success">
                Shipment sudah berada pada status terminal
                <strong><?= esc($currentShipmentStatus) ?></strong>.
                Event baru tidak dapat memundurkan status.
            </div>
        <?php elseif ($canManageVendorWorkflow): ?>
            <form
                method="post"
                action="<?= site_url('shipments/vendor/' . $shipmentId . '/tracking-events') ?>">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Vendor Event Status *</label>
                        <select
                            name="internal_status"
                            class="form-select"
                            required>
                            <option value="">-- Select Vendor Event --</option>
                            <?php foreach ($vendorTrackingStatusOptions as $code => $meta): ?>
                                <option
                                    value="<?= esc($code) ?>"
                                    <?= old('internal_status') === $code
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($meta['label'] ?? $code) ?>
                                    → <?= esc($meta['public_status'] ?? 'IN_TRANSIT') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Event Time *</label>
                        <input
    type="datetime-local"
    name="event_time"
    class="form-control"
    step="1"
    value="<?= esc(
        old('event_time')
        ?? date('Y-m-d\TH:i:s')
    ) ?>"
    required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Time Zone</label>
                        <input
                            type="text"
                            name="timezone_name"
                            class="form-control"
                            value="<?= esc(old('timezone_name') ?? '') ?>"
                            placeholder="Asia/Jakarta, Asia/Singapore">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Country Code</label>
                        <input
                            type="text"
                            name="country_code"
                            class="form-control text-uppercase"
                            maxlength="3"
                            value="<?= esc(old('country_code') ?? '') ?>"
                            placeholder="ID / SG / TW">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Country Name</label>
                        <input
                            type="text"
                            name="country_name"
                            class="form-control"
                            maxlength="100"
                            value="<?= esc(old('country_name') ?? '') ?>"
                            placeholder="Singapore">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">City</label>
                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            maxlength="100"
                            value="<?= esc(old('city') ?? '') ?>"
                            placeholder="Singapore">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hub / Airport</label>
                        <input
                            type="text"
                            name="hub_code"
                            class="form-control text-uppercase"
                            maxlength="20"
                            value="<?= esc(old('hub_code') ?? '') ?>"
                            placeholder="SIN / HKG / TPE">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Vendor Code</label>
                        <input
                            type="text"
                            name="vendor_status_code"
                            class="form-control text-uppercase"
                            maxlength="100"
                            value="<?= esc(old('vendor_status_code') ?? '') ?>"
                            placeholder="AF / PU">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Location Name</label>
                        <input
                            type="text"
                            name="location_name"
                            class="form-control"
                            maxlength="200"
                            value="<?= esc(old('location_name') ?? '') ?>"
                            placeholder="Singapore Transit Gateway">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Source Event ID</label>
                        <input
                            type="text"
                            name="source_event_id"
                            class="form-control"
                            maxlength="150"
                            value="<?= esc(old('source_event_id') ?? '') ?>"
                            placeholder="Optional vendor event ID">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="is_public"
                                value="1"
                                id="vendorTrackingPublic"
                                <?= old('is_public', '1') ? 'checked' : '' ?>>
                            <label
                                class="form-check-label"
                                for="vendorTrackingPublic">
                                Tampilkan ke customer
                            </label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea
                            name="tracking_notes"
                            class="form-control"
                            rows="3"
                            placeholder="Contoh: Shipment arrived at Singapore transit hub."><?= esc(old('tracking_notes') ?? '') ?></textarea>
                    </div>
                </div>

                <div class="alert alert-light border mt-3 mb-0">
                    <strong>Aturan:</strong>
                    ARRIVED/DEPARTED TRANSIT HUB dapat digunakan berulang kali.
                    Event lama tetap disimpan, tetapi tidak boleh memundurkan
                    status shipment yang lebih baru.
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    ➕ Add Vendor Tracking Event
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-secondary mt-3">
                Vendor tracking hanya dapat diperbarui oleh
                JKT-HO / Head Office. Data tracking tetap dapat dilihat.
            </div>
        <?php endif; ?>
    </div>

    <div class="card-body border-top p-0">
        <?php if (empty($vendorTrackingEvents)): ?>
            <div class="text-center text-muted py-4">
                Belum ada vendor tracking event.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Source</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendorTrackingEvents as $event): ?>
                            <tr>
                                <td style="min-width:165px">
                                    <?= esc($event['event_time'] ?? '-') ?>
                                    <?php if (!empty($event['timezone_name'])): ?>
                                        <div class="small text-muted">
                                            <?= esc($event['timezone_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width:230px">
                                    <span class="badge bg-primary">
                                        <?= esc($event['internal_status'] ?? '-') ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        Public:
                                        <?= esc($event['public_status'] ?? '-') ?>
                                    </div>
                                    <?php if (!empty($event['vendor_status_code'])): ?>
                                        <div class="small">
                                            Vendor code:
                                            <?= esc($event['vendor_status_code']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width:220px">
                                    <div class="fw-semibold">
                                        <?= esc($event['location_name'] ?? '-') ?>
                                    </div>
                                    <div class="small text-muted">
                                        <?= esc(
                                            implode(
                                                ' · ',
                                                array_filter([
                                                    $event['hub_code'] ?? null,
                                                    $event['city'] ?? null,
                                                    $event['country_name']
                                                        ?? $event['country_code']
                                                        ?? null,
                                                ])
                                            )
                                        ) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= esc($event['event_source'] ?? '-') ?>
                                    <?php if (!empty($event['source_event_id'])): ?>
                                        <div class="small text-muted">
                                            <?= esc($event['source_event_id']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width:260px">
                                    <?= nl2br(esc($event['tracking_notes'] ?? '-')) ?>
                                    <div class="small text-muted mt-1">
                                        Sequence #<?= (int) ($event['event_sequence'] ?? 0) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
