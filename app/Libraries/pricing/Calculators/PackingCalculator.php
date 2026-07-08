<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;
use App\Services\PackingService;

class PackingCalculator implements CalculatorInterface
{
    public function apply(array $shipment, CostBreakdown $cost): void
    {
        $packing = (new PackingService())->calculate($shipment);

        if ($packing['amount'] <= 0) {
            return;
        }

        $cost->add(

            'CUSTOMER',

            'PACKING',

            'Packing',

            $packing['amount'],

            $packing['rule']

        );
    }
}