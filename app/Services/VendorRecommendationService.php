<?php

namespace App\Services;

use App\Models\VendorModel;

class VendorRecommendationService
{
    protected $vendorModel;
    protected $expressService;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->expressService = new ExpressRateService();
    }

    public function compare(
        int $serviceId,
        string $shipmentType,
        int $zone,
        float $weight,
        float $customerSelling
    )
    {
        $vendors = $this->vendorModel

            ->where('status',1)

            ->findAll();

        $result=[];

        foreach($vendors as $vendor){

            $rate = $this->expressService->getRate(

                $vendor['id'],

                $serviceId,

                $shipmentType,

                $zone,

                $weight

            );

            $vendorCost = $rate['price'];

            $profit =

                $customerSelling

                -

                $vendorCost;

            $result[]=[

                'vendor'=>$vendor,

                'cost'=>$vendorCost,

                'profit'=>$profit,

                'rate'=>$rate

            ];

        }

        usort($result,function($a,$b){

            return $b['profit'] <=> $a['profit'];

        });

        return $result;
    }

}