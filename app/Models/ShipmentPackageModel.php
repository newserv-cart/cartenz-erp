<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentPackageModel extends Model
{
    protected $table            = 'shipment_packages';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [
        'shipment_id',
        'package_no',
        'package_barcode',

        'vendor_id',
        'vendor_awb_no',
        'vendor_status',
        'vendor_label_url',
        'is_submitted_to_vendor',

        'package_type_id',

        'actual_weight',

        'length_cm',
        'width_cm',
        'height_cm',

        'volume_weight',
        'chargeable_weight',
        'remarks',
    ];

    protected $useTimestamps = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}