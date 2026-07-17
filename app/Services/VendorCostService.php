<?php

namespace App\Services;

use App\Models\ExpressRateHeaderModel;
use App\Models\VendorRateModel;

class VendorCostService
{
    protected VendorRateModel $regularRateModel;
    protected ExpressRateHeaderModel $expressHeaderModel;
    protected ExpressRateService $expressRateService;

    public function __construct()
    {
        $this->regularRateModel  = new VendorRateModel();
        $this->expressHeaderModel = new ExpressRateHeaderModel();
        $this->expressRateService = new ExpressRateService();
    }

    public function calculateForVendor(
        array $shipment,
        int $vendorId
    ): ?array {
        if ($vendorId <= 0) {
            return null;
        }

        $engine = strtoupper(
            (string) (
                $shipment['pricing_engine']
                ?? $shipment['service_code']
                ?? 'REGULAR'
            )
        );

        return $engine === 'EXPRESS'
            ? $this->calculateExpress($shipment, $vendorId)
            : $this->calculateRegular($shipment, $vendorId);
    }

    private function calculateRegular(
        array $shipment,
        int $vendorId
    ): ?array {
        $weight = round(
            max(0, (float) ($shipment['chargeable_weight'] ?? 0)),
            2
        );

        if ($weight <= 0) {
            return null;
        }

        $effectiveDate = $this->effectiveDate($shipment);
        $idrCurrencyId = $this->idrCurrencyId();

        $builder = $this->regularRateModel
            ->where('vendor_id', $vendorId)
            ->where('country_id', (int) ($shipment['country_id'] ?? 0))
            ->where('service_id', (int) ($shipment['service_id'] ?? 0))
            ->where(
                'transport_mode_id',
                (int) ($shipment['transport_mode_id'] ?? 0)
            )
            ->where(
                'delivery_type_id',
                (int) ($shipment['delivery_type_id'] ?? 0)
            )
            ->where('weight_from <=', $weight)
            ->where('weight_to >=', $weight)
            ->where('status', 1)
            ->groupStart()
                ->where('effective_from IS NULL', null, false)
                ->orWhere('effective_from <=', $effectiveDate)
            ->groupEnd()
            ->groupStart()
                ->where('effective_until IS NULL', null, false)
                ->orWhere('effective_until >=', $effectiveDate)
            ->groupEnd()
            ->orderBy('effective_from', 'DESC')
            ->orderBy('weight_from', 'DESC')
            ->orderBy('id', 'DESC');

        if ($idrCurrencyId > 0) {
            $builder->groupStart()
                ->where('currency_id', $idrCurrencyId)
                ->orWhere('currency_id IS NULL', null, false)
            ->groupEnd();
        }

        $rate = $builder->first();

        if (!$rate) {
            return null;
        }

        $costPerKg   = max(0, (float) ($rate['cost_per_kg'] ?? 0));
        $minimum     = max(0, (float) ($rate['minimum_charge'] ?? 0));
        $freight     = round(max($weight * $costPerKg, $minimum), 2);
        $currencyId  = (int) ($rate['currency_id'] ?? $idrCurrencyId);

        return [
            'vendor_id'          => $vendorId,
            'rate_source'        => 'REGULAR_VENDOR_RATE',
            'rate_reference_id'  => (int) $rate['id'],
            'currency_id'        => $currencyId > 0 ? $currencyId : null,
            'chargeable_weight'  => $weight,
            'rate_weight'        => $weight,
            'rate_effective_date'=> $rate['effective_from'] ?? $effectiveDate,
            'vendor_cost'        => $freight,
            'breakdown'          => [
                'cost_per_kg'   => $costPerKg,
                'minimum_charge'=> $minimum,
                'calculated'    => round($weight * $costPerKg, 2),
                'total'         => $freight,
            ],
        ];
    }

    private function calculateExpress(
        array $shipment,
        int $vendorId
    ): ?array {
        $weight = max(0, (float) ($shipment['chargeable_weight'] ?? 0));

        if ($weight <= 0) {
            return null;
        }

        $zone = $this->resolveExpressZone(
            (int) ($shipment['express_zone_id'] ?? 0)
        );

        if ($zone <= 0) {
            return null;
        }

        $shipmentType = $this->resolveShipmentType($shipment);
        $idrCurrencyId = $this->idrCurrencyId();

        $rate = $this->expressRateService->getRate(
            $vendorId,
            (int) ($shipment['service_id'] ?? 0),
            $shipmentType,
            $zone,
            $weight,
            $this->effectiveDate($shipment),
            $idrCurrencyId > 0 ? $idrCurrencyId : null
        );

        if (empty($rate['found'])) {
            return null;
        }

        return [
            'vendor_id'           => $vendorId,
            'rate_source'         => 'EXPRESS_RATE_DETAIL',
            'rate_reference_id'   => (int) $rate['detail_id'],
            'rate_header_id'      => (int) $rate['header_id'],
            'currency_id'         => $rate['currency_id'],
            'chargeable_weight'   => (float) $rate['charge_weight'],
            'rate_weight'         => (float) $rate['rate_weight'],
            'rate_effective_date' => $rate['effective_date'],
            'vendor_cost'         => round((float) $rate['price'], 2),
            'breakdown'           => [
                'shipment_type' => $shipmentType,
                'zone'          => $zone,
                'rate_weight'   => (float) $rate['rate_weight'],
                'total'         => round((float) $rate['price'], 2),
            ],
        ];
    }

    private function resolveExpressZone(int $zoneId): int
    {
        if ($zoneId <= 0) {
            return 0;
        }

        $zone = db_connect()
            ->table('express_zones')
            ->where('id', $zoneId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (!$zone) {
            return $zoneId;
        }

        $digits = preg_replace(
            '/\D+/',
            '',
            (string) ($zone['zone_code'] ?? '')
        );

        return $digits !== '' ? (int) $digits : $zoneId;
    }

    private function resolveShipmentType(array $shipment): string
    {
        $name = strtoupper(
            (string) ($shipment['package_type_name'] ?? '')
        );

        return str_contains($name, 'DOCUMENT')
            ? 'DOCUMENT'
            : 'PACKAGE';
    }

    private function effectiveDate(array $shipment): string
    {
        $date = trim((string) ($shipment['shipment_date'] ?? ''));

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            ? $date
            : date('Y-m-d');
    }

    private function idrCurrencyId(): int
    {
        $row = db_connect()
            ->table('currencies')
            ->select('id')
            ->where('currency_code', 'IDR')
            ->limit(1)
            ->get()
            ->getRowArray();

        return (int) ($row['id'] ?? 0);
    }
}
