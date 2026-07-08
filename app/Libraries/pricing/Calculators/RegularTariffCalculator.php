<?php

namespace App\Libraries\Pricing\Calculators;

use App\Models\RegularTariffModel;
use App\Libraries\Pricing\CostBreakdown;

class RegularTariffCalculator implements CalculatorInterface
{
    public function calculate(array $shipment): array
    {
        $model = new RegularTariffModel();

        $tariff = $model
            ->where('country_id', $shipment['country_id'])
            ->where('service_id', $shipment['service_id'])
            ->where('transport_mode_id', $shipment['transport_mode_id'])
            ->where('weight_from <=', $shipment['chargeable_weight'])
            ->where('weight_to >=', $shipment['chargeable_weight'])
            ->where('status', 1)
            ->first();

        if (!$tariff) {
            return [
                'amount'        => 0,
                'vendor_amount' => 0,
                'details'       => [
                    'error' => 'Regular tariff not found'
                ]
            ];
        }

        // ===========================
        // Ambil harga sesuai tipe agent
        // ===========================

        $pricePerKg = $this->getSellingPrice(
            $tariff,
            $shipment['agent_type'] ?? null
        );

        $sellingAmount = $pricePerKg * $shipment['chargeable_weight'];
        $vendorAmount  = $tariff['vendor_cost'] * $shipment['chargeable_weight'];

        return [
            'amount'        => $sellingAmount,
            'vendor_amount' => $vendorAmount,
            'details'       => [
                'price_per_kg'       => $pricePerKg,
                'vendor_per_kg'      => $tariff['vendor_cost'],
                'weight'             => $shipment['chargeable_weight'],
                'weight_from'        => $tariff['weight_from'],
                'weight_to'          => $tariff['weight_to'],
                'country_id'         => $shipment['country_id'],
                'service_id'         => $shipment['service_id'],
                'transport_mode_id'  => $shipment['transport_mode_id']
            ]
        ];
    }

    /**
     * Harga jual sesuai Agent Type
     */
    private function getSellingPrice(array $tariff, ?string $agentType): float
    {
        switch (strtoupper($agentType ?? '')) {
            case 'A':
                return (float) $tariff['agent_a_price'];
            case 'B':
                return (float) $tariff['agent_b_price'];
            case 'C':
                return (float) $tariff['agent_c_price'];
            default:
                return (float) $tariff['publish_price'];
        }
    }

    public function apply(array $shipment, CostBreakdown $cost): void
    {
        $result = $this->calculate($shipment);

        if ($result['amount'] <= 0) {
            return;
        }

        $cost->add(
            'CUSTOMER',
            'TARIFF',
            'Regular Tariff',
            $result['amount'],
            $result['details']
        );

        $cost->add(
            'VENDOR',
            'TARIFF',
            'Vendor Cost',
            $result['vendor_amount'],
            $result['details']
        );
    }
}