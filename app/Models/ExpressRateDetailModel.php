<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressRateDetailModel extends Model
{
    protected $table            = 'express_rate_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'header_id',
        'shipment_type',
        'zone',
        'weight',
        'price',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
