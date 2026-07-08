<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingStatusModel extends Model
{
    protected $table      = 'tracking_statuses';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'status_code',
        'status_name',
        'status_color',
        'sort_order',
        'is_active'
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';
}