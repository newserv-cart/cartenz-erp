<?php

namespace App\Controllers;

use App\Models\TrackingStatusModel;

class TrackingStatusController extends BaseController
{
    public function index()
    {
        $model = new TrackingStatusModel();

        $statuses = $model
            ->orderBy('sort_order','ASC')
            ->findAll();

        return view(
            'tracking_status/index',
            [
                'statuses'=>$statuses
            ]
        );
    }
}