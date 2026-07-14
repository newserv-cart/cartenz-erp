<?php
$historyRows = $shipmentHistories ?? [];
$createdAt = $shipment['created_at'] ?? null;
$updatedAt = $shipment['updated_at'] ?? null;
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">📝 Shipment History</h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="190">Time</th>
                        <th width="180">Event</th>
                        <th>Description</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($historyRows as $row): ?>
                        <?php
                        $eventType = strtoupper(
                            (string) (
                                $row['event_type']
                                ?? 'UPDATED'
                            )
                        );

                        $historyColor = match ($eventType) {
                            'CANCELLED', 'DELETED' => 'danger',
                            'CREATED' => 'success',
                            default => 'warning',
                        };
                        ?>
                        <tr>
                            <td>
                                <?= esc(
                                    $row['created_at'] ?? '-'
                                ) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= esc($historyColor) ?> <?= $historyColor === 'warning' ? 'text-dark' : '' ?>">
                                    <?= esc($eventType) ?>
                                </span>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?= esc(
                                        $row['event_title']
                                        ?? 'Shipment Updated'
                                    ) ?>
                                </div>

                                <div>
                                    <?= esc(
                                        $row['description']
                                        ?? '-'
                                    ) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($createdAt): ?>
                        <tr>
                            <td><?= esc($createdAt) ?></td>

                            <td>
                                <span class="badge bg-success">
                                    CREATED
                                </span>
                            </td>

                            <td>
                                Shipment
                                <?= esc(
                                    $shipment['awb_no'] ?? '-'
                                ) ?>
                                dibuat.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach (($tracking ?? []) as $row): ?>
                        <?php
                        $trackingStatus = strtoupper(
                            (string) (
                                $row['tracking_status']
                                ?? 'TRACKING'
                            )
                        );

                        $trackingColor = in_array(
                            $trackingStatus,
                            ['CANCELLED', 'RETURN_TO_SENDER'],
                            true
                        )
                            ? 'danger'
                            : 'primary';
                        ?>
                        <tr>
                            <td>
                                <?= esc(
                                    $row['event_time']
                                    ?? $row['created_at']
                                    ?? '-'
                                ) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= esc($trackingColor) ?>">
                                    <?= esc($trackingStatus) ?>
                                </span>
                            </td>

                            <td>
                                <?= esc(
                                    $row['tracking_notes'] ?? '-'
                                ) ?>

                                <?php if (
                                    !empty(
                                        $row['tracking_location']
                                    )
                                ): ?>
                                    <div class="small text-muted">
                                        <?= esc(
                                            $row[
                                                'tracking_location'
                                            ]
                                        ) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (
                        $historyRows === []
                        && !$createdAt
                        && empty($tracking)
                    ): ?>
                        <tr>
                            <td
                                colspan="3"
                                class="text-center text-muted">
                                Belum ada history shipment.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
