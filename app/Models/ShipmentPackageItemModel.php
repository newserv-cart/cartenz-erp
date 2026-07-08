<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentPackageItemModel extends Model
{
    protected $table            = 'shipment_package_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [
        'shipment_package_id',

        'item_description',
        'hs_code',

        'quantity',
        'unit_type',

        'unit_value',
        'currency_id',
        'total_value',

        'country_of_origin',
        'brand_name',

        'remarks',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}