<?php

namespace App\Models;

use CodeIgniter\Model;

class PickupRequestModel extends Model
{
   protected $table = 'pickup_requests';

protected $allowedFields = [

    'shipment_id',

    'pickup_date',
    'pickup_time',

    'pickup_address',

    'contact_name',
    'contact_phone',

    'pickup_status',

    'driver_name',

    'notes'

];

protected $useTimestamps = true;
}
