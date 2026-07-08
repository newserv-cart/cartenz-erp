<?php

namespace App\Libraries\Pricing;

use App\Services\RegularRateService;
use App\Services\CommodityRuleService;

class RegularPricingEngine
{
    public function calculate(array $shipment)
    {
        // Buat wadah biaya baru
        $cost = new CostBreakdown();

        // ==============================================
        // STEP 1: Tarif Dasar
        // ==============================================
        $rate = (new RegularRateService())->calculate(
            (int)$shipment['country_id'],
            (int)$shipment['service_id'],
            (int)$shipment['transport_mode_id'],
            (float)$shipment['chargeable_weight'],
            $shipment['agent_type'] ?? 'A'
        );

        if ($rate) {
            // Biaya untuk pelanggan
            $cost->add(
                'CUSTOMER',
                'FREIGHT',
                'Tarif Pengiriman',
                (float)$rate['total'],
                $rate['tariff'] ?? []
            );

            // Biaya untuk vendor
            $cost->add(
                'VENDOR',
                'FREIGHT',
                'Biaya Vendor',
                (float)$rate['vendor_total'],
                $rate['tariff'] ?? []
            );
        }

        // ==============================================
        // STEP 2: Biaya Jenis Barang
        // ==============================================
        $commodity = (new CommodityRuleService())->calculate(
            (int)$shipment['commodity_type_id'],
            (float)$shipment['chargeable_weight']
        );

        if ($commodity['amount'] > 0) {
            $cost->add(
                'CUSTOMER',
                'COMMODITY',
                'Biaya Jenis Barang',
                (float)$commodity['amount'],
                $commodity['rule'] ?? []
            );
        }

        // ==============================================
        // STEP 3: Asuransi
        // ==============================================
        $insurance = (float)($shipment['insurance_amount'] ?? 0);
        if ($insurance > 0) {
            $cost->add(
                'CUSTOMER',
                'INSURANCE',
                'Biaya Asuransi',
                $insurance
            );
        }

        // ==============================================
        // STEP 4: Biaya Packing
        // ==============================================
        $packing = (float)($shipment['packing_charge'] ?? 0);
        if ($packing > 0) {
            $cost->add(
                'CUSTOMER',
                'PACKING',
                'Biaya Packing',
                $packing
            );
        }

        // ==============================================
        // STEP 5: Biaya Tambahan
        // ==============================================
        $additional = (float)($shipment['additional_charge'] ?? 0);
        if ($additional > 0) {
            $cost->add(
                'CUSTOMER',
                'ADDITIONAL',
                'Biaya Tambahan',
                $additional
            );
        }

        // ==============================================
        // STEP 6: Potongan Harga
        // ==============================================
        $discount = (float)($shipment['discount'] ?? 0);
        if ($discount > 0) {
            $cost->add(
                'CUSTOMER',
                'DISCOUNT',
                'Potongan Harga',
                -$discount // Nilai negatif karena mengurangi total
            );
        }

        // ==============================================
        // Urutkan sesuai urutan yang sudah ditentukan ✅
        // ==============================================
        $cost->sort();

        // Kembalikan objek yang sudah sesuai standar
        return $cost;
    }
}