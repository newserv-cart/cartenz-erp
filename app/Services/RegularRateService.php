<?php

namespace App\Services;

use App\Models\RegularTariffModel;

class RegularRateService
{
    protected RegularTariffModel $tariffModel;

    public function __construct()
    {
        $this->tariffModel = new RegularTariffModel();
    }

    /**
     * Cari dan hitung tarif sesuai berat dan tujuan
     */
    public function calculate(int $customerId, int $countryId, int $serviceId, float $chargeWeight): array
    {
        // Nilai default jika tidak ditemukan tarif
        $default = [
            'tariff_id'       => 0,
            'weight_from'     => 0,
            'weight_to'       => 0,
            'weight_range'    => '0 - 0',
            'agent_type'      => 'A',
            'customer_price'  => 0,
            'vendor_price'    => 0,
            'publish_price'   => 0,
            'customer_freight'=> 0,
            'vendor_freight'  => 0
        ];

        // Validasi input dasar
        if ($countryId <= 0 || $serviceId <= 0 || $chargeWeight <= 0) {
            return $default;
        }

        // ✅ Cari tarif hanya berdasarkan negara + layanan + berat
        $tariff = $this->tariffModel
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('status', 1)
            ->where('weight_from <=', $chargeWeight)
            ->where('weight_to >=', $chargeWeight)
            ->orderBy('weight_from', 'ASC')
            ->first();

        // Jika tidak ketemu, kembalikan nilai kosong
        if (!$tariff) {
            return $default;
        }

        // ✅ Hitung total biaya
        $customerPrice = (float) $tariff->publish_price;
        $vendorPrice   = (float) $tariff->vendor_cost;

        return [
            'tariff_id'         => $tariff->id,
            'weight_from'       => $tariff->weight_from,
            'weight_to'         => $tariff->weight_to,
            'weight_range'      => $tariff->weight_from . ' - ' . $tariff->weight_to . ' Kg',
            'agent_type'        => 'A',
            'customer_price'    => $customerPrice,
            'vendor_price'      => $vendorPrice,
            'publish_price'     => $customerPrice,
            'customer_freight'  => round($customerPrice * $chargeWeight, 2),
            'vendor_freight'    => round($vendorPrice * $chargeWeight, 2)
        ];
    }
}