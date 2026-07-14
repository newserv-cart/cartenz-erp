<?php

namespace App\Models;

use CodeIgniter\Model;

class ShipmentModel extends Model
{
    protected $table            = 'shipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'company_id',
        'awb_no',
        'shipment_mode',
        'customer_reference_no',
        'shipment_date',
        'vendor_awb_no',
        'master_awb_no',

        'customer_id',
        'sender_address_id',
        'sender_country_id',
        'receiver_address_id',
        'agent_id',
        'vendor_id',

        'country_id',
        'service_id',
        'transport_mode_id',
        'delivery_type_id',
        'package_type_id',
        'payment_type',
        'commodity_type_id',

        'total_colly',
        'actual_weight',
        'volume_weight',
        'chargeable_weight',
        'declared_value',
        'currency_id',

        'insurance_amount',
        'packing_charge',
        'additional_charge',
        'discount_amount',

        'freight_charge',
        'remote_charge',
        'selling_amount',
        'vendor_cost',
        'vendor_freight',
        'fuel_charge',
        'vendor_remote_charge',
        'go_green_charge',
        'handling_charge',
        'vat_amount',
        'vendor_total',
        'total_charge',
        'profit_amount',
        'margin_percent',
        'markup_percent',

        'invoice_id',
        'is_invoiced',
        'shipment_status',
        'public_tracking_status',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
        'cancelled_from_status',
        'tracking_no_vendor',
        'remarks',

        'sender_name',
        'sender_company',
        'sender_phone',
        'sender_email',
        'sender_identity_number',
        'sender_address',
        'sender_city',
        'sender_state',
        'sender_postal_code',

        'receiver_name',
        'receiver_company',
        'receiver_phone',
        'receiver_email',
        'receiver_id_number',
        'receiver_address',
        'receiver_city',
        'receiver_state',
        'receiver_postal_code',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByAwb($awbNo)
    {
        return $this->where('awb_no', $awbNo)->first();
    }

    public function getDraftShipments()
    {
        return $this->where('shipment_status', 'DRAFT')->findAll();
    }

    public function getDeliveredShipments()
    {
        return $this->where('shipment_status', 'DELIVERED')->findAll();
    }
}
