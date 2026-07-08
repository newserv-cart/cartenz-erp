<?php

namespace App\Models;

use CodeIgniter\Model;

class CountryModel extends Model
{
    protected $table = 'countries';

    protected $primaryKey = 'id';

    protected $returnType = 'array';
protected $allowedFields = [

    'region_id',

    'express_zone_id',

    'gogreen_zone_id',

    'country_code',

    'country_name',

    'status'


    ];
}