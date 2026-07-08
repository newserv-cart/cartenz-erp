<?php

namespace App\Models;

use CodeIgniter\Model;

class GoGreenZoneModel extends Model
{
    protected $table='gogreen_zones';

    protected $primaryKey='id';

    protected $returnType='array';

    protected $useTimestamps=true;

    protected $allowedFields=[

        'zone_code',

        'zone_name',

        'description',

        'status'

    ];
}