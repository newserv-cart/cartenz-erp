<?php

namespace App\Controllers;

use App\Services\PricingEngine;

class PricingTestController extends BaseController
{
    public function index()
    {
        $shipment = [
    'customer_id'       => 1,
    'country_id'        => 16,
    'service_id'        => 2,
    'service_engine'    => 'REGULAR',

    'commodity_type_id' => 1,

    'actual_weight'     => 25,
    'volume_weight'     => 20,
    'chargeable_weight' => 25,

    'total_colly'       => 1,
    'total_items'       => 1,

    'insurance_amount'  => 0,
    'packing_charge'    => 0,
    'additional_charge' => 0,
    'discount_amount'   => 0,
];

        $engine=new PricingEngine();

        return $this->response->setJSON(

            $engine->calculate($shipment)

        );
    }
}