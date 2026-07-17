<?php

namespace App\Services;

use App\Models\PickupRequestModel;
use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;
use CodeIgniter\Database\BaseConnection;

class BranchScanService
{
    public const SCAN_PICKUP  = 'PICKUP';
    public const SCAN_DROPOFF = 'DROPOFF';

    private const TERMINAL_STATUSES = [
        'CANCELLED',
        'DELIVERED',
        'RETURN_TO_SENDER',
        'CLOSED',
    ];

    protected BaseConnection $db;
    protected BranchOperationalContextService $context;
    protected ShipmentModel $shipmentModel;
    protected ShipmentTrackingModel $trackingModel;
    protected ShipmentHistoryModel $historyModel;

    public function __construct()
    {
        $this->db            = db_connect();
        $this->context       = new BranchOperationalContextService();
        $this->shipmentModel = new ShipmentModel();
        $this->trackingModel = new ShipmentTrackingModel();
        $this->historyModel  = new ShipmentHistoryModel();
    }

    public function scanByAwb(
        string $awbNo,
        string $scanType,
        int $actorId,
        string $notes = ''
    ): array {
        $awbNo = strtoupper(trim($awbNo));

        if ($awbNo === '') {
            throw new \RuntimeException(
                'Nomor AWB wajib diisi.'
            );
        }

        $scanType = strtoupper(trim($scanType));

        if (
            !in_array(
                $scanType,
                [self::SCAN_PICKUP, self::SCAN_DROPOFF],
                true
            )
        ) {
            throw new \RuntimeException(
                'Jenis scan tidak valid.'
            );
        }

        $shipment = $this->shipmentModel
            ->where('awb_no', $awbNo)
            ->first();

        if (!$shipment) {
            throw new \RuntimeException(
                'Shipment dengan AWB ' . $awbNo .
                ' tidak ditemukan.'
            );
        }

        return $scanType === self::SCAN_PICKUP
            ? $this->scanPickup(
                (int) $shipment['id'],
                $actorId,
                $notes
            )
            : $this->scanDropoff(
                (int) $shipment['id'],
                $actorId,
                $notes
            );
    }

    public function scanPickup(
        int $shipmentId,
        int $actorId,
        string $notes = ''
    ): array {
        $profile = $this->context
            ->requireProfile($actorId);

        if (
            (int) ($profile['can_scan_pickup'] ?? 0)
            !== 1
        ) {
            throw new \RuntimeException(
                'User tidak mempunyai izin scan pickup.'
            );
        }

        return $this->applyScan(
            $shipmentId,
            $actorId,
            $profile,
            self::SCAN_PICKUP,
            $notes
        );
    }

    public function scanDropoff(
        int $shipmentId,
        int $actorId,
        string $notes = ''
    ): array {
        $profile = $this->context
            ->requireProfile($actorId);

        if (
            (int) ($profile['can_scan_dropoff'] ?? 0)
            !== 1
        ) {
            throw new \RuntimeException(
                'User tidak mempunyai izin scan drop-off.'
            );
        }

        return $this->applyScan(
            $shipmentId,
            $actorId,
            $profile,
            self::SCAN_DROPOFF,
            $notes
        );
    }

    private function applyScan(
        int $shipmentId,
        int $actorId,
        array $profile,
        string $scanType,
        string $notes
    ): array {
        $this->db->transBegin();

        try {
            $shipment = $this->db
                ->query(
                    'SELECT * FROM shipments ' .
                    'WHERE id = ? FOR UPDATE',
                    [$shipmentId]
                )
                ->getRowArray();

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan.'
                );
            }

            $currentStatus = strtoupper(
                (string) (
                    $shipment['shipment_status']
                    ?? 'DRAFT'
                )
            );

            if (
                in_array(
                    $currentStatus,
                    self::TERMINAL_STATUSES,
                    true
                )
            ) {
                throw new \RuntimeException(
                    'Shipment berstatus terminal ' .
                    $currentStatus .
                    ' dan tidak dapat dipindai.'
                );
            }

            if (
                strtoupper(
                    (string) (
                        $shipment[
                            'vendor_submission_status'
                        ]
                        ?? 'NOT_SUBMITTED'
                    )
                ) === 'SUBMITTED'
            ) {
                throw new \RuntimeException(
                    'Shipment sudah disubmit ke vendor.'
                );
            }

            $this->assertNotInActiveManifest(
                $shipmentId
            );

            $branchId = (int) $profile['branch_id'];
            $branch   = $this->context
                ->branchById($branchId);

            if (!$branch) {
                throw new \RuntimeException(
                    'Branch user tidak valid atau tidak aktif.'
                );
            }

            $now      = date('Y-m-d H:i:s');
            $location = $this->context
                ->locationLabel($branch);

            if ($scanType === self::SCAN_PICKUP) {
                $allowed = [
                    'DRAFT',
                    'CREATED',
                ];

                if (
                    !in_array(
                        $currentStatus,
                        $allowed,
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Pickup scan hanya boleh dari status ' .
                        implode(' atau ', $allowed) .
                        '. Status saat ini: ' .
                        $currentStatus .
                        '.'
                    );
                }

                $newInternal = 'PICKED_UP';
                $newPublic   = 'PICKED_UP';
                $method      =
                    ShipmentAcceptanceService::COURIER_PICKUP;
                $description =
                    'Shipment dipindai oleh kurir saat pickup.';

                $updateData = [
                    'shipment_status' =>
                        $newInternal,
                    'public_tracking_status' =>
                        $newPublic,
                    'acceptance_method' => $method,
                    'last_scan_branch_id' => $branchId,
                    'last_scan_type' => $scanType,
                    'last_scan_at' => $now,
                    'last_scan_by' => $actorId,
                ];
            } else {
                $allowed = [
                    'DRAFT',
                    'CREATED',
                    'PICKED_UP',
                    'ARRIVED_HUB',
                ];

                if (
                    !in_array(
                        $currentStatus,
                        $allowed,
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Drop-off scan belum boleh dari status ' .
                        $currentStatus .
                        '.'
                    );
                }

                $isMain = $this->context
                    ->isMainBranch($branch);

                $newInternal = $isMain
                    ? 'RECEIVED_AT_MAIN_WAREHOUSE'
                    : 'RECEIVED_AT_ORIGIN_BRANCH';

                $newPublic = $isMain
                    ? 'IN_TRANSIT'
                    : 'PICKED_UP';

                $method = $isMain
                    ? ShipmentAcceptanceService::
                        CUSTOMER_DROP_OFF_MAIN
                    : ShipmentAcceptanceService::
                        CUSTOMER_DROP_OFF_BRANCH;

                $description = $isMain
                    ? 'Shipment diterima melalui scan di main warehouse.'
                    : 'Shipment diterima melalui scan di origin branch.';

                $updateData = [
                    'shipment_status' =>
                        $newInternal,
                    'public_tracking_status' =>
                        $newPublic,
                    'acceptance_method' => $method,
                    'acceptance_branch_id' => $branchId,
                    'receiving_branch_id' => $branchId,
                    'current_branch_id' => $branchId,
                    'receiving_location' => $location,
                    'received_at' => $now,
                    'received_by' => $actorId,
                    'last_scan_branch_id' => $branchId,
                    'last_scan_type' => $scanType,
                    'last_scan_at' => $now,
                    'last_scan_by' => $actorId,
                ];
            }

            if (
                !$this->shipmentModel->update(
                    $shipmentId,
                    $updateData
                )
            ) {
                throw new \RuntimeException(
                    'Status shipment gagal diperbarui.'
                );
            }

            $notes = trim($notes);

            if ($notes !== '') {
                $description .= ' ' . $notes;
            }

            if (
                !$this->trackingModel->insert([
                    'shipment_id' => $shipmentId,
                    'tracking_code' =>
                        $shipment['awb_no'],
                    'tracking_status' =>
                        $newInternal,
                    'public_status' => $newPublic,
                    'tracking_location' => $location,
                    'tracking_notes' => $description,
                    'event_time' => $now,
                    'created_by' => $actorId,
                    'created_at' => $now,
                    'branch_id' => $branchId,
                    'event_type' =>
                        $scanType === self::SCAN_PICKUP
                            ? 'COURIER_PICKUP_SCAN'
                            : 'BRANCH_DROPOFF_SCAN',
                    'scan_type' => $scanType,
                    'source_reference' =>
                        $shipment['awb_no'],
                ])
            ) {
                throw new \RuntimeException(
                    'Tracking scan gagal disimpan.'
                );
            }

            if (
                !$this->historyModel->insert([
                    'shipment_id' => $shipmentId,
                    'event_type' =>
                        $scanType === self::SCAN_PICKUP
                            ? 'COURIER_PICKUP_SCAN'
                            : 'BRANCH_DROPOFF_SCAN',
                    'event_title' =>
                        $scanType === self::SCAN_PICKUP
                            ? 'Courier Pickup Scan'
                            : 'Branch Drop-off Scan',
                    'description' => $description,
                    'changed_fields' => json_encode(
                        [
                            'previous_status' =>
                                $currentStatus,
                            'new_status' => $newInternal,
                            'branch_id' => $branchId,
                            'branch_code' =>
                                $branch['branch_code']
                                ?? null,
                            'scan_type' => $scanType,
                        ],
                        JSON_UNESCAPED_UNICODE
                        | JSON_UNESCAPED_SLASHES
                    ),
                    'created_by' => $actorId,
                    'created_at' => $now,
                ])
            ) {
                throw new \RuntimeException(
                    'History scan gagal disimpan.'
                );
            }

            if ($scanType === self::SCAN_PICKUP) {
                $this->completePickupRequest(
                    $shipmentId,
                    $actorId,
                    $now,
                    $profile
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi scan gagal.'
                );
            }

            $this->db->transCommit();

            return [
                'shipment_id' => $shipmentId,
                'awb_no' => $shipment['awb_no'],
                'previous_status' => $currentStatus,
                'new_status' => $newInternal,
                'public_status' => $newPublic,
                'branch_id' => $branchId,
                'branch_label' =>
                    $this->context->branchLabel($branch),
                'scan_type' => $scanType,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function assertNotInActiveManifest(
        int $shipmentId
    ): void {
        $active = $this->db
            ->table('manifest_details md')
            ->select([
                'm.manifest_no',
                'm.status',
            ])
            ->join(
                'manifests m',
                'm.id = md.manifest_id',
                'inner'
            )
            ->where('md.shipment_id', $shipmentId)
            ->whereIn(
                'm.status',
                ['OPEN', 'DISPATCHED', 'RECEIVED']
            )
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($active) {
            throw new \RuntimeException(
                'Shipment berada dalam manifest aktif ' .
                $active['manifest_no'] .
                ' (' . $active['status'] . ').'
            );
        }
    }

    private function completePickupRequest(
        int $shipmentId,
        int $actorId,
        string $now,
        array $profile
    ): void {
        if (!$this->db->tableExists('pickup_requests')) {
            return;
        }

        $pickup = (new PickupRequestModel())
            ->where('shipment_id', $shipmentId)
            ->whereIn(
                'pickup_status',
                ['REQUESTED', 'ASSIGNED']
            )
            ->orderBy('id', 'DESC')
            ->first();

        if (!$pickup) {
            return;
        }

        (new PickupRequestModel())->update(
            (int) $pickup['id'],
            [
                'pickup_status' => 'COMPLETED',
                'driver_name' =>
                    $profile['username']
                    ?? $profile['agent_name']
                    ?? 'Courier',
                'assigned_user_id' => $actorId,
                'picked_up_at' => $now,
                'picked_up_by' => $actorId,
            ]
        );
    }
}
