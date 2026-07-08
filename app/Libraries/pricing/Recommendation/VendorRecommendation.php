<?php

namespace App\Libraries\Pricing\Recommendation;

use App\Models\VendorModel;

class VendorRecommendation
{
    public function recommend(array $shipment, array $vendors): ?array
    {
        if (empty($vendors)) {
            return null;
        }

        // Urutkan berdasarkan profit terbesar
        usort($vendors, function ($a, $b) {
            return $b['profit'] <=> $a['profit'];
        });

        return $vendors[0];
    }
}