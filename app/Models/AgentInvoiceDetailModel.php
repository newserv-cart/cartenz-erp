<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentInvoiceDetailModel extends Model
{
    protected $table = 'agent_invoice_details';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'agent_invoice_id','shipment_id','awb_no','shipment_date',
        'chargeable_weight','freight_amount','insurance_amount',
        'packing_amount','additional_amount','discount_amount','line_total',
    ];
    protected $useTimestamps = true;
}
