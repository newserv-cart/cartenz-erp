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

        // Generic owner
        'owner_type',
        'owner_id',

        'address_type',
        'address_label',
        'contact_name',
        'company_name',
        'phone',
        'email',
        'identity_number',
        'country_id',
        'state',
        'city',
        'postal_code',
        'address',

        // Address book metadata
        'is_default',
        'is_primary',
        'source',
        'status',

        'created_at',
        'updated_at',
    ];
}