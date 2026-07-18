<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentInvoiceModel extends Model
{
    protected $table = 'agent_invoices';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'agent_id','invoice_no','invoice_date','due_date',
        'total_amount','paid_amount','balance_amount','invoice_status',
        'source','approval_status','requested_by','requested_at',
        'approved_by','approved_at','issued_by','issued_at',
        'rejected_by','rejected_at','rejection_reason',
    ];
    protected $useTimestamps = true;
}
