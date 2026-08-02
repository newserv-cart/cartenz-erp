<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';

    protected $allowedFields = [
        'company_id',
        'invoice_no',
        'customer_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'vat_percent',
'vat_amount',
'extra_charge',
'grand_total',
        'paid_amount',
        'balance_amount',
        'invoice_status'
    ];

    protected $useTimestamps = true;
}