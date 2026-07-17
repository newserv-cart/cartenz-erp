<?php

namespace App\Services;

use App\Models\ExpressRateDetailModel;
use App\Models\ExpressRateHeaderModel;

class ExpressRateService
{
    protected ExpressRateHeaderModel $headerModel;
    protected ExpressRateDetailModel $detailModel;

    public function __construct()
    {
        $this->headerModel = new ExpressRateHeaderModel();
        $this->detailModel = new ExpressRateDetailModel();
    }

    public function getRate(
        int $vendorId,
        int $serviceId,
        string $shipmentType,
        int $zone,
        float $weight,
        ?string $effectiveDate = null,
        ?int $currencyId = null
    ): array {
        $chargeWeight  = $this->normalizeWeight($weight);
        $effectiveDate = $effectiveDate ?: date('Y-m-d');
        $shipmentType  = strtoupper(trim($shipmentType));

        $builder = $this->headerModel
            ->where('vendor_id', $vendorId)
            ->where('service_id', $serviceId)
            ->where('effective_date <=', $effectiveDate)
            ->orderBy('effective_date', 'DESC')
            ->orderBy('id', 'DESC');

        if ($currencyId !== null && $currencyId > 0) {
            $builder->where('currency_id', $currencyId);
        }

        $header = $builder->first();

        if (!$header) {
            return $this->notFound(
                $chargeWeight,
                $zone,
                'Express Rate Header tidak ditemukan.'
            );
        }

        // Billing must never round the rate breakpoint downward.
        $rate = $this->detailModel
            ->where('header_id', (int) $header['id'])
            ->where('shipment_type', $shipmentType)
            ->where('zone', $zone)
            ->where('weight >=', $chargeWeight)
            ->orderBy('weight', 'ASC')
            ->orderBy('id', 'ASC')
            ->first();

        if (!$rate) {
            return $this->notFound(
                $chargeWeight,
                $zone,
                'Tarif Express untuk berat tersebut tidak tersedia.',
                (int) $header['id']
            );
        }

        return [
            'found'          => true,
            'price'          => round((float) $rate['price'], 2),
            'charge_weight'  => $chargeWeight,
            'rate_weight'    => (float) $rate['weight'],
            'zone'           => $zone,
            'header_id'      => (int) $header['id'],
            'detail_id'      => (int) $rate['id'],
            'currency_id'    => isset($header['currency_id'])
                ? (int) $header['currency_id']
                : null,
            'shipment_type'  => $shipmentType,
            'effective_date' => $header['effective_date'],
            'remarks'        => $header['remarks'] ?? null,
        ];
    }

    public function normalizeWeight(float $weight): float
    {
        $weight = max(0, $weight);

        return $weight <= 30
            ? ceil($weight * 2) / 2
            : ceil($weight);
    }

    private function notFound(
        float $chargeWeight,
        int $zone,
        string $message,
        ?int $headerId = null
    ): array {
        return [
            'found'         => false,
            'price'         => 0.0,
            'charge_weight' => $chargeWeight,
            'rate_weight'   => null,
            'zone'          => $zone,
            'header_id'     => $headerId,
            'detail_id'     => null,
            'currency_id'   => null,
            'remarks'       => $message,
        ];
    }
}
