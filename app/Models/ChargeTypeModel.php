<?php

namespace App\Models;

use CodeIgniter\Model;

class ChargeTypeModel extends Model
{
    protected $table = 'charge_types';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $allowedFields = [

        'charge_code',

        'charge_name',

        'description',

        'charge_category',

        'calculation_type',

        'default_value',

        'currency_code',

        'display_order',

        'is_required',

        'is_active'

    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';
}