<?php

namespace App\Models;

use CodeIgniter\Model;

class InboundIntakeModel extends Model
{
    protected $table            = 'inbound_intakes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'intake_no',
        'acceptance_method',
        'agent_id',
        'origin_branch_id',
        'receiving_branch_id',
        'receiving_location',
        'arrival_date',
        'agent_reference_no',
        'total_colly',
        'declared_weight',
        'received_weight',
        'intake_status',
        'converted_shipment_id',
        'received_by',
        'received_at',
        'cancelled_by',
        'cancelled_at',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
