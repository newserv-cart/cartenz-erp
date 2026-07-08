<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;


class AgentShipmentController extends BaseController
{
    public function detail($id)
    {
        $shipmentModel = new ShipmentModel();

        $trackingModel = new ShipmentTrackingModel();

        $shipment = $shipmentModel->find($id);

        $tracking = $trackingModel
            ->getTrackingHistory($id);

        $latestTracking = $trackingModel
            ->getLatestTracking($id);

        return view(
            'agent/shipment_detail',



            
            [
                'shipment'       => $shipment,
                'tracking'       => $tracking,
                'latestTracking' => $latestTracking
            ]
        );
    }
}