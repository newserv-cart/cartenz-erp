<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentModel extends Model
{
    protected $table            = 'agents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'agent_code',
        'agent_name',
        'agent_type_id',
        'branch_id',
        'parent_agent_id',
        'handover_type',
        'handover_agent_id',
        'white_label_enabled',
        'company_name',
        'logo',
        'tracking_domain',
        'credit_limit',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
