<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ShipmentModel;

class ShipmentApprovalController extends BaseController
{
   public function pending()
{
    $shipments = (new ShipmentModel())
        ->select('
            shipments.*,
            customers.customer_name,
            customers.company_name,
            customers.phone,
            customers.email

        ')
        ->join(
            'customers',
            'customers.id = shipments.customer_id',
            'left'
        )
        ->where(
            'shipment_status',
            'DRAFT'
        )
        ->orderBy('shipments.id', 'DESC')
        ->findAll();

    return view(
        'shipments/pending',
        [
            'shipments' => $shipments
        ]
    );
}

    public function approve($id)
    {
        $shipmentModel = new ShipmentModel();

        $shipment = $shipmentModel->find($id);

        if (!$shipment)
        {
            return redirect()->back();
        }

        $shipmentModel->update(
            $id,
            [
                'shipment_status' => 'CREATED'
            ]
        );

  // ==========================
    // INSERT TRACKING OTOMATIS
    // ==========================

    $trackingModel =
        new \App\Models\ShipmentTrackingModel();

    $trackingModel->insert([

        'shipment_id' =>
            $shipment['id'],

        'tracking_code' =>
            $shipment['awb_no'],

        'tracking_status' =>
            'CREATED',

        'tracking_location' =>
            'Cartenz System',

        'tracking_notes' =>
            'Shipment Approved',

        'event_time' =>
            date('Y-m-d H:i:s'),

        'created_at' =>
            date('Y-m-d H:i:s'),

    ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Shipment Approved'
            );
    }

public function reject($id)
{
    $shipmentModel = new ShipmentModel();

    $shipment = $shipmentModel->find($id);

    if (!$shipment)
    {
        return redirect()->back();
    }

    $shipmentModel->update(
        $id,
        [
            'shipment_status' => 'REJECTED'
        ]
    );

    $trackingModel =
        new \App\Models\ShipmentTrackingModel();

    $trackingModel->insert([

        'shipment_id' =>
            $shipment['id'],

        'tracking_code' =>
            $shipment['awb_no'],

        'tracking_status' =>
            'REJECTED',

        'tracking_location' =>
            'Cartenz System',

        'tracking_notes' =>
            'Shipment Rejected',

        'event_time' =>
            date('Y-m-d H:i:s'),

        'created_at' =>
            date('Y-m-d H:i:s'),

    ]);

    return redirect()
        ->back()
        ->with(
            'success',
            'Shipment Rejected'
        );
}

public function view($id)
{
    $shipment = (new \App\Models\ShipmentModel())
        ->select('
            shipments.*,
            customers.customer_name,
            customers.company_name,
            customers.phone,
            customers.email
        ')
        ->join(
            'customers',
            'customers.id = shipments.customer_id',
            'left'
        )
        ->find($id);

    if (!$shipment)
    {
        return redirect()->back();
    }

    $tracking = (new \App\Models\ShipmentTrackingModel())
        ->where(
            'shipment_id',
            $id
        )
        ->orderBy(
            'event_time',
            'DESC'
        )
        ->findAll();

    return view(
        'shipments/view',
        [
            'shipment' => $shipment,
            'tracking' => $tracking
        ]
    );
}

}