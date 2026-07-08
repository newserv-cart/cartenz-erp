<?php

namespace App\Libraries\Pricing\Engines;

use App\Libraries\Pricing\CostBreakdown;

use App\Libraries\Pricing\Calculators\RegularTariffCalculator;
use App\Libraries\Pricing\Calculators\RemoteCalculator;
use App\Libraries\Pricing\Calculators\InsuranceCalculator;
use App\Libraries\Pricing\Calculators\PackingCalculator;
use App\Libraries\Pricing\Calculators\AdditionalCalculator;
use App\Libraries\Pricing\Calculators\DiscountCalculator;

class RegularEngine
{
    public function calculate(array $shipment): CostBreakdown
    {

        $cost = new CostBreakdown();

        $calculators = [

            new RegularTariffCalculator(),

            new RemoteCalculator(),

            new InsuranceCalculator(),

            new PackingCalculator(),

            new AdditionalCalculator(),

            new DiscountCalculator(),

        ];

        foreach($calculators as $calculator){

            $calculator->apply($shipment,$cost);

        }

        return $cost;

    }
}