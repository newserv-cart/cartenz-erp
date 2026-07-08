<?php

namespace App\Libraries\Pricing;

use App\Services\ExpressRateService;
use App\Services\RemoteService;
use App\Services\CommodityRuleService;

class ExpressPricingEngine
{
    protected ExpressRateService $expressRateService;
    protected RemoteService $remoteService;
    protected CommodityRuleService $commodityRuleService;

    public function __construct()
    {
        // Inisialisasi semua service yang dibutuhkan
        $this->expressRateService   = new ExpressRateService();
        $this->remoteService        = new RemoteService();
        $this->commodityRuleService = new CommodityRuleService();
    }

    public function calculate(array $shipment)
    {
        $result = [
            'express_tariff'      => 0,
            'fuel'                => 0,
            'gogreen'             => 0,
            'remote'              => 0,
            'commodity_charge'    => 0,
            'insurance'           => (float)($shipment['insurance_amount'] ?? 0),
            'packing'             => (float)($shipment['packing_charge'] ?? 0),
            'additional_charge'   => (float)($shipment['additional_charge'] ?? 0),
            'discount'            => (float)($shipment['discount_amount'] ?? 0),
            'vendor_cost'         => 0,
            'customer_charge'     => 0,
            'profit'              => 0,
            'recommended_vendor'  => null,
        ];

        /**
         * STEP 1: Express Tariff
         */
        $headerId       = (int)($shipment['express_header_id'] ?? 0);
        $shipmentType   = $shipment['shipment_type'] ?? 'DOC';
        $zone           = (int)($shipment['zone'] ?? 0);
        $chargeableWt   = (float)($shipment['chargeable_weight'] ?? 0);

        if ($headerId > 0 && $zone > 0) {
            $result['express_tariff'] = $this->expressRateService->getPrice(
                $headerId,
                $shipmentType,
                $zone,
                $chargeableWt
            );
            // Asumsikan tarif untuk vendor sama dengan tarif dasar (bisa disesuaikan nanti)
            $result['vendor_cost'] = $result['express_tariff'];
            // Ambil vendor yang dipakai
            $result['recommended_vendor'] = (int)($shipment['vendor_id'] ?? null);
        }

        /**
         * STEP 2: Fuel Surcharge
         * Contoh: 5% dari tarif express
         */
        $fuelPercent = 5; // Bisa ambil dari konfigurasi nanti
        $result['fuel'] = round(($result['express_tariff'] * $fuelPercent) / 100, 2);

        /**
         * STEP 3: Go Green
         * Contoh: Tetap Rp 2.500 per kiriman
         */
        $result['gogreen'] = 2500; // Bisa diatur di master biaya nanti

        /**
         * STEP 4: Remote Area
         */
        $remote = $this->remoteService->calculate(
            (int)($shipment['country_id'] ?? 0),
            $shipment['receiver_city'] ?? '',
            $shipment['receiver_postcode'] ?? ''
        );
        $result['remote'] = (float)($remote['amount'] ?? 0);
        // Tambah biaya remote ke vendor cost juga
        $result['vendor_cost'] += (float)($remote['vendor_amount'] ?? 0);

        /**
         * STEP 5: Commodity Charge
         */
        $commodity = $this->commodityRuleService->calculate(
            (int)($shipment['commodity_type_id'] ?? 0),
            $chargeableWt
        );
        $result['commodity_charge'] = (float)($commodity['amount'] ?? 0);

        /**
         * STEP 6: Insurance
         * Sudah diisi dari input di atas
         */

        /**
         * STEP 7: Packing
         * Sudah diisi dari input di atas
         */

        /**
         * STEP 8: Additional Charge
         * Sudah diisi dari input di atas
         */

        /**
         * STEP 9: Discount
         * Sudah diisi dari input di atas
         */

        /**
         * STEP 10: Hitung Total & Profit
         */
        $result['customer_charge'] =
            $result['express_tariff']
            + $result['fuel']
            + $result['gogreen']
            + $result['remote']
            + $result['commodity_charge']
            + $result['insurance']
            + $result['packing']
            + $result['additional_charge']
            - $result['discount'];

        $result['profit'] = $result['customer_charge'] - $result['vendor_cost'];

        // Kembalikan hasil dalam format yang konsisten
        return new class($result) {
            public function __construct($data) { $this->data = $data; }
            public function summary() { return $this->data; }
            public function items() { return []; }
        };
    }
}