<?php
$trackingRows = $tracking ?? [];
$statusRows   = $statuses ?? [];

$currentInternalStatus = strtoupper(
    (string) ($shipment['shipment_status'] ?? 'DRAFT')
);

$currentPublicStatus = strtoupper(
    (string) ($shipment['public_tracking_status'] ?? 'CREATED')
);
?>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">⚙️ Update Internal Status</h5>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <div class="small text-muted">Current Internal Status</div>
                    <span class="badge bg-primary">
                        <?= esc($currentInternalStatus) ?>
                    </span>
                </div>

                <div class="mb-3">
                    <div class="small text-muted">Current Public Status</div>
                    <span class="badge bg-success">
                        <?= esc($currentPublicStatus) ?>
                    </span>
                </div>

                <form
                    method="post"
                    action="<?= site_url('shipments/tracking/add/' . (int) $shipment['id']) ?>">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Next Internal Status</label>
                        <select
                            name="tracking_status"
                            class="form-select"
                            required>
                            <option value="">-- Select Status --</option>

                            <?php foreach ($statusRows as $status): ?>
                                <?php
                                $statusCode = strtoupper(
                                    (string) ($status['status_code'] ?? '')
                                );

                                $publicCode = strtoupper(
                                    (string) (
                                        $status['public_status_code']
                                        ?? $statusCode
                                    )
                                );
                                ?>
                                <option
                                    value="<?= esc($statusCode) ?>"
                                    <?= old('tracking_status') === $statusCode ? 'selected' : '' ?>>
                                    <?= esc($status['status_name'] ?? $statusCode) ?>
                                    → Public: <?= esc($publicCode) ?>
                                    <?= (int) ($status['requires_notes'] ?? 0) === 1
                                        ? ' (notes required)'
                                        : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Location</label>
                        <input
                            type="text"
                            name="tracking_location"
                            class="form-control"
                            value="<?= esc(old('tracking_location') ?? '') ?>"
                            placeholder="Example: Cartenz Main Warehouse"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes</label>
                        <textarea
                            name="tracking_notes"
                            class="form-control"
                            rows="3"
                            placeholder="Required for ON_HOLD and RETURN_TO_SENDER"><?= esc(old('tracking_notes') ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Save Tracking Status
                    </button>
                </form>

                <div class="small text-muted mt-3">
                    Status internal dipakai oleh operasional.
                    Status publik otomatis dipetakan untuk customer.
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📍 Internal Tracking Timeline</h5>
                <span class="badge bg-light text-primary">
                    <?= count($trackingRows) ?> Event
                </span>
            </div>

            <div class="card-body">
                <?php if (empty($trackingRows)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada riwayat tracking untuk shipment ini.
                    </div>
                <?php else: ?>
                    <?php foreach ($trackingRows as $index => $row): ?>
                        <?php
                        $internalStatus = strtoupper(
                            (string) ($row['tracking_status'] ?? '-')
                        );

                        $publicStatus = strtoupper(
                            (string) (
                                $row['public_status']
                                ?? $internalStatus
                            )
                        );

                        $badgeClass = match ($internalStatus) {
                            'DELIVERED' => 'success',
                            'RETURN_TO_SENDER' => 'danger',
                            'ON_HOLD' => 'warning',
                            'OUT_FOR_DELIVERY' => 'dark',
                            default => 'primary',
                        };
                        ?>

                        <div class="pb-4 <?= $index < count($trackingRows) - 1 ? 'border-bottom mb-4' : '' ?>">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-<?= esc($badgeClass) ?>">
                                        Internal: <?= esc($internalStatus) ?>
                                    </span>

                                    <span class="badge bg-success">
                                        Public: <?= esc($publicStatus) ?>
                                    </span>
                                </div>

                                <div class="text-muted small">
                                    <?= esc($row['event_time'] ?? $row['created_at'] ?? '-') ?>
                                </div>
                            </div>

                            <div class="fw-bold mt-3">
                                <?= esc($row['tracking_location'] ?? 'Cartenz System') ?>
                            </div>

                            <div class="mt-1">
                                <?= nl2br(esc($row['tracking_notes'] ?? '-')) ?>
                            </div>

                            <div class="small text-muted mt-2">
                                Updated by:
                                <?= !empty($row['created_by'])
                                    ? 'User #' . esc((string) $row['created_by'])
                                    : 'System' ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
