<?php

namespace App\Controllers;

use App\Services\PricingEngine;

class PricingTestController extends BaseController
{
    public function index()
    {
        $shipment=[

            'country_id'=>16,

            'service_id'=>2,

            'chargeable_weight'=>25

        ];

        $engine=new PricingEngine();

        return $this->response->setJSON(

            $engine->calculate($shipment)

        );
    }
}