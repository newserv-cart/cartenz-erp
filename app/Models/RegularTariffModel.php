<?php

namespace App\Models;

use CodeIgniter\Model;
use RuntimeException;

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
     * Return one active tariff row for a shipment.
     *
     * The date filters treat NULL / empty / '0000-00-00' as "no limit",
     * so rows with blank effective dates still match.
     */
    public function findApplicableRate(
        int $countryId,
        int $serviceId,
        float $chargeableWeight,
        string $pricingDate
    ): ?array {
        $builder = $this->db->table($this->table)
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('weight_from <=', $chargeableWeight)
            ->where('weight_to >=', $chargeableWeight)
            ->where('status', 1)
            ->groupStart()
                ->where('effective_from IS NULL', null, false)
                ->orWhere('effective_from', '')
                ->orWhere('effective_from', '0000-00-00')
                ->orWhere('effective_from <=', $pricingDate)
            ->groupEnd()
            ->groupStart()
                ->where('effective_until IS NULL', null, false)
                ->orWhere('effective_until', '')
                ->orWhere('effective_until', '0000-00-00')
                ->orWhere('effective_until >=', $pricingDate)
            ->groupEnd()
            ->orderBy('weight_from', 'DESC')
            ->orderBy('effective_from', 'DESC')
            ->orderBy('id', 'DESC');

        $sql = $builder->getCompiledSelect(false);
        log_message('error', '[REGULAR TARIFF SQL] ' . $sql);

        $query = $builder->get();

        if ($query === false) {
            $dbError = $this->db->error();

            log_message(
                'error',
                '[REGULAR TARIFF QUERY FAILED] Code: '
                . ($dbError['code'] ?? '-')
                . ' Message: '
                . ($dbError['message'] ?? 'Unknown database error')
            );

            throw new RuntimeException(
                'Query tarif gagal: '
                . ($dbError['message'] ?? 'Unknown database error')
            );
        }

        $row = $query->getFirstRow('array');

        return $row ?: null;
    }
}
