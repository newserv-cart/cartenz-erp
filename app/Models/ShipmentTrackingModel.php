<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentTrackingModel extends Model
{
    protected $table            = 'shipment_tracking';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [
        'shipment_id',
        'tracking_code',
        'tracking_status',
        'tracking_location',
        'tracking_notes',
        'event_time',
        'created_by',
        'created_at',
    ];

    protected $useTimestamps = false;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getTrackingHistory($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->orderBy('event_time', 'ASC')
                    ->findAll();
    }

    public function getLatestTracking($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->orderBy('event_time', 'DESC')
                    ->first();
    }
}