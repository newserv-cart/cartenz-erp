<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table='vendors';

    protected $primaryKey='id';

    protected $returnType='array';

    protected $useTimestamps=true;

    protected $allowedFields=[

        'vendor_code',

        'vendor_name',

        'contact_person',

        'phone',

        'email',

        'api_type',

        'api_url',

        'status'

    ];
}