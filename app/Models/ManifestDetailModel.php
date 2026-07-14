<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestDetailModel extends Model
{
    protected $table            = 'manifest_details';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'manifest_id',
        'shipment_id',
        'awb_no',
        'actual_weight',
        'previous_shipment_status',
        'previous_public_status',
        'added_at',
        'added_by',
    ];

    protected $useTimestamps = false;
}
