<?php
$trackingRows = $tracking ?? [];
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">📍 Tracking Timeline</h5>
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
            <div class="position-relative">
                <?php foreach ($trackingRows as $index => $row): ?>
                    <?php
                    $status = strtoupper((string) ($row['tracking_status'] ?? '-'));

                    $badgeClass = match ($status) {
                        'DELIVERED', 'CLOSED' => 'success',
                        'CANCELLED', 'FAILED', 'REJECTED' => 'danger',
                        'IN_TRANSIT', 'DISPATCHED', 'OUT_FOR_DELIVERY' => 'warning',
                        default => 'primary',
                    };
                    ?>

                    <div class="d-flex gap-3 pb-4 <?= $index < count($trackingRows) - 1 ? 'border-bottom mb-4' : '' ?>">
                        <div>
                            <span class="badge rounded-pill bg-<?= esc($badgeClass) ?>">
                                <?= esc($status) ?>
                            </span>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-1">
                                <div class="fw-bold">
                                    <?= esc($row['tracking_location'] ?? 'Cartenz System') ?>
                                </div>

                                <div class="text-muted small">
                                    <?= esc($row['event_time'] ?? $row['created_at'] ?? '-') ?>
                                </div>
                            </div>

                            <div class="mt-2">
                                <?= nl2br(esc($row['tracking_notes'] ?? '-')) ?>
                            </div>

                            <?php if (!empty($row['tracking_code'])): ?>
                                <div class="small text-muted mt-2">
                                    Tracking Code:
                                    <?= esc($row['tracking_code']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
