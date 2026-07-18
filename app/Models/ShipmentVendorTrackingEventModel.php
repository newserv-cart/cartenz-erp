<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentVendorTrackingEventModel extends Model
{
    protected $table            = 'shipment_vendor_tracking_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'vendor_id',
        'shipment_package_id',
        'vendor_awb_no',
        'internal_status',
        'public_status',
        'event_source',
        'source_event_id',
        'vendor_status_code',
        'country_code',
        'country_name',
        'city',
        'hub_code',
        'location_name',
        'timezone_name',
        'event_time',
        'event_sequence',
        'tracking_notes',
        'event_fingerprint',
        'is_public',
        'raw_payload',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    public function getEvents(int $shipmentId): array
    {
        return $this
            ->where('shipment_id', $shipmentId)
            ->orderBy('event_time', 'DESC')
            ->orderBy('event_sequence', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function getLatestEvent(int $shipmentId): ?array
    {
        return $this
            ->where('shipment_id', $shipmentId)
            ->orderBy('event_time', 'DESC')
            ->orderBy('event_sequence', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }
}
