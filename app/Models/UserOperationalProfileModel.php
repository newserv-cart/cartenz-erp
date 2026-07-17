<?php

namespace App\Models;

use CodeIgniter\Model;

class UserOperationalProfileModel extends Model
{
    protected $table            = 'user_operational_profiles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'branch_id',
        'agent_id',
        'staff_type',
        'can_scan_pickup',
        'can_scan_dropoff',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
