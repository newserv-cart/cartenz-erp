<?php

namespace App\Services;

use App\Models\ManifestDetailModel;
use App\Models\ManifestModel;
use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;
use CodeIgniter\Database\BaseConnection;

class ManifestWorkflowService
{
    private const ACTIVE_MANIFEST_STATUSES = [
        'OPEN',
        'DISPATCHED',
        'RECEIVED',
    ];

    private const ELIGIBLE_SHIPMENT_STATUSES = [
        'DRAFT',
        'CREATED',
        'PICKED_UP',
        'RECEIVED_AT_ORIGIN_BRANCH',
        'ARRIVED_HUB',
    ];

    private const TERMINAL_SHIPMENT_STATUSES = [
        'DELIVERED',
        'RETURN_TO_SENDER',
        'CLOSED',
        'CANCELLED',
    ];

    protected BaseConnection $db;
    protected ManifestModel $manifestModel;
    protected ManifestDetailModel $detailModel;
    protected ShipmentModel $shipmentModel;
    protected ShipmentTrackingModel $trackingModel;
    protected ShipmentHistoryModel $historyModel;
    protected BranchOperationalContextService $branchContext;

    public function __construct()
    {
        $this->db            = db_connect();
        $this->manifestModel = new ManifestModel();
        $this->detailModel   = new ManifestDetailModel();
        $this->shipmentModel = new ShipmentModel();
        $this->trackingModel = new ShipmentTrackingModel();
        $this->historyModel  = new ShipmentHistoryModel();
        $this->branchContext =
            new BranchOperationalContextService();
    }

    public function createManifest(
        array $input,
        int $actorId
    ): int {
        $manifestDate = trim(
            (string) (
                $input['manifest_date']
                ?? date('Y-m-d')
            )
        );

        $date = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $manifestDate
        );

        if (
            !$date
            || $date->format('Y-m-d')
                !== $manifestDate
        ) {
            throw new \RuntimeException(
                'Tanggal manifest tidak valid.'
            );
        }

        $profile = $this->branchContext
            ->requireProfile($actorId);

        $originBranchId = (int) $profile['branch_id'];

        $destinationBranchId = (int) (
            $input['destination_branch_id']
            ?? 0
        );

        if ($destinationBranchId <= 0) {
            $mainBranch = $this->branchContext
                ->mainBranch();

            $destinationBranchId = (int) (
                $mainBranch['id']
                ?? 0
            );
        }

        if ($destinationBranchId <= 0) {
            throw new \RuntimeException(
                'Destination Branch wajib dipilih.'
            );
        }

        if ($originBranchId === $destinationBranchId) {
            throw new \RuntimeException(
                'Origin Branch dan Destination Branch tidak boleh sama.'
            );
        }

        $originBranch = $this->branchContext
            ->branchById($originBranchId);

        $destinationBranch = $this->branchContext
            ->branchById($destinationBranchId);

        if (!$originBranch || !$destinationBranch) {
            throw new \RuntimeException(
                'Origin atau Destination Branch tidak valid.'
            );
        }

        $this->db->transBegin();

        try {
            $manifestId = $this->manifestModel
                ->insert([
                    'manifest_no' =>
                        $this->generateManifestNumber(
                            $originBranch
                        ),
                    'origin_branch_id' =>
                        $originBranchId,
                    'destination_branch_id' =>
                        $destinationBranchId,
                    'manifest_date' =>
                        $manifestDate,
                    'status' => 'OPEN',
                    'total_shipment' => 0,
                    'total_weight' => 0,
                    'remarks' => $this->nullableText(
                        $input['remarks'] ?? null
                    ),
                    'created_by' => $actorId,
                ], true);

            if (!$manifestId) {
                throw new \RuntimeException(
                    'Manifest gagal dibuat.'
                );
            }

            $this->commitOrFail();

            return (int) $manifestId;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function addShipments(
        int $manifestId,
        array $shipmentIds,
        int $actorId
    ): int {
        $shipmentIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $shipmentIds),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        if ($shipmentIds === []) {
            throw new \RuntimeException(
                'Pilih minimal satu shipment.'
            );
        }

        $this->db->transBegin();

        try {
            $manifest = $this->lockManifest($manifestId);
            $this->assertManifestStatus($manifest, 'OPEN');

            $added = 0;

            foreach ($shipmentIds as $shipmentId) {
                $shipment = $this->lockShipment($shipmentId);
                $this->assertShipmentEligible($shipment);
                $this->assertShipmentAtManifestOrigin(
                    $shipment,
                    $manifest
                );
                $this->assertNotInAnotherActiveManifest(
                    $shipmentId,
                    $manifestId
                );

                $duplicate = $this->detailModel
                    ->where('manifest_id', $manifestId)
                    ->where('shipment_id', $shipmentId)
                    ->first();

                if ($duplicate) {
                    throw new \RuntimeException(
                        'Shipment ' .
                        $shipment['awb_no'] .
                        ' sudah ada dalam manifest ini.'
                    );
                }

                $weight = $this->resolveShipmentWeight(
                    $shipment
                );

                $previousInternal = strtoupper(
                    (string) ($shipment['shipment_status'] ?? 'DRAFT')
                );

                $previousPublic = strtoupper(
                    (string) (
                        $shipment['public_tracking_status']
                        ?? ''
                    )
                );

                if ($previousPublic === '') {
                    $previousPublic = $this->mapPublicStatus(
                        $previousInternal
                    );
                }

                if (!$this->detailModel->insert([
                    'manifest_id'              => $manifestId,
                    'shipment_id'              => $shipmentId,
                    'awb_no'                   => $shipment['awb_no'],
                    'actual_weight'            => $weight,
                    'previous_shipment_status' => $previousInternal,
                    'previous_public_status'   => $previousPublic,
                    'added_at'                 => date('Y-m-d H:i:s'),
                    'added_by'                 => $actorId,
                ])) {
                    throw new \RuntimeException(
                        'Gagal menambahkan shipment ' .
                        $shipment['awb_no'] .
                        ' ke manifest.'
                    );
                }

                $publicStatus = $previousPublic !== ''
                    ? $previousPublic
                    : 'CREATED';

                $this->updateShipmentStatus(
                    $shipmentId,
                    'MANIFESTED',
                    $publicStatus
                );

                $this->recordTracking(
                    $shipment,
                    'MANIFESTED',
                    $publicStatus,
                    'Cartenz Manifest',
                    'Shipment ditambahkan ke manifest ' .
                    $manifest['manifest_no'] . '.',
                    $actorId,
                    !empty($manifest['origin_branch_id'])
                        ? (int) $manifest['origin_branch_id']
                        : null,
                    'MANIFEST_ADDED'
                );

                $this->recordHistory(
                    $shipment,
                    'MANIFEST_ADDED',
                    'Added to Manifest',
                    'Shipment ditambahkan ke manifest ' .
                    $manifest['manifest_no'] . '.',
                    [
                        'manifest_id'       => $manifestId,
                        'manifest_no'       => $manifest['manifest_no'],
                        'previous_status'   => $previousInternal,
                        'new_status'        => 'MANIFESTED',
                        'manifest_weight'   => $weight,
                    ],
                    $actorId
                );

                $added++;
            }

            $this->refreshTotals($manifestId);
            $this->commitOrFail();

            return $added;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function removeShipment(
        int $manifestId,
        int $shipmentId,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $manifest = $this->lockManifest($manifestId);
            $this->assertManifestStatus($manifest, 'OPEN');

            $detail = $this->db
                ->query(
                    'SELECT * FROM manifest_details ' .
                    'WHERE manifest_id = ? AND shipment_id = ? ' .
                    'FOR UPDATE',
                    [$manifestId, $shipmentId]
                )
                ->getRowArray();

            if (!$detail) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan dalam manifest.'
                );
            }

            $shipment = $this->lockShipment($shipmentId);

            if (
                strtoupper(
                    (string) ($shipment['shipment_status'] ?? '')
                ) !== 'MANIFESTED'
            ) {
                throw new \RuntimeException(
                    'Shipment tidak dapat dilepas karena statusnya ' .
                    'sudah berubah dari MANIFESTED.'
                );
            }

            $restoreInternal = strtoupper(
                (string) (
                    $detail['previous_shipment_status']
                    ?? 'DRAFT'
                )
            );

            $restorePublic = strtoupper(
                (string) (
                    $detail['previous_public_status']
                    ?? ''
                )
            );

            if ($restorePublic === '') {
                $restorePublic = $this->mapPublicStatus(
                    $restoreInternal
                );
            }

            if (
                !$this->detailModel
                    ->where('manifest_id', $manifestId)
                    ->where('shipment_id', $shipmentId)
                    ->delete()
            ) {
                throw new \RuntimeException(
                    'Shipment gagal dilepas dari manifest.'
                );
            }

            $this->updateShipmentStatus(
                $shipmentId,
                $restoreInternal,
                $restorePublic
            );

            $this->recordTracking(
                $shipment,
                $restoreInternal,
                $restorePublic,
                'Cartenz Manifest',
                'Shipment dilepas dari manifest ' .
                $manifest['manifest_no'] . '.',
                $actorId
            );

            $this->recordHistory(
                $shipment,
                'MANIFEST_REMOVED',
                'Removed from Manifest',
                'Shipment dilepas dari manifest ' .
                $manifest['manifest_no'] . '.',
                [
                    'manifest_id'     => $manifestId,
                    'manifest_no'     => $manifest['manifest_no'],
                    'restored_status' => $restoreInternal,
                ],
                $actorId
            );

            $this->refreshTotals($manifestId);
            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function dispatch(
        int $manifestId,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $manifest = $this->lockManifest($manifestId);
            $this->assertManifestStatus($manifest, 'OPEN');

            $originBranchId = (int) (
                $manifest['origin_branch_id']
                ?? 0
            );

            if ($originBranchId > 0) {
                $profile = $this->branchContext
                    ->assertActorAtBranch(
                        $actorId,
                        $originBranchId,
                        'Dispatch Manifest'
                    );
            } else {
                $profile = $this->branchContext
                    ->profileForUser($actorId);
            }

            $originBranch = $originBranchId > 0
                ? $this->branchContext
                    ->branchById($originBranchId)
                : null;

            $originLocation = $originBranch
                ? $this->branchContext
                    ->locationLabel($originBranch)
                : 'Origin Hub';

            $details = $this->lockManifestDetails(
                $manifestId
            );

            if ($details === []) {
                throw new \RuntimeException(
                    'Manifest kosong dan tidak dapat didispatch.'
                );
            }

            $this->refreshTotals($manifestId);

            foreach ($details as $detail) {
                $shipment = $this->lockShipment(
                    (int) $detail['shipment_id']
                );

                $status = strtoupper(
                    (string) (
                        $shipment['shipment_status']
                        ?? ''
                    )
                );

                if ($status !== 'MANIFESTED') {
                    throw new \RuntimeException(
                        'Shipment ' .
                        $shipment['awb_no'] .
                        ' harus berstatus MANIFESTED sebelum dispatch.'
                    );
                }

                $this->updateShipmentStatus(
                    (int) $shipment['id'],
                    'SENT_TO_MAIN_WAREHOUSE',
                    'IN_TRANSIT',
                    [
                        'current_branch_id' => null,
                        'last_scan_branch_id' =>
                            $originBranchId > 0
                                ? $originBranchId
                                : null,
                        'last_scan_type' =>
                            'MANIFEST_DISPATCH',
                        'last_scan_at' =>
                            date('Y-m-d H:i:s'),
                        'last_scan_by' => $actorId,
                    ]
                );

                $this->recordTracking(
                    $shipment,
                    'SENT_TO_MAIN_WAREHOUSE',
                    'IN_TRANSIT',
                    $originLocation,
                    'Manifest ' .
                    $manifest['manifest_no'] .
                    ' didispatch dari ' .
                    $originLocation .
                    ' menuju destination branch.',
                    $actorId,
                    $originBranchId > 0
                        ? $originBranchId
                        : null,
                    'MANIFEST_DISPATCHED'
                );

                $this->recordHistory(
                    $shipment,
                    'MANIFEST_DISPATCHED',
                    'Manifest Dispatched',
                    'Manifest ' .
                    $manifest['manifest_no'] .
                    ' didispatch dari ' .
                    $originLocation . '.',
                    [
                        'manifest_id' => $manifestId,
                        'manifest_no' =>
                            $manifest['manifest_no'],
                        'origin_branch_id' =>
                            $originBranchId > 0
                                ? $originBranchId
                                : null,
                        'destination_branch_id' =>
                            $manifest[
                                'destination_branch_id'
                            ] ?? null,
                        'new_status' =>
                            'SENT_TO_MAIN_WAREHOUSE',
                    ],
                    $actorId
                );
            }

            if (
                !$this->manifestModel->update(
                    $manifestId,
                    [
                        'status' => 'DISPATCHED',
                        'dispatched_at' =>
                            date('Y-m-d H:i:s'),
                        'dispatched_by' => $actorId,
                        'dispatched_branch_id' =>
                            $originBranchId > 0
                                ? $originBranchId
                                : (
                                    $profile['branch_id']
                                    ?? null
                                ),
                    ]
                )
            ) {
                throw new \RuntimeException(
                    'Manifest gagal didispatch.'
                );
            }

            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function receive(
        int $manifestId,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $manifest = $this->lockManifest($manifestId);
            $this->assertManifestStatus(
                $manifest,
                'DISPATCHED'
            );

            $destinationBranchId = (int) (
                $manifest['destination_branch_id']
                ?? 0
            );

            if ($destinationBranchId > 0) {
                $profile = $this->branchContext
                    ->assertActorAtBranch(
                        $actorId,
                        $destinationBranchId,
                        'Receive Manifest'
                    );
            } else {
                $profile = $this->branchContext
                    ->profileForUser($actorId);
            }

            $destinationBranch =
                $destinationBranchId > 0
                    ? $this->branchContext
                        ->branchById(
                            $destinationBranchId
                        )
                    : null;

            $destinationLocation =
                $destinationBranch
                    ? $this->branchContext
                        ->locationLabel(
                            $destinationBranch
                        )
                    : 'Main Warehouse';

            $isMainDestination =
                $destinationBranch
                    ? $this->branchContext
                        ->isMainBranch(
                            $destinationBranch
                        )
                    : true;

            $newInternal = $isMainDestination
                ? 'RECEIVED_AT_MAIN_WAREHOUSE'
                : 'RECEIVED_AT_ORIGIN_BRANCH';

            $newPublic = $isMainDestination
                ? 'IN_TRANSIT'
                : 'PICKED_UP';

            $details = $this->lockManifestDetails(
                $manifestId
            );

            if ($details === []) {
                throw new \RuntimeException(
                    'Manifest tidak mempunyai shipment.'
                );
            }

            $now = date('Y-m-d H:i:s');

            foreach ($details as $detail) {
                $shipment = $this->lockShipment(
                    (int) $detail['shipment_id']
                );

                $status = strtoupper(
                    (string) (
                        $shipment['shipment_status']
                        ?? ''
                    )
                );

                if (
                    $status !==
                    'SENT_TO_MAIN_WAREHOUSE'
                ) {
                    throw new \RuntimeException(
                        'Shipment ' .
                        $shipment['awb_no'] .
                        ' tidak berada pada status dispatch yang benar.'
                    );
                }

                $this->updateShipmentStatus(
                    (int) $shipment['id'],
                    $newInternal,
                    $newPublic,
                    [
                        'current_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : null,
                        'receiving_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : (
                                    $shipment[
                                        'receiving_branch_id'
                                    ]
                                    ?? null
                                ),
                        'receiving_location' =>
                            $destinationLocation,
                        'received_at' => $now,
                        'received_by' => $actorId,
                        'last_scan_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : null,
                        'last_scan_type' =>
                            'MANIFEST_RECEIVE',
                        'last_scan_at' => $now,
                        'last_scan_by' => $actorId,
                    ]
                );

                $this->recordTracking(
                    $shipment,
                    $newInternal,
                    $newPublic,
                    $destinationLocation,
                    'Manifest ' .
                    $manifest['manifest_no'] .
                    ' diterima di ' .
                    $destinationLocation . '.',
                    $actorId,
                    $destinationBranchId > 0
                        ? $destinationBranchId
                        : null,
                    'MANIFEST_RECEIVED'
                );

                $this->recordHistory(
                    $shipment,
                    'MANIFEST_RECEIVED',
                    'Manifest Received',
                    'Manifest ' .
                    $manifest['manifest_no'] .
                    ' diterima di ' .
                    $destinationLocation . '.',
                    [
                        'manifest_id' =>
                            $manifestId,
                        'manifest_no' =>
                            $manifest['manifest_no'],
                        'destination_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : null,
                        'new_status' => $newInternal,
                    ],
                    $actorId
                );
            }

            if (
                !$this->manifestModel->update(
                    $manifestId,
                    [
                        'status' => 'RECEIVED',
                        'received_at' => $now,
                        'received_by' => $actorId,
                        'received_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : (
                                    $profile['branch_id']
                                    ?? null
                                ),
                    ]
                )
            ) {
                throw new \RuntimeException(
                    'Manifest gagal diterima.'
                );
            }

            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function close(
        int $manifestId,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $manifest = $this->lockManifest($manifestId);
            $this->assertManifestStatus(
                $manifest,
                'RECEIVED'
            );

            $destinationBranchId = (int) (
                $manifest['destination_branch_id']
                ?? 0
            );

            if ($destinationBranchId > 0) {
                $profile = $this->branchContext
                    ->assertActorAtBranch(
                        $actorId,
                        $destinationBranchId,
                        'Close Manifest'
                    );
            } else {
                $profile = $this->branchContext
                    ->profileForUser($actorId);
            }

            $details = $this->lockManifestDetails(
                $manifestId
            );

            if ($details === []) {
                throw new \RuntimeException(
                    'Manifest kosong tidak dapat ditutup.'
                );
            }

            foreach ($details as $detail) {
                $shipment = $this->lockShipment(
                    (int) $detail['shipment_id']
                );

                $this->recordHistory(
                    $shipment,
                    'MANIFEST_CLOSED',
                    'Manifest Closed',
                    'Manifest ' .
                    $manifest['manifest_no'] .
                    ' ditutup dan dikunci.',
                    [
                        'manifest_id' => $manifestId,
                        'manifest_no' =>
                            $manifest['manifest_no'],
                        'origin_branch_id' =>
                            $manifest[
                                'origin_branch_id'
                            ] ?? null,
                        'destination_branch_id' =>
                            $manifest[
                                'destination_branch_id'
                            ] ?? null,
                    ],
                    $actorId
                );
            }

            if (
                !$this->manifestModel->update(
                    $manifestId,
                    [
                        'status' => 'CLOSED',
                        'closed_at' =>
                            date('Y-m-d H:i:s'),
                        'closed_by' => $actorId,
                        'closed_branch_id' =>
                            $destinationBranchId > 0
                                ? $destinationBranchId
                                : (
                                    $profile['branch_id']
                                    ?? null
                                ),
                    ]
                )
            ) {
                throw new \RuntimeException(
                    'Manifest gagal ditutup.'
                );
            }

            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function getEligibleShipments(
        int $manifestId
    ): array {
        $manifest = $this->manifestModel
            ->find($manifestId);

        if (!$manifest) {
            throw new \RuntimeException(
                'Manifest tidak ditemukan.'
            );
        }

        $originBranchId = (int) (
            $manifest['origin_branch_id']
            ?? 0
        );

        $usedRows = $this->db
            ->table('manifest_details md')
            ->select('md.shipment_id')
            ->join(
                'manifests m',
                'm.id = md.manifest_id',
                'inner'
            )
            ->whereIn(
                'm.status',
                self::ACTIVE_MANIFEST_STATUSES
            )
            ->where(
                'md.manifest_id !=',
                $manifestId
            )
            ->get()
            ->getResultArray();

        $usedIds = array_map(
            'intval',
            array_column(
                $usedRows,
                'shipment_id'
            )
        );

        $builder = $this->shipmentModel
            ->whereIn(
                'shipment_status',
                [
                    'RECEIVED_AT_ORIGIN_BRANCH',
                    'ARRIVED_HUB',
                ]
            )
            ->where('chargeable_weight >', 0)
            ->orderBy('id', 'DESC');

        if ($originBranchId > 0) {
            $builder->where(
                'current_branch_id',
                $originBranchId
            );
        }

        if ($usedIds !== []) {
            $builder->whereNotIn('id', $usedIds);
        }

        return $builder->findAll();
    }

    public function getManifestDetails(
        int $manifestId
    ): array {
        return $this->db
            ->table('manifest_details md')
            ->select([
                'md.*',
                's.shipment_status',
                's.public_tracking_status',
                's.customer_id',
                's.country_id',
                's.actual_weight AS shipment_actual_weight',
                's.chargeable_weight',
                's.total_colly',
                's.current_branch_id',
                's.acceptance_branch_id',
            ])
            ->join(
                'shipments s',
                's.id = md.shipment_id',
                'inner'
            )
            ->where('md.manifest_id', $manifestId)
            ->orderBy('md.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function calculateLiveTotals(
        array $details
    ): array {
        $totalWeight = 0.0;

        foreach ($details as $detail) {
            $chargeable = (float) (
                $detail['chargeable_weight']
                ?? 0
            );

            $actual = (float) (
                $detail['shipment_actual_weight']
                ?? $detail['actual_weight']
                ?? 0
            );

            $totalWeight += $chargeable > 0
                ? $chargeable
                : $actual;
        }

        return [
            'total_shipment' => count($details),
            'total_weight'   => round($totalWeight, 2),
        ];
    }

    private function assertShipmentEligible(
        array $shipment
    ): void {
        $status = strtoupper(
            (string) ($shipment['shipment_status'] ?? '')
        );

        if (
            in_array(
                $status,
                self::TERMINAL_SHIPMENT_STATUSES,
                true
            )
        ) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' berstatus terminal ' .
                $status .
                ' dan tidak dapat dimanifest.'
            );
        }

        if (
            !in_array(
                $status,
                self::ELIGIBLE_SHIPMENT_STATUSES,
                true
            )
        ) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' dengan status ' .
                $status .
                ' belum eligible untuk manifest.'
            );
        }

        if ($this->resolveShipmentWeight($shipment) <= 0) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' belum mempunyai berat valid.'
            );
        }

        $submittedPackage = $this->db
            ->table('shipment_packages')
            ->select('id')
            ->where(
                'shipment_id',
                (int) $shipment['id']
            )
            ->groupStart()
                ->where('is_submitted_to_vendor', 1)
                ->orWhere(
                    'vendor_awb_no IS NOT NULL',
                    null,
                    false
                )
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($submittedPackage) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' sudah dikirim ke vendor dan tidak dapat dimanifest.'
            );
        }
    }

    private function assertShipmentAtManifestOrigin(
        array $shipment,
        array $manifest
    ): void {
        $originBranchId = (int) (
            $manifest['origin_branch_id']
            ?? 0
        );

        if ($originBranchId <= 0) {
            return;
        }

        $shipmentStatus = strtoupper(
            (string) (
                $shipment['shipment_status']
                ?? ''
            )
        );

        if (
            !in_array(
                $shipmentStatus,
                [
                    'RECEIVED_AT_ORIGIN_BRANCH',
                    'ARRIVED_HUB',
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' belum diterima di origin branch. Status: ' .
                ($shipmentStatus !== ''
                    ? $shipmentStatus
                    : '-') .
                '.'
            );
        }

        $shipmentBranchId = (int) (
            $shipment['current_branch_id']
            ?? 0
        );

        if ($shipmentBranchId !== $originBranchId) {
            throw new \RuntimeException(
                'Shipment ' .
                $shipment['awb_no'] .
                ' berada pada branch ' .
                ($shipmentBranchId > 0
                    ? $shipmentBranchId
                    : '-') .
                ', bukan origin branch manifest ' .
                $originBranchId .
                '.'
            );
        }
    }

    private function assertNotInAnotherActiveManifest(
        int $shipmentId,
        int $manifestId
    ): void {
        $active = $this->db
            ->table('manifest_details md')
            ->select([
                'm.id',
                'm.manifest_no',
                'm.status',
            ])
            ->join(
                'manifests m',
                'm.id = md.manifest_id',
                'inner'
            )
            ->where('md.shipment_id', $shipmentId)
            ->where('md.manifest_id !=', $manifestId)
            ->whereIn(
                'm.status',
                self::ACTIVE_MANIFEST_STATUSES
            )
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($active) {
            throw new \RuntimeException(
                'Shipment sudah berada dalam manifest aktif ' .
                $active['manifest_no'] .
                ' (' . $active['status'] . ').'
            );
        }
    }

    private function refreshTotals(int $manifestId): void
    {
        $details = $this->getManifestDetails(
            $manifestId
        );

        $totalWeight = 0.0;

        foreach ($details as $detail) {
            $weight = (float) (
                ((float) ($detail['chargeable_weight'] ?? 0)) > 0
                    ? $detail['chargeable_weight']
                    : (
                        $detail['shipment_actual_weight']
                        ?? 0
                    )
            );

            $weight = round($weight, 2);

            $this->detailModel->update(
                (int) $detail['id'],
                ['actual_weight' => $weight]
            );

            $totalWeight += $weight;
        }

        if (!$this->manifestModel->update($manifestId, [
            'total_shipment' => count($details),
            'total_weight'   => round($totalWeight, 2),
        ])) {
            throw new \RuntimeException(
                'Total manifest gagal dihitung ulang.'
            );
        }
    }

    private function resolveShipmentWeight(
        array $shipment
    ): float {
        $chargeable = (float) (
            $shipment['chargeable_weight'] ?? 0
        );

        $actual = (float) (
            $shipment['actual_weight'] ?? 0
        );

        return round(
            $chargeable > 0 ? $chargeable : $actual,
            2
        );
    }

    private function updateShipmentStatus(
        int $shipmentId,
        string $internalStatus,
        string $publicStatus,
        array $extra = []
    ): void {
        $data = array_merge(
            [
                'shipment_status' =>
                    $internalStatus,
                'public_tracking_status' =>
                    $publicStatus,
            ],
            $extra
        );

        if (
            !$this->shipmentModel->update(
                $shipmentId,
                $data
            )
        ) {
            throw new \RuntimeException(
                'Status shipment gagal diperbarui.'
            );
        }
    }

    private function recordTracking(
        array $shipment,
        string $internalStatus,
        string $publicStatus,
        string $location,
        string $notes,
        int $actorId,
        ?int $branchId = null,
        ?string $eventType = null
    ): void {
        $now = date('Y-m-d H:i:s');

        if (!$this->trackingModel->insert([
            'shipment_id'       => (int) $shipment['id'],
            'branch_id'         => $branchId,
            'tracking_code'     => $shipment['awb_no'],
            'tracking_status'   => $internalStatus,
            'public_status'     => $publicStatus,
            'event_type'        => $eventType,
            'source_reference'  => $shipment['awb_no'],
            'tracking_location' => $location,
            'tracking_notes'    => $notes,
            'event_time'        => $now,
            'created_by'        => $actorId,
            'created_at'        => $now,
        ])) {
            throw new \RuntimeException(
                'Tracking shipment gagal dicatat.'
            );
        }
    }

    private function recordHistory(
        array $shipment,
        string $eventType,
        string $title,
        string $description,
        array $changedFields,
        int $actorId
    ): void {
        if (!$this->historyModel->insert([
            'shipment_id'    => (int) $shipment['id'],
            'event_type'     => $eventType,
            'event_title'    => $title,
            'description'    => $description,
            'changed_fields' => json_encode(
                $changedFields,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ),
            'created_by'     => $actorId,
            'created_at'     => date('Y-m-d H:i:s'),
        ])) {
            throw new \RuntimeException(
                'History shipment gagal dicatat.'
            );
        }
    }

    private function lockManifest(int $manifestId): array
    {
        $manifest = $this->db
            ->query(
                'SELECT * FROM manifests WHERE id = ? FOR UPDATE',
                [$manifestId]
            )
            ->getRowArray();

        if (!$manifest) {
            throw new \RuntimeException(
                'Manifest tidak ditemukan.'
            );
        }

        return $manifest;
    }

    private function lockShipment(int $shipmentId): array
    {
        $shipment = $this->db
            ->query(
                'SELECT * FROM shipments WHERE id = ? FOR UPDATE',
                [$shipmentId]
            )
            ->getRowArray();

        if (!$shipment) {
            throw new \RuntimeException(
                'Shipment tidak ditemukan.'
            );
        }

        return $shipment;
    }

    private function lockManifestDetails(
        int $manifestId
    ): array {
        return $this->db
            ->query(
                'SELECT * FROM manifest_details ' .
                'WHERE manifest_id = ? ORDER BY id ASC FOR UPDATE',
                [$manifestId]
            )
            ->getResultArray();
    }

    private function assertManifestStatus(
        array $manifest,
        string $expected
    ): void {
        $status = strtoupper(
            (string) ($manifest['status'] ?? '')
        );

        if ($status !== $expected) {
            throw new \RuntimeException(
                'Manifest harus berstatus ' .
                $expected .
                '. Status saat ini: ' .
                ($status !== '' ? $status : '-') .
                '.'
            );
        }
    }

    private function generateManifestNumber(
        array $originBranch
    ): string {
        $code = strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                (string) (
                    $originBranch['branch_code']
                    ?? 'BR'
                )
            )
        );

        $code = substr(
            $code !== '' ? $code : 'BR',
            0,
            10
        );

        for ($attempt = 1; $attempt <= 999; $attempt++) {
            $manifestNo = sprintf(
                'MNF-%s-%s-%03d',
                $code,
                date('ymd'),
                $attempt
            );

            $exists = $this->manifestModel
                ->where(
                    'manifest_no',
                    $manifestNo
                )
                ->first();

            if (!$exists) {
                return $manifestNo;
            }
        }

        throw new \RuntimeException(
            'Nomor manifest branch harian sudah penuh.'
        );
    }

    private function mapPublicStatus(
        string $internalStatus
    ): string {
        return match (strtoupper($internalStatus)) {
            'PICKED_UP',
            'RECEIVED_AT_ORIGIN_BRANCH' =>
                'PICKED_UP',

            'SENT_TO_MAIN_WAREHOUSE',
            'RECEIVED_AT_MAIN_WAREHOUSE',
            'ARRIVED_HUB',
            'IN_TRANSIT_HUB',
            'IN_TRANSIT' =>
                'IN_TRANSIT',

            'OUT_FOR_DELIVERY' =>
                'OUT_FOR_DELIVERY',

            'DELIVERED' =>
                'DELIVERED',

            'ON_HOLD' =>
                'ON_HOLD',

            'RETURN_TO_SENDER' =>
                'RETURN_TO_SENDER',

            'CANCELLED' =>
                'CANCELLED',

            default =>
                'CREATED',
        };
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function commitOrFail(): void
    {
        if ($this->db->transStatus() === false) {
            throw new \RuntimeException(
                'Transaksi manifest gagal.'
            );
        }

        $this->db->transCommit();
    }
}
