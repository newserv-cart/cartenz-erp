<?php
$actionShipment = $actionShipment ?? $shipment ?? [];
$actionState = $actionState
    ?? $actionShipment['_actions']
    ?? [];

$shipmentId = (int) ($actionShipment['id'] ?? 0);
$awbNo = (string) ($actionShipment['awb_no'] ?? '');
?>

<div class="d-flex flex-wrap gap-2">
   <?php
$actionShipment = $actionShipment ?? $shipment ?? [];
$actionState = $actionState
    ?? $actionShipment['_actions']
    ?? [];

$shipmentId = (int) ($actionShipment['id'] ?? 0);
$awbNo = (string) ($actionShipment['awb_no'] ?? '');
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
            data-bs-toggle="modal"
            data-bs-target="#cancelShipmentModal"
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
</div>
