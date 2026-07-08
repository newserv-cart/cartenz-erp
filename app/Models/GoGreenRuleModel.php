<?php

namespace App\Models;

use CodeIgniter\Model;

class GoGreenRuleModel extends Model
{
    protected $table='gogreen_rules';

    protected $primaryKey='id';

    protected $returnType='array';

    protected $useTimestamps=true;

    protected $allowedFields=[

        'service_id',

        'gogreen_zone_id',

        'customer_type',

        'customer_value',

        'vendor_type',

        'vendor_value',

        'effective_from',

        'effective_to',

        'status'

    ];
}