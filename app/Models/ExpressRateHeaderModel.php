<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressRateHeaderModel extends Model
{
    protected $table         = 'express_rate_headers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object'; // atau 'array' sesuai kebiasaanmu
    protected $useTimestamps = true;

    protected $allowedFields = [
        'vendor_id',
        'service_id',
        'effective_date',
        'remarks'
    ];
}