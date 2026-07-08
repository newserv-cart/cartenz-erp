<?php

namespace App\Services;

class AdditionalService
{
    public function calculate(array $shipment): array
    {
        return [

            'amount' => (float)($shipment['additional_charge'] ?? 0),

            'rule' => []

        ];
    }
}