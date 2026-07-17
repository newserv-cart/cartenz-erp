<?php

namespace App\Services;

use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;
use CodeIgniter\Database\BaseConnection;

class ShipmentAcceptanceService
{
    public const DATA_ENTRY_ONLY = 'DATA_ENTRY_ONLY';
    public const CUSTOMER_DROP_OFF_MAIN = 'CUSTOMER_DROP_OFF_MAIN_WAREHOUSE';
    public const CUSTOMER_DROP_OFF_BRANCH = 'CUSTOMER_DROP_OFF_BRANCH';
    public const COURIER_PICKUP = 'COURIER_PICKUP';
    public const AGENT_HANDOVER_MAIN = 'AGENT_HANDOVER_MAIN_WAREHOUSE';
    public const AGENT_HANDOVER_BRANCH = 'AGENT_HANDOVER_BRANCH';
    public const AGENT_HANDOVER_NO_AWB = 'AGENT_HANDOVER_NO_AWB';
    public const DIRECT_MAIN_RECEIVE = 'DIRECT_MAIN_WAREHOUSE_RECEIVE';

    private const TERMINAL_STATUSES = [
        'CANCELLED',
        'DELIVERED',
        'RETURN_TO_SENDER',
        'CLOSED',
    ];

    public static function methodLabels(): array
    {
        return [
            self::DATA_ENTRY_ONLY => 'Data Entry Only — status Draft',
            self::CUSTOMER_DROP_OFF_MAIN => 'Customer Drop-off at Main Warehouse',
            self::CUSTOMER_DROP_OFF_BRANCH => 'Customer Drop-off at Branch',
            self::COURIER_PICKUP => 'Courier Pickup',
            self::AGENT_HANDOVER_MAIN => 'Agent Handover at Main Warehouse — AWB Available',
            self::AGENT_HANDOVER_BRANCH => 'Agent Handover at Branch — AWB Available',
            self::AGENT_HANDOVER_NO_AWB => 'Agent Handover Without AWB — From Inbound Intake',
        ];
    }

    public function normalizeMethod(string $method): string
    {
        $method = strtoupper(trim($method));

        if (!array_key_exists($method, self::methodLabels())) {
            throw new \RuntimeException('Acceptance Method tidak valid.');
        }

        return $method;
    }

    public function automaticContext(
        int $actorId,
        ?array $inboundIntake = null
    ): ?array {
        return (new BranchOperationalContextService())
            ->automaticShipmentContext(
                $actorId,
                $inboundIntake
            );
    }

    public function initialState(string $method): array
    {
        $method = $this->normalizeMethod($method);

        return match ($method) {
            self::CUSTOMER_DROP_OFF_MAIN,
            self::AGENT_HANDOVER_MAIN,
            self::AGENT_HANDOVER_NO_AWB => [
                'internal_status' => 'RECEIVED_AT_MAIN_WAREHOUSE',
                'public_status'   => 'IN_TRANSIT',
                'notes'           => 'Shipment diterima di main warehouse.',
            ],

            self::CUSTOMER_DROP_OFF_BRANCH,
            self::AGENT_HANDOVER_BRANCH => [
                'internal_status' => 'RECEIVED_AT_ORIGIN_BRANCH',
                'public_status'   => 'PICKED_UP',
                'notes'           => 'Shipment diterima di origin branch.',
            ],

            self::COURIER_PICKUP => [
                'internal_status' => 'PICKED_UP',
                'public_status'   => 'PICKED_UP',
                'notes'           => 'Shipment berhasil dijemput dari pengirim.',
            ],

            default => [
                'internal_status' => 'DRAFT',
                'public_status'   => 'CREATED',
                'notes'           => 'Shipment berhasil dibuat.',
            ],
        };
    }

    public function recordInitialEvents(
        int $shipmentId,
        string $awbNo,
        string $method,
        string $location,
        int $actorId,
        ?int $branchId = null,
        ?array $stateOverride = null
    ): void {
        $state = $stateOverride
            ?? $this->initialState($method);
        $now   = date('Y-m-d H:i:s');

        $tracking = new ShipmentTrackingModel();
        $history  = new ShipmentHistoryModel();

        if (!$tracking->insert([
            'shipment_id'       => $shipmentId,
            'branch_id'         => $branchId,
            'tracking_code'     => $awbNo,
            'tracking_status'   => 'DRAFT',
            'public_status'     => 'CREATED',
            'event_type'        => 'SHIPMENT_CREATED',
            'source_reference'  => $awbNo,
            'tracking_location' => 'Cartenz System',
            'tracking_notes'    => 'Shipment berhasil dibuat.',
            'event_time'        => $now,
            'created_by'        => $actorId,
            'created_at'        => $now,
        ])) {
            throw new \RuntimeException('Tracking CREATED gagal disimpan.');
        }

        if (!$history->insert([
            'shipment_id'    => $shipmentId,
            'event_type'     => 'SHIPMENT_CREATED',
            'event_title'    => 'Shipment Created',
            'description'    => 'Shipment berhasil dibuat.',
            'changed_fields' => json_encode([
                'acceptance_method' => $method,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by'     => $actorId,
            'created_at'     => $now,
        ])) {
            throw new \RuntimeException('History shipment awal gagal disimpan.');
        }

        if ($state['internal_status'] === 'DRAFT') {
            return;
        }

        if (!$tracking->insert([
            'shipment_id'       => $shipmentId,
            'branch_id'         => $branchId,
            'tracking_code'     => $awbNo,
            'tracking_status'   => $state['internal_status'],
            'public_status'     => $state['public_status'],
            'event_type'        => 'SHIPMENT_ACCEPTED',
            'source_reference'  => $awbNo,
            'tracking_location' => $location,
            'tracking_notes'    => $state['notes'],
            'event_time'        => $now,
            'created_by'        => $actorId,
            'created_at'        => $now,
        ])) {
            throw new \RuntimeException('Tracking penerimaan shipment gagal disimpan.');
        }

        if (!$history->insert([
            'shipment_id'    => $shipmentId,
            'event_type'     => 'SHIPMENT_ACCEPTED',
            'event_title'    => 'Shipment Accepted',
            'description'    => $state['notes'] . ' Location: ' . $location,
            'changed_fields' => json_encode([
                'acceptance_method' => $method,
                'shipment_status'   => $state['internal_status'],
                'public_status'     => $state['public_status'],
                'location'          => $location,
                'branch_id'         => $branchId,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by'     => $actorId,
            'created_at'     => $now,
        ])) {
            throw new \RuntimeException('History penerimaan shipment gagal disimpan.');
        }
    }

    public function receiveDirect(
        int $shipmentId,
        string $location,
        string $receiveMethod,
        string $referenceNo,
        string $notes,
        int $actorId
    ): void {
        $location      = trim($location);
        $receiveMethod = strtoupper(trim($receiveMethod));
        $notes         = trim($notes);

        if ($actorId <= 0) {
            throw new \RuntimeException(
                'Sesi user tidak ditemukan. Silakan login ulang.'
            );
        }

        $allowedMethods = [
            'CUSTOMER_WALK_IN',
            'AGENT_HANDOVER',
            'DIRECT_DELIVERY',
            'INTERNAL_TRANSFER',
        ];

        if (!in_array($receiveMethod, $allowedMethods, true)) {
            throw new \RuntimeException(
                'Receive Method tidak valid.'
            );
        }

        if ($notes === '') {
            throw new \RuntimeException(
                'Notes wajib diisi untuk Direct Receive.'
            );
        }

        /*
         * SECURITY:
         * Direct Receive ke Main Warehouse hanya boleh dilakukan
         * oleh user yang memiliki Operational Profile aktif pada
         * branch utama JKT-HO.
         *
         * Profile kosong tidak boleh dianggap lolos.
         */
        $context = new BranchOperationalContextService();
        $profile = $context->requireProfile($actorId);

        if (!$context->profileIsMain($profile)) {
            throw new \RuntimeException(
                'Direct Main Warehouse Receive hanya boleh dilakukan ' .
                'oleh user JKT-HO / Head Office.'
            );
        }

        $branchId = (int) ($profile['branch_id'] ?? 0);
        $branch   = $context->branchById($branchId);

        if (
            !$branch
            || !$context->isMainBranch($branch)
        ) {
            throw new \RuntimeException(
                'Operational Profile tidak terhubung ke branch utama JKT-HO.'
            );
        }

        /*
         * Jangan percaya lokasi dari form/browser.
         * Lokasi resmi selalu berasal dari master branch user.
         */
        $location = $context->locationLabel($branch);

        $db = db_connect();
        $db->transBegin();

        try {
            $shipment = $db->query(
                'SELECT * FROM shipments WHERE id = ? FOR UPDATE',
                [$shipmentId]
            )->getRowArray();

            if (!$shipment) {
                throw new \RuntimeException('Shipment tidak ditemukan.');
            }

            $currentStatus = strtoupper((string) ($shipment['shipment_status'] ?? 'DRAFT'));

            if (in_array($currentStatus, self::TERMINAL_STATUSES, true)) {
                throw new \RuntimeException('Shipment berstatus terminal dan tidak dapat diterima ulang.');
            }

            if ($currentStatus === 'RECEIVED_AT_MAIN_WAREHOUSE') {
                throw new \RuntimeException('Shipment sudah diterima di main warehouse.');
            }

            if (strtoupper((string) ($shipment['vendor_submission_status'] ?? 'NOT_SUBMITTED')) === 'SUBMITTED') {
                throw new \RuntimeException('Shipment sudah disubmit ke vendor.');
            }

            $activeManifest = $db->table('manifest_details md')
                ->select('m.manifest_no, m.status')
                ->join('manifests m', 'm.id = md.manifest_id', 'inner')
                ->where('md.shipment_id', $shipmentId)
                ->whereIn(
                    'm.status',
                    ['OPEN', 'DISPATCHED', 'RECEIVED']
                )
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($activeManifest) {
                throw new \RuntimeException(
                    'Shipment berada dalam manifest ' .
                    $activeManifest['manifest_no'] .
                    ' (' . $activeManifest['status'] . '). Gunakan workflow manifest.'
                );
            }

            $now = date('Y-m-d H:i:s');
            $description = 'Shipment diterima langsung di main warehouse tanpa manifest.';

            if ($referenceNo !== '') {
                $description .= ' Reference: ' . $referenceNo . '.';
            }

            $description .= ' Method: ' . trim($receiveMethod) . '. ' . $notes;

            $shipmentModel = new ShipmentModel();

            if (!$shipmentModel->update($shipmentId, [
                'shipment_status'        => 'RECEIVED_AT_MAIN_WAREHOUSE',
                'public_tracking_status' => 'IN_TRANSIT',
                'acceptance_method'      => self::DIRECT_MAIN_RECEIVE,
                'acceptance_branch_id'   => $branchId > 0
                    ? $branchId
                    : null,
                'receiving_branch_id'    => $branchId > 0
                    ? $branchId
                    : (
                        $shipment['receiving_branch_id']
                        ?? null
                    ),
                'current_branch_id'      => $branchId > 0
                    ? $branchId
                    : (
                        $shipment['current_branch_id']
                        ?? null
                    ),
                'receiving_location'     => $location,
                'received_at'            => $now,
                'received_by'            => $actorId,
                'last_scan_branch_id'    => $branchId > 0
                    ? $branchId
                    : null,
                'last_scan_type'         => 'DIRECT_RECEIVE',
                'last_scan_at'           => $now,
                'last_scan_by'           => $actorId,
            ])) {
                throw new \RuntimeException('Status Direct Receive gagal diperbarui.');
            }

            $tracking = new ShipmentTrackingModel();

            if (!$tracking->insert([
                'shipment_id'       => $shipmentId,
                'branch_id'         => $branchId > 0
                    ? $branchId
                    : null,
                'tracking_code'     => $shipment['awb_no'],
                'tracking_status'   => 'RECEIVED_AT_MAIN_WAREHOUSE',
                'public_status'     => 'IN_TRANSIT',
                'event_type'        => 'DIRECT_MAIN_WAREHOUSE_RECEIVE',
                'scan_type'         => 'DIRECT_RECEIVE',
                'source_reference'  => $referenceNo !== ''
                    ? $referenceNo
                    : $shipment['awb_no'],
                'tracking_location' => $location,
                'tracking_notes'    => $description,
                'event_time'        => $now,
                'created_by'        => $actorId,
                'created_at'        => $now,
            ])) {
                throw new \RuntimeException('Tracking Direct Receive gagal disimpan.');
            }

            $history = new ShipmentHistoryModel();

            if (!$history->insert([
                'shipment_id'    => $shipmentId,
                'event_type'     => 'DIRECT_MAIN_WAREHOUSE_RECEIVE',
                'event_title'    => 'Direct Main Warehouse Receive',
                'description'    => $description,
                'changed_fields' => json_encode([
                    'previous_status' => $currentStatus,
                    'new_status'      => 'RECEIVED_AT_MAIN_WAREHOUSE',
                    'location'        => $location,
                    'receive_method'  => $receiveMethod,
                    'reference_no'    => $referenceNo,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_by'     => $actorId,
                'created_at'     => $now,
            ])) {
                throw new \RuntimeException('History Direct Receive gagal disimpan.');
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi Direct Receive gagal.');
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function branchOptions(): array
    {
        return (new BranchOperationalContextService())
            ->branchOptions();
    }
}
