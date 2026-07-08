<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentItemModel extends Model
{
    protected $table            = 'shipment_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [

        'shipment_id',

        'item_description',
        'hs_code',

        'quantity',
        'unit_type',

        'unit_value',
        'currency_id',
        'total_value',

        'item_weight',

        'length_cm',
        'width_cm',
        'height_cm',

        'is_dangerous_goods',

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

    public function getItemsByShipment($shipmentId)
    {
        return $this->where('shipment_id', $shipmentId)
                    ->findAll();
    }
}