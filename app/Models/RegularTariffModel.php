<?php

namespace App\Models;

use CodeIgniter\Model;

class RegularTariffModel extends Model
{
    protected $table            = 'regular_tariffs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'country_id',
        'service_id',
        'weight_from',
        'weight_to',
        'agent_a_price',
        'agent_b_price',
        'agent_c_price',
        'publish_price',
        'vendor_cost',
        'effective_from',
        'effective_until',
        'remarks',
        'status',
    ];

    /**
     * Return one active customer tariff row for a shipment.
     *
     * regular_tariffs does not contain transport_mode_id or
     * delivery_type_id in the current CARTENZ schema. Those dimensions
     * belong to vendor_rates and must not be queried here.
     */
    public function findApplicableRate(
        int $countryId,
        int $serviceId,
        float $chargeableWeight,
        string $pricingDate
    ): ?array {
        return $this
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('weight_from <=', $chargeableWeight)
            ->where('weight_to >=', $chargeableWeight)
            ->where('status', 1)
            ->groupStart()
                ->where('effective_from IS NULL', null, false)
                ->orWhere('effective_from <=', $pricingDate)
            ->groupEnd()
            ->groupStart()
                ->where('effective_until IS NULL', null, false)
                ->orWhere('effective_until >=', $pricingDate)
            ->groupEnd()
            ->orderBy('weight_from', 'DESC')
            ->orderBy('effective_from', 'DESC')
            ->orderBy('id', 'DESC')
            ->first();
    }
}
