<?php

namespace App\Services;

class TariffEngine
{
    public function getBaseTariff(array $shipment): array
    {
        return [
            'selling_rate' => 0,
            'vendor_rate'  => 0,
            'weight'       => $shipment['chargeable_weight'] ?? 0,
            'service_id'   => $shipment['service_id'] ?? null,
            'country_id'   => $shipment['country_id'] ?? null,
        ];
    }
}