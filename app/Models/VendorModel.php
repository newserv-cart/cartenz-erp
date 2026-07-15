<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table            = 'vendors';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vendor_code',
        'vendor_name',
        'vendor_category',
        'vendor_type',
        'vendor_priority',
        'country_id',
        'contact_person',
        'phone',
        'email',
        'website',
        'address',
        'api_available',
        'tracking_url',
        'credit_limit',
        'deposit_balance',
        'payment_term_days',
        'status',
        'notes',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
