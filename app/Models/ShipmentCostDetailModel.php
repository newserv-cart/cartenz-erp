<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentCostDetailModel extends Model
{
    protected $table            = 'shipment_cost_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'party',
        'component_code',
        'component_name',
        'amount',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
