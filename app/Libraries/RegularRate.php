<?php

namespace App\Libraries;

use App\Models\RegularTariffModel;

class RegularRate
{

    public function calculate(
        $countryId,
        $serviceId,
        $transportId,
        $weight,
        $agentType='A'
    )
    {

        $tariff = (new RegularTariffModel())

            ->where('country_id',$countryId)

            ->where('service_id',$serviceId)

            ->where('transport_mode_id',$transportId)

            ->where('weight_from <=',$weight)

            ->where('weight_to >=',$weight)

            ->first();

        if(!$tariff){

            return null;

        }

        switch(strtoupper($agentType)){

            case 'A':

                $price = $tariff['agent_a_price'];

                break;

            case 'B':

                $price = $tariff['agent_b_price'];

                break;

            case 'C':

                $price = $tariff['agent_c_price'];

                break;

             default:
        $price = $tariff['publish_price'];
}

$vendor = $tariff['vendor_cost'];

return [

    'price_per_kg'=>$price,

    'vendor_per_kg'=>$vendor,

    'vendor_total'=>$vendor*$weight,

    'total'=>$price*$weight

];

    }

}