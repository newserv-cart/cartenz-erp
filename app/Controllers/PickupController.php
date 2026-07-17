<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PickupRequestModel;
use App\Services\BranchScanService;

class PickupController extends BaseController
{
    public function index()
    {
        return view(
            'pickup/index',
            [
                'pickups' =>
                    (new PickupRequestModel())
                        ->orderBy('id', 'DESC')
                        ->findAll(),
            ]
        );
    }

    public function request($shipmentId)
    {
        return view(
            'pickup/request',
            ['shipmentId' => (int) $shipmentId]
        );
    }

    public function store()
    {
        try {
            $shipmentId = (int) $this->request
                ->getPost('shipment_id');

            if ($shipmentId <= 0) {
                throw new \RuntimeException(
                    'Shipment tidak valid.'
                );
            }

            $id = (new PickupRequestModel())
                ->insert([
                    'shipment_id' => $shipmentId,
                    'pickup_date' => $this->request
                        ->getPost('pickup_date'),
                    'pickup_time' => $this->request
                        ->getPost('pickup_time'),
                    'pickup_address' => $this->request
                        ->getPost('pickup_address'),
                    'contact_name' => $this->request
                        ->getPost('contact_name'),
                    'contact_phone' => $this->request
                        ->getPost('contact_phone'),
                    'pickup_status' => 'REQUESTED',
                ], true);

            if (!$id) {
                throw new \RuntimeException(
                    'Pickup Request gagal disimpan.'
                );
            }

            return redirect()
                ->to(
                    site_url(
                        'shipments/view/' .
                        $shipmentId
                    )
                )
                ->with(
                    'success',
                    'Pickup Request Submitted'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function assign($id)
    {
        try {
            $actorId = $this->requireActorId();

            $updated = (new PickupRequestModel())
                ->update(
                    (int) $id,
                    [
                        'pickup_status' => 'ASSIGNED',
                        'driver_name' =>
                            auth()->user()->username
                            ?? 'Assigned Courier',
                        'assigned_user_id' => $actorId,
                    ]
                );

            if (!$updated) {
                throw new \RuntimeException(
                    'Pickup gagal di-assign.'
                );
            }

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Driver Assigned'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function done($id)
    {
        try {
            $pickup = (new PickupRequestModel())
                ->find((int) $id);

            if (!$pickup) {
                throw new \RuntimeException(
                    'Pickup tidak ditemukan.'
                );
            }

            $result = (new BranchScanService())
                ->scanPickup(
                    (int) $pickup['shipment_id'],
                    $this->requireActorId(),
                    'Pickup Request #' .
                    (int) $pickup['id'] .
                    ' completed.'
                );

            return redirect()
                ->to(
                    site_url(
                        'shipments/detail/' .
                        $result['shipment_id']
                    )
                )
                ->with(
                    'success',
                    'Pickup scan berhasil. Status shipment menjadi PICKED_UP.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    private function requireActorId(): int
    {
        $actorId = (int) (auth()->id() ?? 0);

        if ($actorId <= 0) {
            throw new \RuntimeException(
                'Sesi user tidak ditemukan.'
            );
        }

        return $actorId;
    }
}
