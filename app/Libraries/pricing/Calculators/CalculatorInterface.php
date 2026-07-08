<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;

interface CalculatorInterface
{
    public function apply(array $shipment, CostBreakdown $cost): void;
}