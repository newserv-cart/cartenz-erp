<?php

namespace App\Services;

use App\Models\ExpressRateHeaderModel;
use App\Models\ExpressRateDetailModel;

class ExpressRateService
{
    protected $headerModel;
    protected $detailModel;

    public function __construct()
    {
        $this->headerModel = new ExpressRateHeaderModel();
        $this->detailModel = new ExpressRateDetailModel();
    }

    /**
     * Ambil tarif lengkap beserta informasi sumbernya
     */
    public function getRate(
        int $vendorId,
        int $serviceId,
        string $shipmentType,
        int $zone,
        float $weight
    ): array {
        // 1. Normalisasi berat sesuai aturan vendor
        $chargeWeight = $this->normalizeWeight($weight);

        // 2. Ambil header tarif yang berlaku dan paling baru
        $header = $this->headerModel
            ->where('vendor_id', $vendorId)
            ->where('service_id', $serviceId)
            ->where('effective_date <=', date('Y-m-d')) // Pastikan tarif sudah berlaku
            ->orderBy('effective_date', 'DESC')
            ->first();

        if (!$header) {
            return [
                'price'          => 0,
                'weight'         => $chargeWeight,
                'zone'           => $zone,
                'header_id'      => null,
                'rate_version'   => null,
                'remarks'        => 'Tarif tidak ditemukan',
                'found'          => false
            ];
        }

        // 3. Cari tarif yang sesuai (ambil berat terdekat jika tidak ada persis)
        $rate = $this->detailModel
            ->where('header_id', $header['id'])
            ->where('shipment_type', strtoupper($shipmentType))
            ->where('zone', $zone)
            ->where('weight <=', $chargeWeight) // Cari yang mendekati atau sama
            ->orderBy('weight', 'DESC') // Ambil yang paling besar di bawah berat muatan
            ->first();

        if (!$rate) {
            return [
                'price'          => 0,
                'weight'         => $chargeWeight,
                'zone'           => $zone,
                'header_id'      => $header['id'],
                'rate_version'   => date('F Y', strtotime($header['effective_date'])),
                'remarks'        => 'Tarif untuk berat ini tidak tersedia',
                'found'          => false
            ];
        }

        // 4. Kembalikan SEMUA data untuk kebutuhan perhitungan & audit
        return [
            'price'          => (float)$rate['price'],
            'charge_weight'  => $chargeWeight,
            'base_weight'    => (float)$rate['weight'],
            'zone'           => $zone,
            'header_id'      => (int)$header['id'],
            'shipment_type'  => strtoupper($shipmentType),
            'effective_date' => $header['effective_date'],
            'rate_version'   => date('F Y', strtotime($header['effective_date'])),
            'remarks'        => $header['remarks'] ?? '',
            'found'          => true
        ];
    }

    /**
     * Pembulatan berat sesuai standar DHL
     * ≤ 30 kg → dibulatkan ke kelipatan 0.5 kg
     * > 30 kg → dibulatkan ke bilangan bulat ke atas
     */
    protected function normalizeWeight(float $weight): float
    {
        if ($weight <= 30) {
            return ceil($weight * 2) / 2;
        }
        return ceil($weight);
    }
}