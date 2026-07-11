<?php

namespace App\Models;

use CodeIgniter\Model;

class BusinessRuleModel extends Model
{
    protected $table            = 'business_rules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'rule_code',
        'rule_name',
        'description',
        'rule_type',
        'priority',
        'stop_processing',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    public function getActiveRules(?string $ruleType = null): array
    {
        $builder = $this
            ->where('is_active', 1)
            ->groupStart()
                ->where('valid_from IS NULL', null, false)
                ->orWhere('valid_from <=', date('Y-m-d'))
            ->groupEnd()
            ->groupStart()
                ->where('valid_until IS NULL', null, false)
                ->orWhere('valid_until >=', date('Y-m-d'))
            ->groupEnd();

        if ($ruleType !== null) {
            $builder->where('rule_type', strtoupper($ruleType));
        }

        return $builder
            ->orderBy('priority', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}