<?php

namespace App\Libraries\Pricing\Calculators;

use App\Models\WeightRangeModel;

class WeightRangeCalculator
{
    public function getRange(float $weight): ?array
    {
        $model = new WeightRangeModel();

        return $model
            ->where('min_weight <=', $weight)
            ->where('max_weight >=', $weight)
            ->where('status', 1)
            ->first();
    }
}