<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestDetailModel extends Model
{
    protected $table = 'manifest_details';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'manifest_id',
        'shipment_id',

        'awb_no',

        'actual_weight'

    ];

    protected $useTimestamps = false;
}