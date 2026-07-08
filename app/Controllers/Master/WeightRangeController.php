<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\WeightRangeModel;

class WeightRangeController extends BaseController
{
    public function index()
    {
        $model = new WeightRangeModel();

        $data = [
            'title' => 'Weight Ranges',
            'rows'  => $model
                ->orderBy('weight_from', 'ASC')
                ->findAll(),
        ];

        return view('master/weight_ranges/index', $data);
    }
}