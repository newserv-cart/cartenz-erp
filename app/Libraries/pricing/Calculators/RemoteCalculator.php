<?php

namespace App\Libraries\Pricing\Calculators;

use App\Libraries\Pricing\CostBreakdown;

use App\Services\RemoteService;

class RemoteCalculator implements CalculatorInterface
{

    public function apply(array $shipment, CostBreakdown $cost): void
    {

        $remote =

            (new RemoteService())

            ->calculate(

                (int)$shipment['country_id'],

                $shipment['receiver_city'] ?? '',

                $shipment['receiver_postcode'] ?? ''

            );

        if($remote['amount']>0){

            $cost->add(

                'CUSTOMER',

                'REMOTE',

                'Remote Area',

                $remote['amount'],

                $remote['rule'] ?? []

            );

        }

        if($remote['vendor_amount']>0){

            $cost->add(

                'VENDOR',

                'REMOTE',

                'Vendor Remote',

                $remote['vendor_amount'],

                $remote['rule'] ?? []

            );

        }

    }

}