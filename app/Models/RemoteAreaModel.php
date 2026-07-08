<?php

namespace App\Models;

use CodeIgniter\Model;

class RemoteAreaModel extends Model
{
    protected $table = 'remote_areas';

    protected $primaryKey = 'id';

    protected $allowedFields = [
        'country_id',
        'service_id',
        'vendor_id',
        'postal_code',
        'city',
        'charge',
        'status'
    ];
}