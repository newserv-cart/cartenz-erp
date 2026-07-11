<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessRuleActionModel extends Model
{
    protected $table            = 'business_rule_actions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'rule_id',
        'charge_type_id',
        'action_type',
        'charge_side',
        'calculation_type',
        'action_value',
        'base_field',
        'minimum_amount',
        'maximum_amount',
        'display_order',
        'remarks',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getByRule(int $ruleId): array
    {
        return $this
            ->where('rule_id', $ruleId)
            ->orderBy('display_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}