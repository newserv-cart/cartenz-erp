<?php

namespace App\Services;

use App\Models\RemoteAreaRuleModel;

class RemoteService
{
    public function findRule(
        int $countryId,
        string $city = '',
        string $postcode = ''
    ): ?array
    {
        return (new RemoteAreaRuleModel())

            ->where('country_id', $countryId)

            ->where('status', 1)

            ->groupStart()

                ->where('city', $city)

                ->orWhere('postal_code', $postcode)

            ->groupEnd()

            ->first();
    }

    public function calculate(
        int $countryId,
        string $city='',
        string $postcode=''
    ): array
    {

        $rule = $this->findRule(

            $countryId,

            $city,

            $postcode

        );

        if(!$rule){

            return [

                'amount'=>0,

                'vendor_amount'=>0,

                'rule'=>null

            ];

        }

        return [

            'amount'=>(float)$rule['customer_charge'],

            'vendor_amount'=>(float)$rule['vendor_charge'],

            'rule'=>$rule

        ];

    }
}