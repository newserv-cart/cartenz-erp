<?php

namespace App\Models;

use CodeIgniter\Model;

class PickupRequestModel extends Model
{
    protected $table            = 'pickup_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'pickup_date',
        'pickup_time',
        'pickup_address',
        'contact_name',
        'contact_phone',
        'pickup_status',
        'driver_name',
        'notes',
        'assigned_user_id',
        'picked_up_at',
        'picked_up_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
