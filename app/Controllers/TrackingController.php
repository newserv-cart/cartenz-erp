<?php

namespace App\Controllers;

use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;

class TrackingController extends BaseController


{
    public function index()
    {
        return view('tracking/index');
    }

    public function search()
    {
        $awb = $this->request->getPost('awb');

        return redirect()->to(
            base_url('track/' . $awb)
        );
    }

    public function detail($awb)
    {
        $shipmentModel = new ShipmentModel();

        $trackingModel = new ShipmentTrackingModel();

        $latestTracking = $trackingModel

        
    ->where('tracking_code', $awb)
    ->orderBy('event_time', 'DESC')
    ->first();


        $shipment = $shipmentModel
            ->where('awb_no', $awb)
            ->first();

        if (!$shipment)
        {
            return view('tracking/not_found');
        }

        $tracking = $trackingModel
    ->where('tracking_code', $awb)
    ->orderBy('event_time', 'DESC')
    ->findAll();

    $customerTracking = [];

foreach ($tracking as $row)
{
    // sembunyikan ON_HOLD lama
    if (
        in_array(
            $shipment['shipment_status'],
            [
                'ARRIVED_HUB',
                'IN_TRANSIT_HUB',
                'DELIVERED'
            ]
        )
        &&
        $row['tracking_status'] == 'ON_HOLD'
    )
    {
        continue;
    }

    // hanya samarkan tracking manifest internal
    if (
        in_array(
            $row['tracking_status'],
            [
                'MANIFESTED',
                'IN_TRANSIT_HUB',
                'ARRIVED_HUB'
            ]
        )
    )
    {
        $row['tracking_status']   = 'IN_TRANSIT';
        $row['tracking_notes']    = 'Paket sedang dalam proses pengiriman menuju negara tujuan.';
        $row['tracking_location'] = 'Cartenz Network';
    }

    $customerTracking[] = $row;
}

$tracking = $customerTracking;

if (
    $shipment['shipment_status'] == 'ARRIVED_HUB'
    && !empty($customerTracking)
)
{
    $tracking = [ $customerTracking[0] ];
}

        return view('tracking/detail', [
            'shipment' => $shipment,
            'tracking' => $tracking,
            'latestTracking' => $latestTracking,
        ]);
    }
}