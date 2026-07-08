<?php

namespace App\Services;

use App\Models\VendorTariffModel;

class VendorCostService
{
    protected VendorTariffModel $vendorTariffModel;

    public function __construct()
    {
        $this->vendorTariffModel = new VendorTariffModel();
    }

    public function calculate(array $data): array
    {
        $weight = $data['chargeable_weight'];

        $tariff = $this->vendorTariffModel
            ->where('vendor_id', $data['vendor_id'])
            ->where('country_id', $data['country_id'])
            ->where('service_id', $data['service_id'])
            ->where('weight_from <=', $weight)
            ->where('weight_to >=', $weight)
            ->where('status', 1)
            ->first();

        if (!$tariff) {

            return [
                'freight'  => 0,
                'fuel'     => 0,
                'remote'   => 0,
                'go_green' => 0,
                'handling' => 0,
                'vat'      => 0,
                'total'    => 0
            ];
        }

        $freight = $tariff['freight'];

        $fuel = ($freight * $tariff['fuel_percent']) / 100;

        $remote = $data['vendor_remote_charge'] ?? 0;

        $goGreen = $tariff['go_green_charge'];

        $handling = $tariff['handling_charge'];

        $subtotal =
            $freight +
            $fuel +
            $remote +
            $goGreen +
            $handling;

        $vat = ($subtotal * $tariff['vat_percent']) / 100;

        $total =
            $subtotal +
            $vat;

        return [

            'freight'  => $freight,

            'fuel'     => $fuel,

            'remote'   => $remote,

            'go_green' => $goGreen,

            'handling' => $handling,

            'vat'      => $vat,

            'total'    => $total

        ];
    }
}