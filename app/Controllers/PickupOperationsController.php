<?php

namespace App\Controllers;

use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class PickupOperationsController extends BaseController
{
    public function index()
    {
        $statusFilter = strtoupper(
            trim((string) $this->request->getGet('status'))
        );

        $allowedFilters = [
            'ACTIVE',
            'REQUESTED',
            'SCHEDULED',
            'COURIER_ASSIGNED',
            'PICKED_UP',
            'CANCELLED',
        ];

        if (!in_array($statusFilter, $allowedFilters, true)) {
            $statusFilter = 'ACTIVE';
        }

        $db = db_connect();

        $builder = $db
            ->table('shipment_pickup_requests pr')
            ->select([
                'pr.id AS pickup_request_id',
                'pr.shipment_id',
                'pr.customer_id',
                'pr.pickup_address_id',
                'pr.pickup_contact_name',
                'pr.pickup_contact_phone',
                'pr.requested_pickup_date',
                'pr.requested_time_start',
                'pr.requested_time_end',
                'pr.pickup_remarks',
                'pr.pickup_status',
                'pr.scheduled_pickup_at',
                'pr.scheduled_by',
                'pr.assigned_courier_user_id',
                'pr.assigned_at',
                'pr.assigned_by',
                'pr.picked_up_at',
                'pr.picked_up_by',
                'pr.created_at',

                's.awb_no',
                's.shipment_status',
                's.handover_method',
                's.entry_channel',
                's.total_colly',
                's.actual_weight',
                's.volume_weight',
                's.chargeable_weight',
                's.pricing_status',

                's.sender_name',
                's.sender_company',
                's.sender_phone',
                's.sender_address',
                's.sender_city',
                's.sender_state',
                's.sender_postal_code',

                'c.customer_name',
                'courier.username AS courier_username',
                'scheduler.username AS scheduled_by_username',
            ])
            ->join(
                'shipments s',
                's.id = pr.shipment_id'
            )
            ->join(
                'customers c',
                'c.id = pr.customer_id',
                'left'
            )
            ->join(
                'users courier',
                'courier.id = pr.assigned_courier_user_id',
                'left'
            )
            ->join(
                'users scheduler',
                'scheduler.id = pr.scheduled_by',
                'left'
            );

        if ($statusFilter === 'ACTIVE') {
            $builder->whereIn('pr.pickup_status', [
                'REQUESTED',
                'SCHEDULED',
                'COURIER_ASSIGNED',
            ]);
        } else {
            $builder->where(
                'pr.pickup_status',
                $statusFilter
            );
        }

        $pickupQuery = $builder
            ->orderBy('pr.requested_pickup_date', 'ASC')
            ->orderBy('pr.requested_time_start', 'ASC')
            ->orderBy('pr.id', 'ASC')
            ->get();

        if ($pickupQuery === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query pickup dashboard gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $courierQuery = $db
            ->table('users u')
            ->select([
                'u.id',
                'u.username',
            ])
            ->join(
                'auth_groups_users agu',
                'agu.user_id = u.id',
                'inner'
            )
            ->whereIn('agu.group', [
                'courier',
                'operations',
                'driver',
            ])
            ->groupBy([
                'u.id',
                'u.username',
            ])
            ->orderBy('u.username', 'ASC')
            ->get();

        $couriers = $courierQuery !== false
            ? $courierQuery->getResultArray()
            : [];

        return view('operations/pickups/index', [
            'title'        => 'Pickup Operations',
            'pickups'      => $pickupQuery->getResultArray(),
            'couriers'     => $couriers,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function schedule(int $pickupRequestId)
    {
        $scheduledAtInput = trim(
            (string) $this->request->getPost('scheduled_pickup_at')
        );

        if ($scheduledAtInput === '') {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Jadwal pickup wajib diisi.'
                );
        }

        $scheduledTimestamp = strtotime($scheduledAtInput);

        if ($scheduledTimestamp === false) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Jadwal pickup tidak valid.'
                );
        }

        $scheduledAt = date(
            'Y-m-d H:i:s',
            $scheduledTimestamp
        );

        $db     = db_connect();
        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $pickup = $this->findPickup($pickupRequestId);

        if (
            strtoupper(
                (string) ($pickup['pickup_status'] ?? '')
            ) !== 'REQUESTED'
        ) {
            return redirect()->back()->with(
                'error',
                'Pickup tidak dapat dijadwalkan. Status saat ini: '
                . ($pickup['pickup_status'] ?? '-')
            );
        }

        $db->transBegin();

        try {
            $updated = $db
                ->table('shipment_pickup_requests')
                ->where('id', $pickupRequestId)
                ->where('pickup_status', 'REQUESTED')
                ->update([
                    'pickup_status'       => 'SCHEDULED',
                    'scheduled_pickup_at' => $scheduledAt,
                    'scheduled_by'        => $userId,
                    'updated_at'          => $now,
                ]);

            if (
                $updated === false
                || $db->affectedRows() !== 1
            ) {
                throw new \RuntimeException(
                    'Pickup sudah dijadwalkan atau datanya berubah.'
                );
            }

            $this->writeTracking(
                $db,
                (int) $pickup['shipment_id'],
                (string) ($pickup['awb_no'] ?? ''),
                'CREATED',
                'PICKUP_SCHEDULED',
                'Customer Pickup Address',
                'Pickup dijadwalkan pada '
                    . $scheduledAt
                    . ' oleh tim operasional CARTENZ.',
                $userId,
                $now
            );

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penjadwalan pickup gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('operations/pickups'))
                ->with(
                    'success',
                    'Pickup AWB '
                    . ($pickup['awb_no'] ?? '')
                    . ' berhasil dijadwalkan.'
                );

        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                'Gagal menjadwalkan pickup: '
                . $e->getMessage()
            );
        }
    }

    public function assign(int $pickupRequestId)
    {
        $courierUserId = (int) $this->request->getPost(
            'courier_user_id'
        );

        if ($courierUserId <= 0) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Kurir wajib dipilih.'
                );
        }

        $db = db_connect();

        $courier = $db
            ->table('users')
            ->select([
                'id',
                'username',
            ])
            ->where('id', $courierUserId)
            ->get()
            ->getRowArray();

        if (!$courier) {
            return redirect()->back()->with(
                'error',
                'User kurir tidak ditemukan.'
            );
        }

        $pickup = $this->findPickup($pickupRequestId);

        if (
            strtoupper(
                (string) ($pickup['pickup_status'] ?? '')
            ) !== 'SCHEDULED'
        ) {
            return redirect()->back()->with(
                'error',
                'Kurir hanya dapat ditugaskan pada pickup SCHEDULED.'
            );
        }

        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $updated = $db
                ->table('shipment_pickup_requests')
                ->where('id', $pickupRequestId)
                ->where('pickup_status', 'SCHEDULED')
                ->update([
                    'pickup_status'            =>
                        'COURIER_ASSIGNED',

                    'assigned_courier_user_id' =>
                        $courierUserId,

                    'assigned_at'              =>
                        $now,

                    'assigned_by'              =>
                        $userId,

                    'updated_at'               =>
                        $now,
                ]);

            if (
                $updated === false
                || $db->affectedRows() !== 1
            ) {
                throw new \RuntimeException(
                    'Pickup sudah memiliki kurir atau datanya berubah.'
                );
            }

            $this->writeTracking(
                $db,
                (int) $pickup['shipment_id'],
                (string) ($pickup['awb_no'] ?? ''),
                'CREATED',
                'COURIER_ASSIGNED',
                'Origin Operations',
                'Kurir '
                    . ($courier['username'] ?? 'CARTENZ')
                    . ' telah ditugaskan untuk menjemput shipment.',
                $userId,
                $now
            );

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi assignment kurir gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('operations/pickups'))
                ->with(
                    'success',
                    'Kurir '
                    . ($courier['username'] ?? '')
                    . ' berhasil ditugaskan untuk AWB '
                    . ($pickup['awb_no'] ?? '')
                    . '.'
                );

        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                'Gagal assign kurir: '
                . $e->getMessage()
            );
        }
    }

    public function markPickedUp(int $pickupRequestId)
    {
        $pickup = $this->findPickup($pickupRequestId);

        if (
            strtoupper(
                (string) ($pickup['pickup_status'] ?? '')
            ) !== 'COURIER_ASSIGNED'
        ) {
            return redirect()->back()->with(
                'error',
                'Pickup belum memiliki kurir atau sudah diproses.'
            );
        }

        if (
            strtoupper(
                (string) ($pickup['shipment_status'] ?? '')
            ) !== 'CREATED'
        ) {
            return redirect()->back()->with(
                'error',
                'Shipment tidak dapat dijemput. Status saat ini: '
                . ($pickup['shipment_status'] ?? '-')
            );
        }

        $db     = db_connect();
        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $pickupUpdated = $db
                ->table('shipment_pickup_requests')
                ->where('id', $pickupRequestId)
                ->where(
                    'pickup_status',
                    'COURIER_ASSIGNED'
                )
                ->update([
                    'pickup_status' => 'PICKED_UP',
                    'picked_up_at'  => $now,
                    'picked_up_by'  => $userId,
                    'updated_at'    => $now,
                ]);

            if (
                $pickupUpdated === false
                || $db->affectedRows() !== 1
            ) {
                throw new \RuntimeException(
                    'Pickup sudah dikonfirmasi atau datanya berubah.'
                );
            }

            $shipmentUpdated = $db
                ->table('shipments')
                ->where(
                    'id',
                    (int) $pickup['shipment_id']
                )
                ->where('shipment_status', 'CREATED')
                ->where(
                    'handover_method',
                    'CUSTOMER_PICKUP_REQUEST'
                )
                ->update([
                    'shipment_status' => 'PICKED_UP',
                    'updated_at'      => $now,
                ]);

            if (
                $shipmentUpdated === false
                || $db->affectedRows() !== 1
            ) {
                throw new \RuntimeException(
                    'Status shipment gagal diperbarui menjadi PICKED_UP.'
                );
            }

            $this->writeTracking(
                $db,
                (int) $pickup['shipment_id'],
                (string) ($pickup['awb_no'] ?? ''),
                'PICKED_UP',
                'PICKED_UP',
                'Customer Pickup Address',
                'Shipment telah dijemput oleh kurir CARTENZ '
                    . 'dan sedang dibawa menuju origin branch.',
                $userId,
                $now
            );

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi konfirmasi pickup gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('operations/pickups'))
                ->with(
                    'success',
                    'Shipment '
                    . ($pickup['awb_no'] ?? '')
                    . ' berhasil ditandai PICKED_UP.'
                );

        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[PICKUP CONFIRM FAILED] '
                . $e->getMessage()
            );

            return redirect()->back()->with(
                'error',
                'Gagal mengonfirmasi pickup: '
                . $e->getMessage()
            );
        }
    }

    private function findPickup(int $pickupRequestId): array
    {
        $db = db_connect();

        $pickup = $db
            ->table('shipment_pickup_requests pr')
            ->select([
                'pr.*',
                's.awb_no',
                's.shipment_status',
                's.handover_method',
                's.sender_city',
                's.sender_state',
            ])
            ->join(
                'shipments s',
                's.id = pr.shipment_id'
            )
            ->where('pr.id', $pickupRequestId)
            ->get()
            ->getRowArray();

        if (!$pickup) {
            throw PageNotFoundException::forPageNotFound(
                'Pickup request tidak ditemukan.'
            );
        }

        return $pickup;
    }

    private function writeTracking(
        $db,
        int $shipmentId,
        string $awbNo,
        string $shipmentStatus,
        string $eventCode,
        string $location,
        string $description,
        int $userId,
        string $createdAt
    ): void {
        $tracking = new ShipmentTrackingModel();

        if (!$tracking->insert([
            'shipment_id'       => $shipmentId,
            'tracking_code'     => $awbNo,
            'tracking_status'   => $shipmentStatus,
            'public_status'     => $shipmentStatus,
            'event_code'        => $eventCode,
            'event_type'        => $eventCode,
            'source_reference'  => $awbNo,
            'tracking_location' => $location,
            'tracking_notes'    => $description,
            'event_time'        => $createdAt,
            'created_by'        => $userId,
            'created_at'        => $createdAt,
        ])) {
            throw new \RuntimeException(
                'Gagal menyimpan tracking pickup.'
            );
        }

        $history = new ShipmentHistoryModel();

        if (!$history->insert([
            'shipment_id' => $shipmentId,
            'event_type'  => $eventCode,
            'event_title' => str_replace(
                '_',
                ' ',
                $eventCode
            ),
            'description' => $description,
            'changed_fields' => json_encode([
                'shipment_status' => $shipmentStatus,
                'event_code'      => $eventCode,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by' => $userId,
            'created_at' => $createdAt,
        ])) {
            throw new \RuntimeException(
                'Gagal menyimpan history pickup.'
            );
        }
    }
}
