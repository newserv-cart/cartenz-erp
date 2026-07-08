<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;
use App\Services\DiscountService;

class DiscountCalculator implements CalculatorInterface
{
    public function apply(array $shipment, CostBreakdown $cost): void
    {
        $discount = (new DiscountService())->calculate($shipment);

        if ($discount['amount'] <= 0) {
            return;
        }

        // Diskon mengurangi total customer
        $cost->add(

            'CUSTOMER',

            'DISCOUNT',

            'Discount',

            -1 * $discount['amount'],

            $discount['rule']

        );
    }
}