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
     *
     * Fallback mapping menjaga data legacy tetap dapat dilihat
     * walaupun public_status pada baris lama belum terisi.
     */
    public function getPublicTrackingHistory(int $shipmentId): array
    {
        $rows = $this
            ->where('shipment_id', $shipmentId)
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
                $publicStatus = $this->mapInternalToPublic(
                    (string) ($row['tracking_status'] ?? '')
                );
            }

            if ($publicStatus === '') {
                continue;
            }

            $row['public_status'] = $publicStatus;

            if (
                $publicStatus === $lastPublicStatus
                && $result !== []
            ) {
                $lastIndex = array_key_last($result);

                // Tampilkan event terbaru dari status publik yang sama.
                $result[$lastIndex] = $row;
                continue;
            }

            $result[] = $row;
            $lastPublicStatus = $publicStatus;
        }

        return $result;
    }

    private function mapInternalToPublic(string $internalStatus): string
    {
        return match (strtoupper(trim($internalStatus))) {
            'DRAFT',
            'CREATED',
            'MANIFESTED'
                => 'CREATED',

            'PICKED_UP'
                => 'PICKED_UP',

            'SENT_TO_MAIN_WAREHOUSE',
            'RECEIVED_AT_MAIN_WAREHOUSE',
            'ARRIVED_HUB',
            'IN_TRANSIT_HUB',
            'IN_TRANSIT'
                => 'IN_TRANSIT',

            'OUT_FOR_DELIVERY'
                => 'OUT_FOR_DELIVERY',

            'DELIVERED'
                => 'DELIVERED',

            'ON_HOLD'
                => 'ON_HOLD',

            'RETURN_TO_SENDER'
                => 'RETURN_TO_SENDER',

            'CANCELLED'
                => 'CANCELLED',

            default
                => '',
        };
    }

    public function getLastNonExceptionTracking(
        int $shipmentId,
        array $exceptionStatuses = ['ON_HOLD', 'RETURN_TO_SENDER', 'CANCELLED']
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
