<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressRateHeaderModel extends Model
{
    protected $table            = 'express_rate_headers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vendor_id',
        'service_id',
        'currency_id',
        'effective_date',
        'remarks',
        'import_source',
        'import_batch_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
