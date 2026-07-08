<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAddressModel extends Model
{
    protected $table = 'customer_addresses';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $allowedFields = [


    'customer_id',

    'address_type',

    'address_label',

    'contact_name',

    'company_name',

    'phone',

    'email',

    'address',

    'postal_code',

    'country_id',

    'city',

    'state',

    'identity_number',

    'is_default',

    'status'

    ];
}