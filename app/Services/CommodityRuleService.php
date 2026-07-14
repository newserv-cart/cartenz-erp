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
     * Get active rule by commodity type ID.
     *
     * CodeIgniter Model pada proyek CARTENZ mengembalikan row
     * sebagai array, sehingga tipe return harus ?array.
     */
    public function getRule(int $commodityTypeId): ?array
    {
        $rule = $this->ruleModel
            ->where('commodity_type_id', $commodityTypeId)
            ->where('status', 1)
            ->first();

        if ($rule === null) {
            return null;
        }

        return is_array($rule)
            ? $rule
            : (array) $rule;
    }

    /**
     * Calculate charges based on the active commodity rule.
     */
    public function calculate(
        int $commodityTypeId,
        float $chargeableWeight
    ): array {
        $rule = $this->getRule($commodityTypeId);

        if (!$rule) {
            return [
                'customer_insurance'  => 0.0,
                'customer_packing'    => 0.0,
                'customer_additional' => 0.0,
                'vendor_insurance'    => 0.0,
                'vendor_packing'      => 0.0,
                'vendor_additional'   => 0.0,
            ];
        }

        return [
            'customer_insurance' =>
                (float) ($rule['customer_insurance'] ?? 0),

            'customer_packing' =>
                (float) ($rule['customer_packing'] ?? 0),

            'customer_additional' =>
                (float) ($rule['customer_additional'] ?? 0),

            'vendor_insurance' =>
                (float) ($rule['vendor_insurance'] ?? 0),

            'vendor_packing' =>
                (float) ($rule['vendor_packing'] ?? 0),

            'vendor_additional' =>
                (float) ($rule['vendor_additional'] ?? 0),
        ];
    }
}