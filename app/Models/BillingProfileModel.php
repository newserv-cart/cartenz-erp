<?php

namespace App\Models;

use CodeIgniter\Model;

class BillingProfileModel extends Model
{
    protected $table = 'billing_profiles';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'customer_id',
        'billing_type',
        'invoice_cycle',
        'credit_days',
        'credit_limit',
        'auto_lock',
        'auto_hold_shipment',
        'allow_over_credit',
        'status'
    ];

    protected $useTimestamps = true;
}