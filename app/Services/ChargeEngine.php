<?php

namespace App\Services;

class ChargeEngine
{
    public function calculate(array $shipment, array $matchedRules): array
    {
        $charges = [];

        foreach ($matchedRules as $matchedRule) {
            foreach ($matchedRule['actions'] as $action) {
                $amount = $this->calculateAmount($shipment, $action);

                if ($amount == 0) {
                    continue;
                }

                $charges[] = [
                    'rule_id'          => (int) $matchedRule['rule']['id'],
                    'rule_code'        => $matchedRule['rule']['rule_code'],
                    'rule_name'        => $matchedRule['rule']['rule_name'],
                    'charge_type_id'   => $action['charge_type_id'] !== null
                        ? (int) $action['charge_type_id']
                        : null,
                    'action_type'      => $action['action_type'],
                    'charge_side'      => $action['charge_side'],
                    'calculation_type' => $action['calculation_type'],
                    'rate_value'       => (float) $action['action_value'],
                    'amount'           => $amount,
                    'remarks'          => $action['remarks'] ?? null,
                ];
            }
        }

        return $charges;
    }

    private function calculateAmount(array $shipment, array $action): float
    {
        $type  = strtoupper($action['calculation_type'] ?? 'FLAT');
        $value = (float) ($action['action_value'] ?? 0);

        $amount = match ($type) {
            'FLAT',
            'PER_SHIPMENT',
            'MANUAL' => $value,

            'PER_KG' => $value * (float) (
                $shipment['chargeable_weight'] ?? 0
            ),

            'PER_COLLY' => $value * (int) (
                $shipment['total_colly'] ?? 1
            ),

            'PER_ITEM' => $value * (int) (
                $shipment['total_items'] ?? 0
            ),

            'PERCENTAGE' => $this->calculatePercentage(
                $shipment,
                $action,
                $value
            ),

            default => 0,
        };

        $minimum = $action['minimum_amount'] ?? null;
        $maximum = $action['maximum_amount'] ?? null;

        if ($minimum !== null && $minimum !== '') {
            $amount = max($amount, (float) $minimum);
        }

        if ($maximum !== null && $maximum !== '') {
            $amount = min($amount, (float) $maximum);
        }

        return round($amount, 2);
    }

    private function calculatePercentage(
        array $shipment,
        array $action,
        float $percentage
    ): float {
        $baseField = $action['base_field'] ?? 'freight_charge';
        $baseValue = (float) ($shipment[$baseField] ?? 0);

        return $baseValue * ($percentage / 100);
    }
}