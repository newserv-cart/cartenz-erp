<?php

namespace App\Services;

class DiscountService
{
    public function calculate(array $shipment): array
    {
        return [

            'amount' => (float)($shipment['discount'] ?? 0),

            'rule' => []

        ];
    }
}