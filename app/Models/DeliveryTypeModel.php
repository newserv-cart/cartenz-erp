<?php

namespace App\Models;

use CodeIgniter\Model;

class DeliveryTypeModel extends Model
{
    protected $table = 'delivery_types';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'delivery_type_code',
        'delivery_type_name',
        'status'
    ];

    protected $useTimestamps = true;
}