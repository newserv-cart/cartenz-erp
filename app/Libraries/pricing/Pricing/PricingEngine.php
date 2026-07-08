<?php

namespace App\Libraries\Pricing;

use App\Models\ServiceModel;

class PricingEngine
{
    public function calculate(array $shipment)
    {
        $serviceModel = new ServiceModel();

        $service = $serviceModel->find($shipment['service_id']);

        if (!$service) {
            throw new \Exception('Service tidak ditemukan.');
        }

        switch ($service['pricing_engine']) {

            case 'REGULAR':

                return (new RegularPricingEngine())
                    ->calculate($shipment);

            case 'EXPRESS':

                return (new ExpressPricingEngine())
                    ->calculate($shipment);

            default:

                throw new \Exception('Pricing Engine tidak tersedia.');

        }
    }
}