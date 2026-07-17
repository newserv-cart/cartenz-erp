<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentVendorRecommendationModel extends Model
{
    protected $table            = 'shipment_vendor_recommendations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'shipment_id',
        'vendor_id',
        'vendor_cost',
        'customer_charge',
        'profit',
        'score',
        'recommended',
        'rank_no',
        'rate_source',
        'rate_reference_id',
        'currency_id',
        'chargeable_weight',
        'rate_weight',
        'rate_effective_date',
        'calculation_json',
        'generated_by',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
