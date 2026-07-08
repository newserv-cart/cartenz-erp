<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentPaymentModel extends Model
{
    protected $table = 'agent_payments';

    protected $primaryKey = 'id';

    protected $allowedFields = [

        'agent_invoice_id',

        'payment_date',

        'amount',

        'bank_name',

        'reference_no',

        'proof_file',

        'status'
    ];

    protected $useTimestamps = true;
}