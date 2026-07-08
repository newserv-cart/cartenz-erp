<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;
use App\Services\CommodityRuleService;

class CommodityCalculator implements CalculatorInterface
{
    protected CommodityRuleService $service;

    public function __construct()
    {
        $this->service = new CommodityRuleService();
    }

    public function apply(array $shipment, CostBreakdown $cost): void
    {
        if (empty($shipment['commodity_type_id'])) {
            return;
        }

        $result = $this->service->calculate(

            (int)$shipment['commodity_type_id'],

            (float)$shipment['chargeable_weight']

        );

        if (($result['amount'] ?? 0) <= 0) {
            return;
        }

        $cost->add(

            'CUSTOMER',

            'COMMODITY',

            'Commodity Charge',

            $result['amount'],

            $result['rule'] ?? []

        );
    }
}