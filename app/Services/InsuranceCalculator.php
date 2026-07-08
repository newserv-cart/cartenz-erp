<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;
use App\Services\InsuranceService;

class InsuranceCalculator implements CalculatorInterface
{
    public function apply(array $shipment, CostBreakdown $cost): void
    {
        $insurance = (new InsuranceService())->calculate($shipment);

        if ($insurance['amount'] <= 0) {
            return;
        }

        $cost->add(

            'CUSTOMER',

            'INSURANCE',

            'Insurance',

            $insurance['amount'],

            $insurance['rule']

        );
    }
}