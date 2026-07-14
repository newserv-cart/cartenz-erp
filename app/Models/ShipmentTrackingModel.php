<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentTrackingModel extends Model
{
    protected $table            = 'shipment_tracking';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'tracking_code',
        'tracking_status',
        'public_status',
        'tracking_location',
        'tracking_notes',
        'event_time',
        'created_by',
        'created_at',
    ];

    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function getTrackingHistory(int $shipmentId): array
    {
        return $this
            ->where('shipment_id', $shipmentId)
            ->orderBy('event_time', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getLatestTracking(int $shipmentId): ?array
    {
        return $this
            ->where('shipment_id', $shipmentId)
            ->orderBy('event_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Riwayat publik dengan status berurutan yang sama digabung.
     * Event internal tetap tersimpan lengkap di database.
     */
    public function getPublicTrackingHistory(int $shipmentId): array
    {
        $rows = $this
            ->where('shipment_id', $shipmentId)
            ->where('public_status IS NOT NULL', null, false)
            ->orderBy('event_time', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $result = [];
        $lastPublicStatus = null;

        foreach ($rows as $row) {
            $publicStatus = strtoupper(
                trim((string) ($row['public_status'] ?? ''))
            );

            if ($publicStatus === '') {
                continue;
            }

            if ($publicStatus === $lastPublicStatus && $result !== []) {
                $lastIndex = array_key_last($result);

                // Simpan event terakhir dari status publik yang sama.
                $result[$lastIndex] = $row;
                continue;
            }

            $result[] = $row;
            $lastPublicStatus = $publicStatus;
        }

        return $result;
    }

    public function getLastNonExceptionTracking(
        int $shipmentId,
        array $exceptionStatuses = ['ON_HOLD', 'RETURN_TO_SENDER']
    ): ?array {
        $builder = $this
            ->where('shipment_id', $shipmentId);

        if ($exceptionStatuses !== []) {
            $builder->whereNotIn('tracking_status', $exceptionStatuses);
        }

        return $builder
            ->orderBy('event_time', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }
}
