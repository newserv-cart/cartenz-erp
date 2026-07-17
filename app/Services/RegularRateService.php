<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\RegularTariffModel;
use DateTimeImmutable;
use RuntimeException;

class RegularRateService
{
    protected RegularTariffModel $tariffModel;
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->tariffModel   = new RegularTariffModel();
        $this->customerModel = new CustomerModel();
    }

    public function calculate(
        int $customerId,
        int $countryId,
        int $serviceId,
        float $chargeWeight,
        ?string $pricingDate = null
    ): array {
        if ($customerId <= 0) {
            throw new RuntimeException('Customer ID tidak valid.');
        }

        if ($countryId <= 0 || $serviceId <= 0) {
            throw new RuntimeException(
                'Country dan Service wajib dipilih.'
            );
        }

        if ($chargeWeight <= 0) {
            throw new RuntimeException(
                'Chargeable Weight harus lebih dari 0 Kg.'
            );
        }

        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            throw new RuntimeException('Customer tidak ditemukan.');
        }

        $customerCategory = strtoupper(
            trim(
                (string) (
                    $customer['customer_category']
                    ?? 'PERSONAL'
                )
            )
        );

        $agentType = match ($customerCategory) {
            'CORPORATE' => 'B',
            'AGENT'     => 'C',
            'PUBLISH'   => 'PUBLISH',
            default     => 'A',
        };

        $pricingDate = $this->normalizePricingDate(
            $pricingDate
        );

        $tariff = $this->tariffModel->findApplicableRate(
            $countryId,
            $serviceId,
            $chargeWeight,
            $pricingDate
        );

        if (!$tariff) {
            return $this->emptyResult(
                $agentType,
                $pricingDate,
                $chargeWeight
            );
        }

        $priceColumn = match ($agentType) {
            'B'       => 'agent_b_price',
            'C'       => 'agent_c_price',
            'PUBLISH' => 'publish_price',
            default   => 'agent_a_price',
        };

        $customerPricePerKg = (float) (
            $tariff[$priceColumn] ?? 0
        );

        $vendorPricePerKg = (float) (
            $tariff['vendor_cost'] ?? 0
        );

        /*
         * The values stored in agent_a_price / agent_b_price /
         * agent_c_price / publish_price / vendor_cost are rates PER KG.
         * The old code returned the per-kg rate directly as freight,
         * which made a 25 Kg shipment with Rp65.000/Kg cost only
         * Rp65.000. Freight must be rate x chargeable weight.
         */
        $customerFreight = $this->calculateFreightTotal(
            $customerPricePerKg,
            $chargeWeight
        );

        $vendorFreight = $this->calculateFreightTotal(
            $vendorPricePerKg,
            $chargeWeight
        );

        return [
            'tariff_id'          => (int) $tariff['id'],
            'weight_from'        => (float) $tariff['weight_from'],
            'weight_to'          => (float) $tariff['weight_to'],
            'weight_range'       => $tariff['weight_from'] .
                ' - ' . $tariff['weight_to'],
            'chargeable_weight'  => round($chargeWeight, 2),
            'pricing_date'       => $pricingDate,
            'agent_type'         => $agentType,
            'price_column'       => $priceColumn,

            // Backward-compatible names: these remain rates per kg.
            'customer_price'     => $customerPricePerKg,
            'vendor_price'       => $vendorPricePerKg,
            'publish_price'      => (float) (
                $tariff['publish_price'] ?? 0
            ),

            // Explicit names for UI/API clarity.
            'customer_price_per_kg' => $customerPricePerKg,
            'vendor_price_per_kg'   => $vendorPricePerKg,
            'customer_freight'      => $customerFreight,
            'vendor_freight'        => $vendorFreight,
            'formula'               => 'rate_per_kg_x_chargeable_weight',
        ];
    }

    private function calculateFreightTotal(
        float $ratePerKg,
        float $chargeWeight
    ): float {
        return round(
            max(0, $ratePerKg) * max(0, $chargeWeight),
            2
        );
    }

    private function normalizePricingDate(
        ?string $pricingDate
    ): string {
        $pricingDate = trim((string) $pricingDate);

        if ($pricingDate === '') {
            return date('Y-m-d');
        }

        $date = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $pricingDate
        );

        if (
            !$date
            || $date->format('Y-m-d') !== $pricingDate
        ) {
            throw new RuntimeException(
                'Tanggal pricing tidak valid.'
            );
        }

        return $pricingDate;
    }

    private function emptyResult(
        string $agentType,
        string $pricingDate,
        float $chargeWeight
    ): array {
        return [
            'tariff_id'              => 0,
            'weight_from'            => 0,
            'weight_to'              => 0,
            'weight_range'           => '0 - 0',
            'chargeable_weight'      => round($chargeWeight, 2),
            'pricing_date'           => $pricingDate,
            'agent_type'             => $agentType,
            'price_column'           => null,
            'customer_price'         => 0,
            'vendor_price'           => 0,
            'publish_price'          => 0,
            'customer_price_per_kg'  => 0,
            'vendor_price_per_kg'    => 0,
            'customer_freight'       => 0,
            'vendor_freight'         => 0,
            'formula'                =>
                'rate_per_kg_x_chargeable_weight',
        ];
    }
}
