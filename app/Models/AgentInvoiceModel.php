<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentInvoiceModel extends Model
{
    protected $table = 'agent_invoices';

    protected $primaryKey = 'id';

    protected $allowedFields = [

        'agent_id',
        'invoice_no',
        'invoice_date',
        'due_date',

        'total_amount',
        'paid_amount',
        'balance_amount',

        'invoice_status'
    ];

    protected $useTimestamps = true;
}