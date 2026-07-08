<?php

namespace App\Models;

use CodeIgniter\Model;

class CommodityTypeModel extends Model
{
    protected $table = 'commodity_types';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'commodity_name',

        'status'

    ];

}