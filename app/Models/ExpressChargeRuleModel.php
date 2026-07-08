<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressChargeRuleModel extends Model
{
    protected $table         = 'express_charge_rules';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array'; // as you set
    protected $allowedFields = [
        'country_id', 'service_id',
        'customer_fuel_type', 'customer_fuel_value',
        'vendor_fuel_type', 'vendor_fuel_value',
        'customer_gogreen_type', 'customer_gogreen_value',
        'vendor_gogreen_type', 'vendor_gogreen_value',
        'customer_remote_type', 'customer_remote_value',
        'vendor_remote_type', 'vendor_remote_value',
        'effective_from', 'effective_to',
        'status', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get active rule matching country, service, and date
     * Picks the latest valid rule if multiple exist
     */
    public function getActiveRule(int $countryId, int $serviceId, ?string $shipmentDate = null)
    {
        $shipmentDate = $shipmentDate ?? date('Y-m-d');

        return $this->where('country_id', $countryId)
                    ->where('service_id', $serviceId)
                    ->where('status', 1)
                    // Effective start condition
                    ->groupStart()
                        ->where('effective_from <=', $shipmentDate)
                        ->orWhere('effective_from', null)
                    ->groupEnd()
                    // Effective end condition
                    ->groupStart()
                        ->where('effective_to >=', $shipmentDate)
                        ->orWhere('effective_to', null)
                    ->groupEnd()
                    // Get the most recent rule if multiple are valid
                    ->orderBy('effective_from', 'DESC')
                    ->first();
    }
}