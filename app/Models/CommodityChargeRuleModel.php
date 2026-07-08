<?php

namespace App\Models;

use CodeIgniter\Model;

class CommodityChargeRuleModel extends Model
{
    protected $table = 'commodity_charge_rules';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $allowedFields = [

        'commodity_type_id',

        'customer_insurance',
        'vendor_insurance',

        'customer_packing',
        'vendor_packing',

        'customer_additional',
        'vendor_additional',

        'status'

    ];
}