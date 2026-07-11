<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessRuleConditionModel extends Model
{
    protected $table            = 'business_rule_conditions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'rule_id',
        'field_name',
        'operator',
        'compare_value',
        'compare_value_2',
        'logical_operator',
        'condition_group',
        'display_order',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByRule(int $ruleId): array
    {
        return $this
            ->where('rule_id', $ruleId)
            ->orderBy('condition_group', 'ASC')
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}