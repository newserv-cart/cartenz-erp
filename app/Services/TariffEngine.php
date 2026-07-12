<?php

namespace App\Services;

use RuntimeException;
use Throwable;

class TariffEngine
{
    private RegularCalculationService $regularCalculator;

    public function __construct()
    {
        $this->regularCalculator = new RegularCalculationService();
    }

    public function getBaseTariff(array $shipment): array
    {
        $chargeableWeight = (float) (
            $shipment['chargeable_weight']
            ?? max(
                (float) ($shipment['actual_weight'] ?? 0),
                (float) ($shipment['volume_weight'] ?? 0)
            )
        );

        if ($chargeableWeight <= 0) {
            throw new RuntimeException(
                'Chargeable weight harus lebih dari 0.'
            );
        }

        $serviceEngine = strtoupper(
            (string) ($shipment['service_engine'] ?? 'REGULAR')
        );

        if ($serviceEngine === 'EXPRESS') {
            return $this->getExpressTariff($shipment);
        }

        return $this->getRegularTariff(
            $shipment,
            $chargeableWeight
        );
    }

    private function getRegularTariff(
        array $shipment,
        float $chargeableWeight
    ): array {
        try {
            $result = $this->regularCalculator->calculate([
                'customer_id'      => (int) ($shipment['customer_id'] ?? 0),
                'country_id'       => (int) ($shipment['country_id'] ?? 0),
                'service_id'       => (int) ($shipment['service_id'] ?? 0),
                'commodity_type_id'=> (int) (
                    $shipment['commodity_type_id'] ?? 0
                ),

                // Supaya charge weight yang sudah dihitung tetap dipakai
                'actual_weight'    => $chargeableWeight,
                'volume_weight'    => 0,

                // Charge manual lama sementara tetap diteruskan
                'insurance_amount' => (float) (
                    $shipment['insurance_amount'] ?? 0
                ),
                'packing_charge'   => (float) (
                    $shipment['packing_charge'] ?? 0
                ),
                'additional_charge'=> (float) (
                    $shipment['additional_charge'] ?? 0
                ),
                'discount_amount'  => (float) (
                    $shipment['discount_amount'] ?? 0
                ),
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Regular tariff gagal dihitung: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return [
            'engine'        => 'REGULAR',
            'selling_rate'  => (float) ($result['freight'] ?? 0),
            'vendor_rate'   => (float) ($result['vendor_total'] ?? 0),

            'weight'        => $chargeableWeight,
            'service_id'    => (int) ($shipment['service_id'] ?? 0),
            'country_id'    => (int) ($shipment['country_id'] ?? 0),
            'customer_id'   => (int) ($shipment['customer_id'] ?? 0),

            'rate_info'     => $result['rate_info'] ?? [],
            'source_result' => $result,
        ];
    }

    private function getExpressTariff(array $shipment): array
    {
        throw new RuntimeException(
            'Express Tariff Engine belum diaktifkan.'
        );
    }
}