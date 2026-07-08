<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;
use App\Services\AdditionalService;

class AdditionalCalculator implements CalculatorInterface
{
    public function apply(array $shipment, CostBreakdown $cost): void
    {
        $additional = (new AdditionalService())->calculate($shipment);

        if ($additional['amount'] <= 0) {
            return;
        }

        $cost->add(

            'CUSTOMER',

            'ADDITIONAL',

            'Additional Charge',

            $additional['amount'],

            $additional['rule']

        );
    }
}