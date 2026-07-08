<?php

namespace App\Libraries\Pricing\Calculators;

class FuelCalculator implements CalculatorInterface
{

    public function calculate(array $shipment): float
    {

        /*
            Fuel Rule

            hanya EXPRESS
        */

        return 0;

    }

}