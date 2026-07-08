<?php

namespace App\Models;

use CodeIgniter\Model;

class ManifestModel extends Model
{
    protected $table = 'manifests';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [

        'manifest_no',

        'origin_branch_id',
        'destination_branch_id',

        'manifest_date',

        'total_shipment',
        'total_weight',

        'status',
        'remarks',
        'total_shipment',
'total_weight'

    ];

    protected $useTimestamps = true;
}