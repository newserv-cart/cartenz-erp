<?php

namespace App\Services;

use RuntimeException;

class RegularCalculationService
{
    protected RegularRateService $regularRateService;
    protected CommodityRuleService $commodityRuleService;

    public function __construct()
    {
        $this->regularRateService =
            new RegularRateService();

        $this->commodityRuleService =
            new CommodityRuleService();
    }

    public function calculate(array $data): array
    {
        $customerId = (int) ($data['customer_id'] ?? 0);
        $countryId = (int) ($data['country_id'] ?? 0);
        $serviceId = (int) ($data['service_id'] ?? 0);
        $commodityTypeId = (int) (
            $data['commodity_type_id'] ?? 0
        );

        $actualWeight = (float) (
            $data['actual_weight'] ?? 0
        );

        $volumeWeight = (float) (
            $data['volume_weight'] ?? 0
        );

        $explicitChargeable = (float) (
            $data['chargeable_weight'] ?? 0
        );

        $chargeWeight = $explicitChargeable > 0
            ? $explicitChargeable
            : max($actualWeight, $volumeWeight);

        if ($customerId <= 0) {
            throw new RuntimeException(
                'Customer ID tidak valid.'
            );
        }

        if ($chargeWeight <= 0) {
            throw new RuntimeException(
                'Chargeable Weight harus lebih dari 0 Kg.'
            );
        }

        $rate = $this->regularRateService->calculate(
            $customerId,
            $countryId,
            $serviceId,
            $chargeWeight,
            $data['shipment_date'] ?? null
        );

        $freight = (float) (
            $rate['customer_freight'] ?? 0
        );

        $vendorCost = (float) (
            $rate['vendor_freight'] ?? 0
        );

        $commodityResult =
            $this->commodityRuleService->calculate(
                $commodityTypeId,
                $chargeWeight
            );

        if (is_array($commodityResult)) {
            $commodityCharge = (float) (
                $commodityResult['charge_amount']
                ?? $commodityResult['amount']
                ?? $commodityResult['total_charge']
                ?? $commodityResult['charge']
                ?? 0
            );
        } else {
            $commodityCharge = (float) $commodityResult;
        }

        $insurance = (float) (
            $data['insurance_amount'] ?? 0
        );

        $packing = (float) (
            $data['packing_charge'] ?? 0
        );

        $additionalInput = (float) (
            $data['additional_charge'] ?? 0
        );

        $additional = $additionalInput + $commodityCharge;

        $discount = (float) (
            $data['discount_amount']
            ?? $data['discount']
            ?? 0
        );

        $customerTotal = round(
            $freight
            + $insurance
            + $packing
            + $additional
            - $discount,
            2
        );

        $profit = round(
            $customerTotal - $vendorCost,
            2
        );

        $margin = $customerTotal > 0
            ? round(
                ($profit / $customerTotal) * 100,
                2
            )
            : 0;

        $markup = $vendorCost > 0
            ? round(
                ($profit / $vendorCost) * 100,
                2
            )
            : 0;

        return [
            'freight'        => round($freight, 2),
            'insurance'      => round($insurance, 2),
            'packing'        => round($packing, 2),
            'additional'     => round($additional, 2),
            'commodity'      => round($commodityCharge, 2),
            'discount'       => round($discount, 2),
            'customer_total' => $customerTotal,
            'vendor_total'   => round($vendorCost, 2),
            'profit'         => $profit,
            'margin'         => $margin,
            'markup'         => $markup,

            'rate_info' => [
                'tariff_id'       => $rate['tariff_id'],
                'weight_from'     => $rate['weight_from'],
                'weight_to'       => $rate['weight_to'],
                'weight_range'    => $rate['weight_range'],
                'chargeable_weight' =>
                    $rate['chargeable_weight'],
                'pricing_date'    => $rate['pricing_date'],
                'agent_type'      => $rate['agent_type'],
                'price_column'    => $rate['price_column'],
                'customer_price'  =>
                    $rate['customer_price_per_kg'],
                'vendor_price'    =>
                    $rate['vendor_price_per_kg'],
                'publish_price'   => $rate['publish_price'],
                'formula'         => $rate['formula'],
            ],

            'pricing_breakdown' => [
                'freight'    => round($freight, 2),
                'insurance'  => round($insurance, 2),
                'packing'    => round($packing, 2),
                'commodity'  => round($commodityCharge, 2),
                'additional_input' =>
                    round($additionalInput, 2),
                'additional' => round($additional, 2),
                'discount'   => round($discount, 2),
                'total'      => $customerTotal,
            ],

            'vendor_breakdown' => [
                'freight' => round($vendorCost, 2),
                'total'   => round($vendorCost, 2),
            ],
        ];
    }
}
