<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentTypeModel extends Model
{
    protected $table = 'agent_types';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'agent_type_code',
        'agent_type_name',
        'status'
    ];

    protected $useTimestamps = true;
}