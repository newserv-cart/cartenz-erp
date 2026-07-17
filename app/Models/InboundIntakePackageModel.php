<?php

namespace App\Models;

use CodeIgniter\Model;

class InboundIntakePackageModel extends Model
{
    protected $table            = 'inbound_intake_packages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'inbound_intake_id',
        'package_no',
        'package_barcode',
        'actual_weight',
        'length_cm',
        'width_cm',
        'height_cm',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
