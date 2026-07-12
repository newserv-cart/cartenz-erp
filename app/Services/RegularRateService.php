<?php

namespace App\Services;

use App\Models\CustomerModel;
use App\Models\RegularTariffModel;
use RuntimeException;

class RegularRateService
{
    protected RegularTariffModel $tariffModel;
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->tariffModel  = new RegularTariffModel();
        $this->customerModel = new CustomerModel();
    }

    public function calculate(
        int $customerId,
        int $countryId,
        int $serviceId,
        float $chargeWeight
    ): array {
        $customer = $this->customerModel->find($customerId);

        if (!$customer) {
            throw new RuntimeException('Customer tidak ditemukan.');
        }

$customerCategory = strtoupper(
    (string) ($customer['customer_category'] ?? 'PERSONAL')
);

$agentType = match ($customerCategory) {
    'CORPORATE' => 'B',
    'AGENT'     => 'C',
    'PUBLISH'   => 'PUBLISH',
    default     => 'A',
};
        $today = date('Y-m-d');

        $tariff = $this->tariffModel
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('weight_from <=', $chargeWeight)
            ->where('weight_to >=', $chargeWeight)
            ->where('status', 1)
            ->groupStart()
                ->where('effective_from IS NULL', null, false)
                ->orWhere('effective_from <=', $today)
            ->groupEnd()
            ->groupStart()
                ->where('effective_until IS NULL', null, false)
                ->orWhere('effective_until >=', $today)
            ->groupEnd()
            ->orderBy('weight_from', 'DESC')
            ->first();

        if (!$tariff) {
            return [
                'tariff_id'        => 0,
                'weight_from'      => 0,
                'weight_to'        => 0,
                'weight_range'     => '0 - 0',
                'agent_type'       => $agentType,
                'customer_price'   => 0,
                'vendor_price'     => 0,
                'publish_price'    => 0,
                'customer_freight' => 0,
                'vendor_freight'   => 0,
            ];
        }

        $priceColumn = match ($agentType) {
            'B'     => 'agent_b_price',
            'C'     => 'agent_c_price',
            'PUBLISH' => 'publish_price',
            default => 'agent_a_price',
        };

        $customerPrice = (float) ($tariff[$priceColumn] ?? 0);
        $vendorPrice   = (float) ($tariff['vendor_cost'] ?? 0);

        return [
            'tariff_id'        => (int) $tariff['id'],
            'weight_from'      => (float) $tariff['weight_from'],
            'weight_to'        => (float) $tariff['weight_to'],
            'weight_range'     => $tariff['weight_from'] . ' - ' . $tariff['weight_to'],
            'agent_type'       => $agentType,
            'customer_price'   => $customerPrice,
            'vendor_price'     => $vendorPrice,
            'publish_price'    => (float) ($tariff['publish_price'] ?? 0),
            'customer_freight' => $customerPrice,
            'vendor_freight'   => $vendorPrice,
        ];
    }
}