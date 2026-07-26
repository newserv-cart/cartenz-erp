<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentPickupRequestModel extends Model
{
    protected $table      = 'shipment_pickup_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $allowedFields = [
        'shipment_id',
        'customer_id',
        'pickup_address_id',

        'pickup_contact_name',
        'pickup_contact_phone',

        'requested_pickup_date',
        'requested_time_start',
        'requested_time_end',

        'pickup_remarks',
        'pickup_status',

        'scheduled_pickup_at',
        'scheduled_by',

        'assigned_courier_user_id',
        'assigned_at',
        'assigned_by',

        'picked_up_at',
        'picked_up_by',

        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',

        'created_by',
    ];
}
