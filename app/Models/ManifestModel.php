<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestModel extends Model
{
    protected $table            = 'manifests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'manifest_no',
        'origin_branch_id',
        'destination_branch_id',
        'manifest_date',
        'total_shipment',
        'total_weight',
        'status',
        'remarks',
        'created_by',
        'dispatched_at',
        'dispatched_by',
        'dispatched_branch_id',
        'received_at',
        'received_by',
        'received_branch_id',
        'closed_at',
        'closed_by',
        'closed_branch_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
