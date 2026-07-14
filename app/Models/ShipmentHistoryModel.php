<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentHistoryModel extends Model
{
    protected $table            = 'shipment_histories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'event_type',
        'event_title',
        'description',
        'changed_fields',
        'created_by',
        'created_at',
    ];

    protected $useTimestamps = false;
}
