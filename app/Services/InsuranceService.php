<?php

namespace App\Services;

class InsuranceService
{
    public function calculate(array $shipment): array
    {
        $amount = (float)($shipment['insurance_amount'] ?? 0);

        return [

            'amount' => $amount,

            'rule' => [

                'type' => 'MANUAL'

            ]

        ];
    }
}