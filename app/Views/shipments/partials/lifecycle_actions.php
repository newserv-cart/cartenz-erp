<?php
$actionShipment = $actionShipment ?? $shipment ?? [];
$actionState = $actionState
    ?? $actionShipment['_actions']
    ?? [];

$shipmentId = (int) ($actionShipment['id'] ?? 0);
$awbNo = (string) ($actionShipment['awb_no'] ?? '');

$repriceStatus = strtoupper(
    trim((string) ($actionShipment['shipment_status'] ?? ''))
);

$repriceStatuses = [
    'RECEIVED_AT_ORIGIN_BRANCH',
    'SENT_TO_MAIN_WAREHOUSE',
    'RECEIVED_AT_MAIN_WAREHOUSE',
    'ASSIGNED_TO_VENDOR',
    'HANDED_OVER_TO_VENDOR',
    'BOOKED_WITH_VENDOR',
    'IN_TRANSIT_INTERNATIONAL',
    'ARRIVED_HUB',
    'IN_TRANSIT_HUB',
    'HANDED_TO_LAST_MILE',
    'DELIVERED',
];

$canReprice = in_array($repriceStatus, $repriceStatuses, true);
?>

<div class="d-flex flex-wrap gap-2">

    <?php if ($shipmentId > 0): ?>
        <a
            href="<?= site_url('shipments/print/' . $shipmentId) ?>"
            target="_blank"
            rel="noopener"
            class="btn btn-sm btn-outline-primary">
            🖨 Print Resi
        </a>
    <?php endif; ?>

    <?php if (!empty($actionState['can_edit'])): ?>
        <a
            href="<?= site_url('shipments/edit/' . $shipmentId) ?>"
            class="btn btn-sm btn-warning">
            ✏️ Edit
        </a>
    <?php endif; ?>

    <?php if (!empty($actionState['can_cancel'])): ?>
        <button
            type="button"
            class="btn btn-sm btn-outline-danger js-cancel-shipment"
            data-shipment-id="<?= $shipmentId ?>"
            data-awb="<?= esc($awbNo) ?>"
            data-action-url="<?= site_url(
                'shipments/cancel/' . $shipmentId
            ) ?>">
            ⛔ Cancel
        </button>
    <?php endif; ?>

    <?php if (!empty($actionState['can_delete'])): ?>
        <button
            type="button"
            class="btn btn-sm btn-danger js-delete-shipment"
            data-bs-toggle="modal"
            data-bs-target="#deleteShipmentModal"
            data-shipment-id="<?= $shipmentId ?>"
            data-awb="<?= esc($awbNo) ?>"
            data-action-url="<?= site_url(
                'shipments/delete/' . $shipmentId
            ) ?>">
            🗑 Delete
        </button>
    <?php endif; ?>

    <?php if ($canReprice): ?>
        <form
            method="post"
            action="<?= site_url(
                'inbound-intake/reprice/' . $shipmentId
            ) ?>"
            class="d-inline"
        >
            <?= csrf_field() ?>

            <button type="submit" class="btn btn-sm btn-warning">
                Recalculate Pricing
            </button>
        </form>
    <?php endif; ?>
</div>
