<?php

namespace App\Models;

use CodeIgniter\Model;

class FuelRuleModel extends Model
{
    protected $table='fuel_rules';

    protected $primaryKey='id';

    protected $returnType='array';

    protected $useTimestamps=true;

    protected $allowedFields=[

        'service_id',

        'customer_type',

        'customer_value',

        'vendor_type',

        'vendor_value',

        'effective_from',

        'effective_to',

        'status'

    ];
}