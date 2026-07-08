<?php

namespace App\Services;

class RegularCalculationService
{
    protected RegularRateService $regularRateService;
    protected CommodityRuleService $commodityRuleService;

    public function __construct()
    {
        $this->regularRateService   = new RegularRateService();
        $this->commodityRuleService = new CommodityRuleService();
    }

    public function calculate(array $data): array
    {
        //-------------------------------------
        // INPUT
        //-------------------------------------
        $customerId       = (int) ($data['customer_id'] ?? 0);
        $countryId        = (int) ($data['country_id'] ?? 0);
        $serviceId        = (int) ($data['service_id'] ?? 0);
        $commodityTypeId  = (int) ($data['commodity_type_id'] ?? 0);

        $actualWeight     = (float) ($data['actual_weight'] ?? 0);
        $volumeWeight     = (float) ($data['volume_weight'] ?? 0);
        $chargeWeight     = max($actualWeight, $volumeWeight);

        // Validasi dasar
        if ($customerId <= 0) {
            throw new \RuntimeException('Customer ID tidak valid.');
        }
        if ($chargeWeight <= 0) {
            throw new \RuntimeException('Berat muat harus lebih dari 0.');
        }

        //-------------------------------------
        // AMBIL TARIF DARI MASTER
        //-------------------------------------

        dd([
    'customerId'   => $customerId,
    'countryId'    => $countryId,
    'serviceId'    => $serviceId,
    'chargeWeight' => $chargeWeight,
]);
        $rate = $this->regularRateService->calculate(
            $customerId,
            $countryId,
            $serviceId,
            $chargeWeight
        );

        $freight     = $rate['customer_freight'];
        $vendorCost  = $rate['vendor_freight'];

        //-------------------------------------
        // BIAYA BERDASARKAN JENIS BARANG
        //-------------------------------------
        $commodityCharge = $this->commodityRuleService->calculate(
            $commodityTypeId,
            $chargeWeight
        );

        //-------------------------------------
        // BIAYA TAMBAHAN & POTONGAN
        //-------------------------------------
        $insurance   = (float) ($data['insurance_amount'] ?? 0);
        $packing     = (float) ($data['packing_charge'] ?? 0);
        $additional  = (float) ($data['additional_charge'] ?? 0) + $commodityCharge;
        $discount    = (float) ($data['discount_amount'] ?? 0);

        //-------------------------------------
        // HITUNG TOTAL
        //-------------------------------------
        $customerTotal = $freight + $insurance + $packing + $additional - $discount;

        //-------------------------------------
        // HITUNG LABA & PERSENTASE
        //-------------------------------------
        $profit = $customerTotal - $vendorCost;
        $margin = $customerTotal > 0 ? round(($profit / $customerTotal) * 100, 2) : 0;
        $markup = $vendorCost > 0 ? round(($profit / $vendorCost) * 100, 2) : 0;

        //-------------------------------------
        // KEMBALIKAN HASIL LENGKAP
        //-------------------------------------
        return [
            'freight'         => $freight,
            'insurance'       => $insurance,
            'packing'         => $packing,
            'additional'      => $additional,
            'commodity'       => $commodityCharge,
            'discount'        => $discount,

            'customer_total'  => round($customerTotal, 2),
            'vendor_total'    => round($vendorCost, 2),
            'profit'          => round($profit, 2),
            'margin'          => $margin,
            'markup'          => $markup,

            'rate_info' => [
                'tariff_id'       => $rate['tariff_id'],
                'weight_from'     => $rate['weight_from'],
                'weight_to'       => $rate['weight_to'],
                'weight_range'    => $rate['weight_range'],
                'agent_type'      => $rate['agent_type'],
                'customer_price'  => $rate['customer_price'],
                'vendor_price'    => $rate['vendor_price'],
                'publish_price'   => $rate['publish_price']
            ],

            'pricing_breakdown' => [
                'freight'    => round($freight, 2),
                'insurance'  => round($insurance, 2),
                'packing'    => round($packing, 2),
                'commodity'  => round($commodityCharge, 2),
                'additional' => round($additional, 2),
                'discount'   => round($discount, 2),
                'total'      => round($customerTotal, 2)
            ],

            'vendor_breakdown' => [
                'freight' => round($vendorCost, 2),
                'total'   => round($vendorCost, 2)
            ]
        ];
    }
}