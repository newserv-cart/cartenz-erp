<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PickupController extends BaseController
{
    public function index()
{
    $pickups =
        (new \App\Models\PickupRequestModel())
        ->orderBy('id', 'DESC')
        ->findAll();

    return view(
        'pickup/index',
        [
            'pickups' => $pickups
        ]
    );
}


public function request($shipmentId)
{
    return view(
        'pickup/request',
        [
            'shipmentId' => $shipmentId
        ]
    );
}

public function store()
{
 
    $model = new \App\Models\PickupRequestModel();

   $id = $model->insert([

    'shipment_id' =>
        $this->request->getPost('shipment_id'),

    'pickup_date' =>
        $this->request->getPost('pickup_date'),

    'pickup_time' =>
        $this->request->getPost('pickup_time'),

    'pickup_address' =>
        $this->request->getPost('pickup_address'),

    'contact_name' =>
        $this->request->getPost('contact_name'),

    'contact_phone' =>
        $this->request->getPost('contact_phone'),

    'pickup_status' => 'REQUESTED'

]);


    return redirect()
        ->to(
            '/shipments/view/' .
            $this->request->getPost('shipment_id')
        )
        ->with(
            'success',
            'Pickup Request Submitted'
        );
}

public function assign($id)
{
    $pickupModel = new \App\Models\PickupRequestModel();

    $pickupModel->update(
        $id,
        [
            'pickup_status' => 'ASSIGNED',
            'driver_name'   => 'BUDI DRIVER'
        ]
    );

    return redirect()->back()
        ->with(
            'success',
            'Driver Assigned'
        );
}

public function done($id)
{
    $pickupModel = new \App\Models\PickupRequestModel();

    $pickup = $pickupModel->find($id);

    if (!$pickup) {
        return redirect()->back()
            ->with('error', 'Pickup tidak ditemukan');
    }

    // Update Pickup
    $pickupModel->update(
        $id,
        [
            'pickup_status' => 'COMPLETED'
        ]
    );

    // Update Shipment
    $shipmentModel =
        new \App\Models\ShipmentModel();

    $shipmentModel->update(
        $pickup['shipment_id'],
        [
            'shipment_status' => 'PICKED_UP'
        ]
    );

    // Tracking Otomatis
    $trackingModel =
        new \App\Models\ShipmentTrackingModel();

    $trackingModel->insert([
        'shipment_id'       => $pickup['shipment_id'],
        'tracking_status'   => 'PICKED_UP',
        'tracking_location' => 'Customer Location',
        'tracking_notes'    => 'Shipment Picked Up By Driver',
        'event_time'        => date('Y-m-d H:i:s')
    ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Pickup Completed'
        );
}

}
