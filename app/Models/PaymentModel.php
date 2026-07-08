<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'id';

    protected $allowedFields = [

    'payment_no',

    'invoice_id',

    'payment_type',

    'agent_invoice_id',

    'payment_date',

    'payment_method',

    'reference_no',

    'amount',

    'remarks',

    'proof_file',

    'status'
];

    protected $useTimestamps = true;
}