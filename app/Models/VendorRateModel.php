<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorRateModel extends Model
{
    protected $table            = 'vendor_rates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vendor_id',
        'country_id',
        'service_id',
        'transport_mode_id',
        'delivery_type_id',
        'weight_from',
        'weight_to',
        'cost_per_kg',
        'minimum_charge',
        'currency_id',
        'effective_from',
        'effective_until',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
