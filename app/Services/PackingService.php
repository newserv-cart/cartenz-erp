<?php

namespace App\Services;

class PackingService
{
    public function calculate(array $shipment): array
    {
        return [

            'amount' => (float)($shipment['packing_charge'] ?? 0),

            'rule' => [

                'type' => 'MANUAL'

            ]

        ];
    }
}