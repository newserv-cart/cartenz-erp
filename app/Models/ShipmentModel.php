<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ManifestDetailModel;
class ShipmentModel extends Model
{
    protected $table            = 'shipments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $protectFields = true;

    protected $allowedFields = [
        'company_id',
        'awb_no',
        'customer_reference_no',
        'vendor_awb_no',
        'master_awb_no',

        'customer_id',
        'receiver_address_id',
        'agent_id',
        'vendor_id',

        'country_id',
        'service_id',
        'transport_mode_id',
        'delivery_type_id',
        'package_type_id',
        'commodity_type_id',

        'total_colly',

        'actual_weight',
        'volume_weight',
        'chargeable_weight',

        'declared_value',
        'currency_id',
        'insurance_amount',

        'selling_amount',
        'vendor_cost',
        'total_charge',
        'profit_amount',
        'payment_type',

        'invoice_id',

        'shipment_date',
        'shipment_status',
        'tracking_no_vendor',
        'remarks',

          
   
   

    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

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

    public function print($id)
{
    $manifest = (new ManifestModel())
        ->find($id);

    $details = (new ManifestDetailModel())
        ->where('manifest_id', $id)
        ->findAll();

    return view(
        'manifests/print',
        [
            'manifest' => $manifest,
            'details'  => $details,
        ]
    );
}

public function calculateRate()
{

    $countryId = $this->request->getPost('country_id');

    $serviceId = $this->request->getPost('service_id');

    $weight = $this->request->getPost('chargeable_weight');

    if(empty($countryId) || empty($serviceId) || empty($weight)){

        return $this->response->setJSON([

            'success'=>false

        ]);

    }

    /*
        Sprint 19

        sementara kita isi dummy dulu.

        nanti diganti query regular_tariffs
        dan express tariff
    */

    $selling = $weight * 50000;

    return $this->response->setJSON([

        'success'=>true,

        'selling_amount'=>$selling

    ]);

}

}