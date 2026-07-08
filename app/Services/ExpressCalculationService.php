<?php

namespace App\Services;

use App\Libraries\Pricing\PricingEngine;
use App\Models\ShipmentVendorRecommendationModel;
use App\Models\VendorModel;

class ExpressCalculationService
{
    protected ExpressRateService $expressRateService;
    protected CommodityRuleService $commodityRuleService;
    protected VendorCostService $vendorCostService;
    protected PricingEngine $pricing;
    protected ShipmentVendorRecommendationModel $recommendationModel;
    protected VendorModel $vendorModel;

    public function __construct()
    {
        $this->expressRateService   = new ExpressRateService();
        $this->commodityRuleService = new CommodityRuleService();
        $this->vendorCostService    = new VendorCostService();
        $this->pricing              = new PricingEngine();
        $this->recommendationModel  = new ShipmentVendorRecommendationModel();
        $this->vendorModel          = new VendorModel();
    }

    public function calculate(array $data): array
    {
        $shipmentId         = $data['id'] ?? 0;
        $serviceId          = (int)($data['service_id'] ?? 0);
        $countryId          = (int)($data['country_id'] ?? 0);
        $vendorId           = (int)($data['vendor_id'] ?? 0);
        $commodityTypeId    = (int)($data['commodity_type_id'] ?? 0);
        $shipmentType       = strtoupper($data['shipment_type'] ?? 'PACKAGE');
        $zone               = (int)($data['zone'] ?? 1);
        $actualWeight       = (float)($data['actual_weight'] ?? 0);
        $volumeWeight       = (float)($data['volume_weight'] ?? 0);
        $insuranceAmount    = (float)($data['insurance_amount'] ?? 0);
        $discount           = (float)($data['discount'] ?? 0);

        $chargeableWeightRaw = max($actualWeight, $volumeWeight);
        $chargeableWeight    = $this->normalizeWeight($chargeableWeightRaw);

        // Get EXPRESS rate
        $rateInfo = $this->expressRateService->getRate(
            $vendorId,
            $serviceId,
            $shipmentType,
            $zone,
            $chargeableWeight
        );
        $freight = $rateInfo['price'] ?? 0;

        // Rest of logic is identical to RegularCalculationService
        $vendorCost = $this->vendorCostService->calculate([
            'vendor_id'         => $vendorId,
            'service_id'        => $serviceId,
            'shipment_type'     => $shipmentType,
            'zone'              => $zone,
            'chargeable_weight' => $chargeableWeight,
            'freight'           => $freight
        ]);

        $fuel       = $vendorCost['fuel'] ?? 0;
        $remote     = $vendorCost['remote'] ?? 0;
        $goGreen    = $vendorCost['go_green'] ?? 0;
        $handling   = $vendorCost['handling'] ?? 0;
        $vat        = $vendorCost['vat'] ?? 0;
        $vendorTotal = $this->calculateVendorTotal($vendorCost, $freight);

        $commodityCharge = $this->commodityRuleService->calculate($commodityTypeId, $chargeableWeight);

        $customerCost = $this->pricing->calculate([
            'freight'           => $freight,
            'fuel'              => $fuel,
            'remote'            => $remote,
            'go_green'          => $goGreen,
            'commodity'         => $commodityCharge,
            'insurance_amount'  => $insuranceAmount,
            'discount'          => $discount,
            'chargeable_weight' => $chargeableWeight
        ]);

        $packing        = $customerCost['packing'] ?? 0;
        $insurance      = $customerCost['insurance'] ?? 0;
        $additional     = ($customerCost['additional'] ?? 0) + $commodityCharge;
        $subtotal       = $customerCost['subtotal'] ?? 0;
        $tax            = $customerCost['tax'] ?? 0;
        $customerTotal  = $customerCost['grand_total'] ?? $customerCost['total'] ?? 0;

        $profit = $customerTotal - $vendorTotal;
        $margin = $customerTotal > 0 ? round(($profit / $customerTotal) * 100, 2) : 0;
        $markup = $vendorTotal > 0 ? round(($profit / $vendorTotal) * 100, 2) : 0;

        $recommendations = $this->getAllVendorCalculations([
            'service_id'        => $serviceId,
            'shipment_type'     => $shipmentType,
            'zone'              => $zone,
            'chargeable_weight' => $chargeableWeight,
            'customer_total'    => $customerTotal
        ]);

        if ($shipmentId > 0 && !empty($recommendations)) {
            $this->saveRecommendations($shipmentId, $recommendations, $customerTotal);
        }

        return [
            'charge_weight'     => $chargeableWeight,
            'warnings'          => $this->generateWarnings($rateInfo, $vendorTotal, $customerTotal, $profit, $margin),
            'rate_source'       => $rateInfo,
            'recommendation'    => $recommendations,

            'pricing_breakdown' => [
                'freight'    => $freight,
                'fuel'       => $fuel,
                'remote'     => $remote,
                'gogreen'    => $goGreen,
                'commodity'  => $commodityCharge,
                'insurance'  => $insurance,
                'packing'    => $packing,
                'additional' => $additional,
                'discount'   => $discount,
                'subtotal'   => $subtotal,
                'tax'        => $tax,
                'total'      => $customerTotal
            ],

            'vendor_breakdown' => [
                'freight'   => $freight,
                'fuel'      => $fuel,
                'remote'    => $remote,
                'gogreen'   => $goGreen,
                'handling'  => $handling,
                'vat'       => $vat,
                'total'     => $vendorTotal
            ],

            'freight'           => $freight,
            'fuel'              => $fuel,
            'remote'            => $remote,
            'gogreen'           => $goGreen,
            'packing'           => $packing,
            'insurance'         => $insurance,
            'additional'        => $additional,
            'discount'          => $discount,
            'subtotal'          => $subtotal,
            'tax'               => $tax,
            'customer_total'    => $customerTotal,
            'vendor_name'       => $rateInfo['vendor_name'] ?? '-',
            'vendor_total'      => $vendorTotal,
            'profit'            => $profit,
            'margin'            => $margin,
            'markup'            => $markup
        ];
    }

    // Include the same helper methods here or move them to a shared trait
    protected function normalizeWeight(float $weight): float
    {
        $weight = max(0, $weight);
        if ($weight <= 0) return 0;
        return $weight <= 30 ? ceil($weight * 2) / 2 : ceil($weight);
    }

    protected function calculateVendorTotal(array $vendorCost, float $freight): float
    {
        return $freight
            + ($vendorCost['fuel'] ?? 0)
            + ($vendorCost['remote'] ?? 0)
            + ($vendorCost['go_green'] ?? 0)
            + ($vendorCost['handling'] ?? 0)
            + ($vendorCost['vat'] ?? 0);
    }

    protected function generateWarnings(?array $rateInfo, float $vendorTotal, float $customerTotal, float $profit, float $margin): array
    {
        $warnings = [];
        if ($vendorTotal <= 0) $warnings[] = '❌ Vendor Cost Missing.';
        if ($customerTotal <= 0) $warnings[] = '❌ Customer Tariff Missing.';
        if (empty($rateInfo) || ($rateInfo['price'] ?? 0) <= 0) $warnings[] = '⚠️ Tarif tidak ditemukan.';
        if ($profit < 0) $warnings[] = '🚨 SHIPMENT LOSS: Cost > Selling Price.';
        if ($margin > 0 && $margin < 10) $warnings[] = '⚠️ Low Margin (<10%).';
        return $warnings;
    }

    protected function getAllVendorCalculations(array $data): array
    {
        $vendors = $this->vendorModel->where('status', 1)->findAll();
        $result = [];
        foreach ($vendors as $vendor) {
            $rate = $this->expressRateService->getRate(
                $vendor['id'],
                (int)$data['service_id'],
                strtoupper($data['shipment_type'] ?? 'PACKAGE'),
                (int)($data['zone'] ?? 1),
                (float)$data['chargeable_weight']
            );
            if (empty($rate) || ($rate['price'] ?? 0) <= 0) continue;

            $vendorCost = $this->vendorCostService->calculate([
                'vendor_id'         => $vendor['id'],
                'service_id'        => $data['service_id'],
                'shipment_type'     => $data['shipment_type'],
                'zone'              => $data['zone'],
                'chargeable_weight' => $data['chargeable_weight'],
                'freight'           => $rate['price']
            ]);

            $vendorTotal = $this->calculateVendorTotal($vendorCost, $rate['price']);
            $profit = (float)$data['customer_total'] - $vendorTotal;
            $margin = $data['customer_total'] > 0 ? round(($profit / $data['customer_total']) * 100, 2) : 0;
            $scoreData = $this->calculateVendorScoreAndReason($profit, $margin, $vendor);

            $result[] = [
                'vendor_id'   => $vendor['id'],
                'vendor_name' => $vendor['vendor_name'],
                'vendor_cost' => $vendorTotal,
                'profit'      => $profit,
                'margin'      => $margin,
                'score'       => $scoreData['score'],
                'reason'      => $scoreData['reason'],
                'star_rating' => $this->getStarRating($scoreData['score']),
                'rate'        => $rate
            ];
        }
        usort($result, fn($a, $b) => $b['score'] <=> $a['score']);
        return $result;
    }

    protected function calculateVendorScoreAndReason(float $profit, float $margin, array $vendor): array
    {
        $reason = [];
        $profitScore = min(($margin / 30) * 40, 40);
        if ($margin >= 20) $reason[] = 'Highest Profit';
        $slaScore = ((float)($vendor['sla_performance'] ?? 80) / 100) * 25;
        if ((float)($vendor['sla_performance'] ?? 0) >= 95) $reason[] = 'Fastest SLA';
        $claimScore = max(15 - ((float)($vendor['claim_ratio'] ?? 5) * 2), 0);
        if ((float)($vendor['claim_ratio'] ?? 0) <= 1) $reason[] = 'Low Claim Ratio';
        $successScore = ((float)($vendor['success_rate'] ?? 95) / 100) * 20;
        if ((float)($vendor['success_rate'] ?? 0) >= 98) $reason[] = 'High Success Rate';
        $totalScore = round($profitScore + $slaScore + $claimScore + $successScore, 1);
        return ['score' => max(0, min($totalScore, 100)), 'reason' => !empty($reason) ? implode(', ', $reason) : 'Standard Performance'];
    }

    protected function getStarRating(float $score): string
    {
        return match(true) {
            $score >= 95 => '⭐⭐⭐⭐⭐',
            $score >= 85 => '⭐⭐⭐⭐',
            $score >= 75 => '⭐⭐⭐',
            $score >= 65 => '⭐⭐',
            default      => '⭐'
        };
    }

    protected function saveRecommendations(int $shipmentId, array $recommendations, float $customerTotal): void
    {
        $this->recommendationModel->where('shipment_id', $shipmentId)->delete();
        foreach ($recommendations as $index => $vendor) {
            $this->recommendationModel->insert([
                'shipment_id'     => $shipmentId,
                'vendor_id'       => $vendor['vendor_id'],
                'vendor_cost'     => $vendor['vendor_cost'],
                'customer_charge' => $customerTotal,
                'profit'          => $vendor['profit'],
                'margin'          => $vendor['margin'],
                'score'           => $vendor['score'],
                'recommended'     => ($index === 0) ? 1 : 0
            ]);
        }
    }
}