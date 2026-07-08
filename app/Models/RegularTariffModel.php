<?php

namespace App\Models;

use CodeIgniter\Model;

class RegularTariffModel extends Model
{
    protected $table            = 'regular_tariffs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array'; // Sesuai yang kamu pakai
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields    = [
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
        'status'
    ];

    /**
     * Ambil tarif yang sesuai berdasarkan negara, layanan, mode angkut dan berat
     * Otomatis cek status aktif dan masa berlaku
     */
    public function getApplicableRate(int $countryId, int $serviceId, int $transportModeId, float $chargeableWeight): ?array
    {
        $today = date('Y-m-d');

        return $this
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('transport_mode_id', $transportModeId)
            ->where('weight_from <=', $chargeableWeight)
            ->where('weight_to >=', $chargeableWeight)
            ->where('status', 1)
            ->groupStart()
                ->where('effective_from <=', $today)
                ->orWhere('effective_from IS NULL')
            ->groupEnd()
            ->groupStart()
                ->where('effective_until >=', $today)
                ->orWhere('effective_until IS NULL')
            ->groupEnd()
            ->first();
    }
}