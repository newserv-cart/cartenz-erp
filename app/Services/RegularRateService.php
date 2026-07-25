<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\RegularTariffModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use DateTimeImmutable;
use RuntimeException;

class RegularRateService
{
    protected RegularTariffModel $tariffModel;
    protected CustomerModel $customerModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->tariffModel   = new RegularTariffModel();
        $this->customerModel = new CustomerModel();
        $this->db            = Database::connect();
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

        return $this->doCalculateRate(
            $agentType,
            $countryId,
            $serviceId,
            $chargeWeight,
            $pricingDate
        );
    }

    public function calculateByOwner(array $data): array
    {
        $ownerType = strtoupper(
            trim(
                (string) ($data['owner_type'] ?? '')
            )
        );

        $ownerId = (int) ($data['owner_id'] ?? 0);
        $agentId = (int) ($data['agent_id'] ?? 0);
        $customerId = (int) ($data['customer_id'] ?? 0);

        $countryId = (int) ($data['country_id'] ?? 0);
        $serviceId = (int) ($data['service_id'] ?? 0);

        $chargeWeight = (float) (
            $data['chargeable_weight'] ?? 0
        );

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

        if ($ownerType === 'AGENT' || $agentId > 0) {
            $agentId = $agentId > 0 ? $agentId : $ownerId;

            if ($agentId <= 0) {
                throw new RuntimeException(
                    'Agent ID tidak valid.'
                );
            }

            $agentRecord = $this->db
                ->table('agents AS a')
                ->select(
                    'a.*, agent_types.agent_type_name'
                )
                ->join(
                    'agent_types',
                    'agent_types.id = a.agent_type_id',
                    'left'
                )
                ->where('a.id', $agentId)
                ->where('a.status', 1)
                ->get()
                ->getRowArray();

            if (!$agentRecord) {
                throw new RuntimeException(
                    'Agent tidak ditemukan atau tidak aktif.'
                );
            }

            $agentTypeName = strtoupper(
                trim(
                    (string) (
                        $agentRecord['agent_type_name']
                        ?? 'A'
                    )
                )
            );

            $agentType = match ($agentTypeName) {
                'A', 'AGENT A' => 'A',
                'B', 'AGENT B' => 'B',
                'C', 'AGENT C' => 'C',
                default        => 'A',
            };

            return $this->doCalculateRate(
                $agentType,
                $countryId,
                $serviceId,
                $chargeWeight,
                $data['shipment_date'] ?? null
            );
        }

        if ($ownerType === 'CUSTOMER' || $customerId > 0) {
            $customerId = $customerId > 0
                ? $customerId
                : $ownerId;

            if ($customerId <= 0) {
                throw new RuntimeException(
                    'Customer ID tidak valid.'
                );
            }

            return $this->calculate(
                $customerId,
                $countryId,
                $serviceId,
                $chargeWeight,
                $data['shipment_date'] ?? null
            );
        }

        throw new RuntimeException(
            'Owner type tidak dikenal untuk perhitungan tarif.'
        );
    }

    private function doCalculateRate(
        string $agentType,
        int $countryId,
        int $serviceId,
        float $chargeWeight,
        ?string $pricingDate
    ): array {
        $rawChargeableWeight = (float) $chargeWeight;

        $billingWeight = max(
            1,
            (float) ceil($rawChargeableWeight)
        );

        $pricingDate = $this->normalizePricingDate($pricingDate);

        $tariff = $this->tariffModel->findApplicableRate(
            $countryId,
            $serviceId,
            $billingWeight,
            $pricingDate
        );

        if (!$tariff) {
            return $this->emptyResult(
                $agentType,
                $pricingDate,
                $rawChargeableWeight,
                $billingWeight
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

        $customerFreight = $this->calculateFreightTotal(
            $customerPricePerKg,
            $billingWeight
        );

        $vendorFreight = $this->calculateFreightTotal(
            $vendorPricePerKg,
            $billingWeight
        );

        return [
            'tariff_id'          => (int) $tariff['id'],
            'weight_from'        => (float) $tariff['weight_from'],
            'weight_to'          => (float) $tariff['weight_to'],
            'weight_range'       => $tariff['weight_from'] .
                ' - ' . $tariff['weight_to'],
            'chargeable_weight'  => round(
                $rawChargeableWeight,
                2
            ),
            'billing_weight'     => $billingWeight,
            'invoice_weight'     => $billingWeight,
            'pricing_date'       => $pricingDate,
            'agent_type'         => $agentType,
            'price_column'       => $priceColumn,

            'customer_price'     => $customerPricePerKg,
            'vendor_price'       => $vendorPricePerKg,
            'publish_price'      => (float) (
                $tariff['publish_price'] ?? 0
            ),

            'customer_price_per_kg' => $customerPricePerKg,
            'vendor_price_per_kg'   => $vendorPricePerKg,
            'customer_freight'      => $customerFreight,
            'vendor_freight'        => $vendorFreight,
            'formula'               => 'rate_per_kg_x_billing_weight',
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
        float $rawChargeableWeight,
        float $billingWeight = 0
    ): array {
        return [
            'tariff_id'              => 0,
            'weight_from'            => 0,
            'weight_to'              => 0,
            'weight_range'           => '0 - 0',
            'chargeable_weight'      => round(
                $rawChargeableWeight,
                2
            ),
            'billing_weight'         => $billingWeight,
            'invoice_weight'         => $billingWeight,
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
                'rate_per_kg_x_billing_weight',
        ];
    }
}
