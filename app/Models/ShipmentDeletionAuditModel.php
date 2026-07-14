<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentDeletionAuditModel extends Model
{
    protected $table            = 'shipment_deletion_audits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id_original',
        'awb_no',
        'customer_id',
        'shipment_status',
        'delete_reason',
        'deleted_by',
        'deleted_at',
        'snapshot_json',
        'created_at',
    ];

    protected $useTimestamps = false;
}
