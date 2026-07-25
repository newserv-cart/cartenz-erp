<?php

namespace App\Services;

use App\Models\ShipmentDeletionAuditModel;
use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentPackageModel;
use App\Models\ShipmentTrackingModel;
use CodeIgniter\Database\BaseConnection;

class ShipmentLifecycleService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getActionState(array $shipment): array
    {
        $status = strtoupper(
            trim((string) ($shipment['shipment_status'] ?? ''))
        );

        $invoiceLinked = $this->hasInvoiceLink($shipment);
        $manifestLinked = $this->hasManifestLink(
            (int) ($shipment['id'] ?? 0)
        );
        $vendorSubmitted = $this->hasVendorSubmission($shipment);

        $terminal = in_array(
            $status,
            [
                'DELIVERED',
                'RETURN_TO_SENDER',
                'CLOSED',
                'CANCELLED',
            ],
            true
        );

        $editableStatuses = [
            'DRAFT',
            'RECEIVED_AT_ORIGIN_BRANCH',
            'RECEIVED_AT_MAIN_WAREHOUSE',
        ];

        $isEditableStatus = in_array(
            $status,
            $editableStatuses,
            true
        );

        $canEdit = $isEditableStatus
            && !$invoiceLinked
            && !$vendorSubmitted;

        $canCancel = !$terminal
            && !$invoiceLinked;

        $canDelete = $status === 'DRAFT'
            && !$invoiceLinked
            && !$manifestLinked
            && !$vendorSubmitted;

        return [
            'can_edit'             => $canEdit,
            'can_cancel'           => $canCancel,
            'can_delete'           => $canDelete,
            'edit_block_reason'    => $this->editBlockReason(
                $isEditableStatus,
                $invoiceLinked,
                $vendorSubmitted,
                $status
            ),
            'cancel_block_reason'  => $this->cancelBlockReason(
                $terminal,
                $invoiceLinked,
                $status
            ),
            'delete_block_reason'  => $this->deleteBlockReason(
                $status,
                $invoiceLinked,
                $manifestLinked,
                $vendorSubmitted
            ),
            'invoice_linked'       => $invoiceLinked,
            'manifest_linked'      => $manifestLinked,
            'vendor_submitted'     => $vendorSubmitted,
        ];
    }

    public function cancel(
        int $shipmentId,
        string $reason,
        ?int $userId = null
    ): array {
        $reason = $this->validateReason(
            $reason,
            'Alasan pembatalan'
        );

        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();
        $historyModel  = new ShipmentHistoryModel();

        $this->db->transBegin();

        try {
            $shipment = $shipmentModel->find($shipmentId);

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan.'
                );
            }

            $state = $this->getActionState($shipment);

            if (!$state['can_cancel']) {
                throw new \RuntimeException(
                    $state['cancel_block_reason']
                    ?: 'Shipment tidak dapat dibatalkan.'
                );
            }

            $previousStatus = strtoupper(
                trim(
                    (string) (
                        $shipment['shipment_status']
                        ?? 'DRAFT'
                    )
                )
            );

            $now = date('Y-m-d H:i:s');

            $updated = $shipmentModel->update(
                $shipmentId,
                [
                    'shipment_status'       => 'CANCELLED',
                    'public_tracking_status'=> 'CANCELLED',
                    'cancel_reason'         => $reason,
                    'cancelled_at'          => $now,
                    'cancelled_by'          => $userId,
                    'cancelled_from_status' => $previousStatus,
                ]
            );

            if ($updated === false) {
                throw new \RuntimeException(
                    'Gagal memperbarui status pembatalan: ' .
                    implode(', ', $shipmentModel->errors())
                );
            }

            if ($trackingModel->insert([
                'shipment_id'       => $shipmentId,
                'tracking_code'     => (string) $shipment['awb_no'],
                'tracking_status'   => 'CANCELLED',
                'public_status'     => 'CANCELLED',
                'tracking_location' => 'Cartenz System',
                'tracking_notes'    => $reason,
                'event_time'        => $now,
                'created_by'        => $userId,
                'created_at'        => $now,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking pembatalan: ' .
                    implode(', ', $trackingModel->errors())
                );
            }

            $changedFields = json_encode(
                [
                    'shipment_status' => [
                        'from' => $previousStatus,
                        'to'   => 'CANCELLED',
                    ],
                    'public_tracking_status' => [
                        'from' => $shipment[
                            'public_tracking_status'
                        ] ?? null,
                        'to'   => 'CANCELLED',
                    ],
                    'cancel_reason' => [
                        'from' => null,
                        'to'   => $reason,
                    ],
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );

            if ($changedFields === false) {
                throw new \RuntimeException(
                    'Gagal membentuk audit pembatalan.'
                );
            }

            if ($historyModel->insert([
                'shipment_id'   => $shipmentId,
                'event_type'    => 'CANCELLED',
                'event_title'   => 'Shipment Cancelled',
                'description'   =>
                    'Shipment dibatalkan dari status ' .
                    $previousStatus .
                    '. Alasan: ' .
                    $reason,
                'changed_fields'=> $changedFields,
                'created_by'    => $userId,
                'created_at'    => $now,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan history pembatalan: ' .
                    implode(', ', $historyModel->errors())
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi pembatalan shipment gagal.'
                );
            }

            $this->db->transCommit();

            return [
                'shipment_id'     => $shipmentId,
                'awb_no'          => (string) $shipment['awb_no'],
                'previous_status' => $previousStatus,
                'status'          => 'CANCELLED',
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function deletePermanently(
        int $shipmentId,
        string $reason,
        string $confirmAwb,
        ?int $userId = null
    ): array {
        $reason = $this->validateReason(
            $reason,
            'Alasan penghapusan'
        );

        $confirmAwb = strtoupper(trim($confirmAwb));

        $shipmentModel = new ShipmentModel();
        $auditModel    = new ShipmentDeletionAuditModel();

        $this->db->transBegin();

        try {
            $shipment = $shipmentModel->find($shipmentId);

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan.'
                );
            }

            $awbNo = strtoupper(
                (string) ($shipment['awb_no'] ?? '')
            );

            if ($confirmAwb !== $awbNo) {
                throw new \RuntimeException(
                    'Konfirmasi AWB tidak cocok. Ketik ' .
                    $awbNo .
                    ' untuk menghapus permanen.'
                );
            }

            $state = $this->getActionState($shipment);

            if (!$state['can_delete']) {
                throw new \RuntimeException(
                    $state['delete_block_reason']
                    ?: 'Shipment tidak dapat dihapus permanen.'
                );
            }

            $snapshot = $this->buildDeletionSnapshot(
                $shipment
            );

            $snapshotJson = json_encode(
                $snapshot,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
            );

            if ($snapshotJson === false) {
                throw new \RuntimeException(
                    'Gagal membentuk snapshot audit penghapusan.'
                );
            }

            $now = date('Y-m-d H:i:s');

            if ($auditModel->insert([
                'shipment_id_original' => $shipmentId,
                'awb_no'               => $awbNo,
                'customer_id'          =>
                    $shipment['customer_id'] ?? null,
                'shipment_status'      =>
                    $shipment['shipment_status'] ?? null,
                'delete_reason'        => $reason,
                'deleted_by'           => $userId,
                'deleted_at'           => $now,
                'snapshot_json'        => $snapshotJson,
                'created_at'           => $now,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan audit penghapusan: ' .
                    implode(', ', $auditModel->errors())
                );
            }

            $packageRows = $this->db
                ->table('shipment_packages')
                ->select('id')
                ->where('shipment_id', $shipmentId)
                ->get()
                ->getResultArray();

            $packageIds = array_map(
                static fn (array $row): int =>
                    (int) $row['id'],
                $packageRows
            );

            if (
                $packageIds !== []
                && $this->db->tableExists(
                    'shipment_package_items'
                )
            ) {
                $this->db
                    ->table('shipment_package_items')
                    ->whereIn(
                        'shipment_package_id',
                        $packageIds
                    )
                    ->delete();
            }

            $this->deleteShipmentRows(
                'shipment_packages',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_cost_details',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_tracking',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_histories',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_vendor_recommendations',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_charges',
                $shipmentId
            );
            $this->deleteShipmentRows(
                'shipment_items',
                $shipmentId
            );

            if ($shipmentModel->delete($shipmentId) === false) {
                throw new \RuntimeException(
                    'Gagal menghapus shipment: ' .
                    implode(', ', $shipmentModel->errors())
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penghapusan shipment gagal.'
                );
            }

            $this->db->transCommit();

            return [
                'shipment_id' => $shipmentId,
                'awb_no'      => $awbNo,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function hasInvoiceLink(array $shipment): bool
    {
        if (
            (int) ($shipment['is_invoiced'] ?? 0) === 1
            || !empty($shipment['invoice_id'])
        ) {
            return true;
        }

        return $this->shipmentRowExists(
            'invoice_items',
            (int) ($shipment['id'] ?? 0)
        );
    }

    private function hasManifestLink(int $shipmentId): bool
    {
        return $this->shipmentRowExists(
            'manifest_details',
            $shipmentId
        );
    }

    private function hasVendorSubmission(array $shipment): bool
    {
        if (
            !empty($shipment['vendor_awb_no'])
            || !empty($shipment['tracking_no_vendor'])
        ) {
            return true;
        }

        if (
            !$this->db->tableExists('shipment_packages')
            || (int) ($shipment['id'] ?? 0) <= 0
        ) {
            return false;
        }

        return $this->db
            ->table('shipment_packages')
            ->groupStart()
                ->where('is_submitted_to_vendor', 1)
                ->orWhere(
                    'vendor_awb_no IS NOT NULL',
                    null,
                    false
                )
            ->groupEnd()
            ->where(
                'shipment_id',
                (int) $shipment['id']
            )
            ->countAllResults() > 0;
    }

    private function shipmentRowExists(
        string $table,
        int $shipmentId
    ): bool {
        if (
            $shipmentId <= 0
            || !$this->db->tableExists($table)
        ) {
            return false;
        }

        $fields = $this->db->getFieldNames($table);

        if (!in_array('shipment_id', $fields, true)) {
            return false;
        }

        return $this->db
            ->table($table)
            ->where('shipment_id', $shipmentId)
            ->countAllResults() > 0;
    }

    private function buildDeletionSnapshot(
        array $shipment
    ): array {
        $shipmentId = (int) $shipment['id'];

        $packages = $this->fetchShipmentRows(
            'shipment_packages',
            $shipmentId
        );

        $packageIds = array_map(
            static fn (array $row): int =>
                (int) $row['id'],
            $packages
        );

        $items = [];

        if (
            $packageIds !== []
            && $this->db->tableExists(
                'shipment_package_items'
            )
        ) {
            $items = $this->db
                ->table('shipment_package_items')
                ->whereIn(
                    'shipment_package_id',
                    $packageIds
                )
                ->get()
                ->getResultArray();
        }

        return [
            'shipment' => $shipment,
            'packages' => $packages,
            'package_items' => $items,
            'cost_details' => $this->fetchShipmentRows(
                'shipment_cost_details',
                $shipmentId
            ),
            'tracking' => $this->fetchShipmentRows(
                'shipment_tracking',
                $shipmentId
            ),
            'histories' => $this->fetchShipmentRows(
                'shipment_histories',
                $shipmentId
            ),
            'vendor_recommendations' =>
                $this->fetchShipmentRows(
                    'shipment_vendor_recommendations',
                    $shipmentId
                ),
            'shipment_charges' =>
                $this->fetchShipmentRows(
                    'shipment_charges',
                    $shipmentId
                ),
            'legacy_items' =>
                $this->fetchShipmentRows(
                    'shipment_items',
                    $shipmentId
                ),
        ];
    }

    private function fetchShipmentRows(
        string $table,
        int $shipmentId
    ): array {
        if (!$this->db->tableExists($table)) {
            return [];
        }

        $fields = $this->db->getFieldNames($table);

        if (!in_array('shipment_id', $fields, true)) {
            return [];
        }

        return $this->db
            ->table($table)
            ->where('shipment_id', $shipmentId)
            ->get()
            ->getResultArray();
    }

    private function deleteShipmentRows(
        string $table,
        int $shipmentId
    ): void {
        if (!$this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);

        if (!in_array('shipment_id', $fields, true)) {
            return;
        }

        $this->db
            ->table($table)
            ->where('shipment_id', $shipmentId)
            ->delete();
    }

    private function validateReason(
        string $reason,
        string $label
    ): string {
        $reason = trim(strip_tags($reason));

        if (mb_strlen($reason) < 10) {
            throw new \RuntimeException(
                $label .
                ' wajib diisi minimal 10 karakter.'
            );
        }

        if (mb_strlen($reason) > 1000) {
            throw new \RuntimeException(
                $label .
                ' maksimal 1000 karakter.'
            );
        }

        return $reason;
    }

    private function editBlockReason(
        bool $isEditableStatus,
        bool $invoiceLinked,
        bool $vendorSubmitted,
        string $status
    ): string {
        if (!$isEditableStatus) {
            return 'Shipment status ' .
                $status .
                ' tidak dapat diedit.';
        }

        if ($invoiceLinked) {
            return 'Shipment sudah terhubung ke invoice.';
        }

        if ($vendorSubmitted) {
            return 'Shipment sudah dikirim ke vendor.';
        }

        return '';
    }

    private function cancelBlockReason(
        bool $terminal,
        bool $invoiceLinked,
        string $status
    ): string {
        if ($terminal) {
            return 'Shipment status ' .
                $status .
                ' tidak dapat dibatalkan.';
        }

        if ($invoiceLinked) {
            return 'Shipment sudah terhubung ke invoice. ' .
                'Gunakan proses credit note/void invoice terlebih dahulu.';
        }

        return '';
    }

    private function deleteBlockReason(
        string $status,
        bool $invoiceLinked,
        bool $manifestLinked,
        bool $vendorSubmitted
    ): string {
        if ($status !== 'DRAFT') {
            return 'Hapus permanen hanya diperbolehkan untuk status DRAFT.';
        }

        if ($invoiceLinked) {
            return 'Shipment sudah terhubung ke invoice.';
        }

        if ($manifestLinked) {
            return 'Shipment sudah masuk manifest.';
        }

        if ($vendorSubmitted) {
            return 'Shipment sudah dikirim ke vendor.';
        }

        return '';
    }
}
