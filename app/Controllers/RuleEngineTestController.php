<?php

namespace App\Controllers;

use App\Services\RuleEngine;

class RuleEngineTestController extends BaseController
{
    public function index()
    {
        $context = [
            'country_id'        => 16,
            'service_id'        => 2,
            'chargeable_weight' => 25,
            'commodity_type_id' => 1,
            'customer_id'       => 1,
        ];

        $engine = new RuleEngine();

        return $this->response->setJSON([
            'status'  => 'success',
            'context' => $context,
            'matched' => $engine->evaluate($context),
        ]);
    }
}