<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentChargeModel extends Model
{
    protected $table            = 'shipment_charges';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [

        'shipment_id',

        'charge_type_id',
        'charge_name',

        'qty',
        'unit_price',
        'total_amount',

        'is_vendor_charge',

        'remarks',
    ];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function getChargesByShipment($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->findAll();
    }

    public function getCustomerCharges($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->where('is_vendor_charge', 0)
                    ->findAll();
    }

    public function getVendorCharges($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->where('is_vendor_charge', 1)
                    ->findAll();
    }
}