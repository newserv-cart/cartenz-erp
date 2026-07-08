<?php

namespace App\Services;

use App\Models\CommodityChargeRuleModel;

class CommodityRuleService
{
    protected CommodityChargeRuleModel $ruleModel;

    public function __construct()
    {
        $this->ruleModel = new CommodityChargeRuleModel();
    }

    /**
     * Get active rule by commodity type ID
     */
    public function getRule(int $commodityTypeId): ?object
    {
        return $this->ruleModel
            ->where('commodity_type_id', $commodityTypeId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Calculate charges based on rule; return 0 if no rule exists
     */
    public function calculate(int $commodityTypeId, float $chargeableWeight): array
    {
        $rule = $this->getRule($commodityTypeId);

        // If no rule found, return all zeros
        if (!$rule) {
            return [
                'customer_insurance'  => 0,
                'customer_packing'    => 0,
                'customer_additional' => 0,
                'vendor_insurance'    => 0,
                'vendor_packing'      => 0,
                'vendor_additional'   => 0
            ];
        }

        // Return values directly from the rule (fixed values as stored)
        return [
            'customer_insurance'  => (float)($rule->customer_insurance ?? 0),
            'customer_packing'    => (float)($rule->customer_packing ?? 0),
            'customer_additional' => (float)($rule->customer_additional ?? 0),
            'vendor_insurance'    => (float)($rule->vendor_insurance ?? 0),
            'vendor_packing'      => (float)($rule->vendor_packing ?? 0),
            'vendor_additional'   => (float)($rule->vendor_additional ?? 0)
        ];
    }
}