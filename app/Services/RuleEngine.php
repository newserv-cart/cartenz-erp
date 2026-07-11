<?php

namespace App\Services;

use App\Models\BusinessRuleModel;
use App\Models\BusinessRuleConditionModel;
use App\Models\BusinessRuleActionModel;

class RuleEngine
{
    private BusinessRuleModel $ruleModel;
    private BusinessRuleConditionModel $conditionModel;
    private BusinessRuleActionModel $actionModel;

    public function __construct()
    {
        $this->ruleModel      = new BusinessRuleModel();
        $this->conditionModel = new BusinessRuleConditionModel();
        $this->actionModel    = new BusinessRuleActionModel();
    }

    /**
     * Menjalankan semua rule aktif terhadap context.
     *
     * Contoh context:
     * [
     *     'country_id'       => 16,
     *     'service_id'       => 2,
     *     'chargeable_weight'=> 25,
     *     'commodity_type_id'=> 4,
     *     'customer_id'      => 1,
     * ]
     */
    public function evaluate(array $context, ?string $ruleType = null): array
    {
        $matchedRules = [];
        $rules = $this->ruleModel->getActiveRules($ruleType);

        foreach ($rules as $rule) {
            $conditions = $this->conditionModel->getByRule((int) $rule['id']);

            if (!$this->conditionsMatch($conditions, $context)) {
                continue;
            }

            $matchedRules[] = [
                'rule'    => $rule,
                'actions' => $this->actionModel->getByRule((int) $rule['id']),
            ];

            if ((int) ($rule['stop_processing'] ?? 0) === 1) {
                break;
            }
        }

        return $matchedRules;
    }

    private function conditionsMatch(array $conditions, array $context): bool
    {
        if ($conditions === []) {
            return true;
        }

        $groupResults = [];

        foreach ($conditions as $condition) {
            $group = (int) ($condition['condition_group'] ?? 1);

            $fieldName = $condition['field_name'];
            $actualValue = $context[$fieldName] ?? null;

            $result = $this->compare(
                $actualValue,
                $condition['operator'],
                $condition['compare_value'] ?? null,
                $condition['compare_value_2'] ?? null
            );

            $logicalOperator = strtoupper(
                $condition['logical_operator'] ?? 'AND'
            );

            if (!array_key_exists($group, $groupResults)) {
                $groupResults[$group] = $result;
                continue;
            }

            if ($logicalOperator === 'OR') {
                $groupResults[$group] =
                    $groupResults[$group] || $result;
            } else {
                $groupResults[$group] =
                    $groupResults[$group] && $result;
            }
        }

        // Antar-group dianggap AND.
        foreach ($groupResults as $groupResult) {
            if ($groupResult !== true) {
                return false;
            }
        }

        return true;
    }

    private function compare(
        mixed $actual,
        string $operator,
        mixed $expected,
        mixed $expected2 = null
    ): bool {
        $operator = strtoupper(trim($operator));

        return match ($operator) {
            '='           => (string) $actual === (string) $expected,
            '!='          => (string) $actual !== (string) $expected,
            '>'           => (float) $actual > (float) $expected,
            '>='          => (float) $actual >= (float) $expected,
            '<'           => (float) $actual < (float) $expected,
            '<='          => (float) $actual <= (float) $expected,
            'IN'          => $this->inList($actual, $expected),
            'NOT_IN'      => !$this->inList($actual, $expected),
            'BETWEEN'     => (float) $actual >= (float) $expected
                          && (float) $actual <= (float) $expected2,
            'CONTAINS'    => str_contains(
                strtolower((string) $actual),
                strtolower((string) $expected)
            ),
            'IS_NULL'     => $actual === null || $actual === '',
            'IS_NOT_NULL' => $actual !== null && $actual !== '',
            default       => false,
        };
    }

    private function inList(mixed $actual, mixed $expected): bool
    {
        if (is_array($expected)) {
            $values = $expected;
        } else {
            $values = array_map(
                'trim',
                explode(',', (string) $expected)
            );
        }

        return in_array((string) $actual, array_map('strval', $values), true);
    }
}