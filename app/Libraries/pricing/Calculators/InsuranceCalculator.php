<?php

namespace App\Libraries\Pricing\Calculators;

class InsuranceCalculator implements CalculatorInterface
{
    public function calculate(array $shipment): array
    {
        $amount = (float) ($shipment['insurance_amount'] ?? 0);

        return [
            'amount'  => $amount,
            'details' => [
                'manual' => true
            ]
        ];
    }
}