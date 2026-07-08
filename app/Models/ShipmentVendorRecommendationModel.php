<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentVendorRecommendationModel extends Model
{
    protected $table='shipment_vendor_recommendations';

    protected $primaryKey='id';

    protected $returnType='array';

    protected $useTimestamps=true;

    protected $allowedFields=[

        'shipment_id',

        'vendor_id',

        'vendor_cost',

        'customer_charge',

        'profit',

        'score',

        'recommended'

    ];
}