<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ShipmentVendorWorkflowService;

class ShipmentVendorController extends BaseController
{
    protected ShipmentVendorWorkflowService $workflow;

    public function __construct()
    {
        $this->workflow = new ShipmentVendorWorkflowService();
    }

    public function generate($shipmentId)
    {
        try {
            $total = $this->workflow->generateRecommendations(
                (int) $shipmentId,
                $this->requireActorId()
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with(
                    'success',
                    $total . ' rekomendasi vendor berhasil dihitung.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with('error', $e->getMessage());
        }
    }

    public function assign($shipmentId)
    {
        try {
            $this->workflow->assignVendor(
                (int) $shipmentId,
                (int) $this->request->getPost('recommendation_id'),
                $this->request->getPost('override_reason'),
                $this->requireActorId()
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with(
                    'success',
                    'Vendor berhasil di-assign dan vendor cost dikunci.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with('error', $e->getMessage());
        }
    }

    public function submit($shipmentId)
    {
        try {
            $this->workflow->submitToVendor(
                (int) $shipmentId,
                (string) $this->request->getPost('vendor_awb_no'),
                $this->request->getPost('master_awb_no'),
                $this->request->getPost('tracking_no_vendor'),
                $this->requireActorId()
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with(
                    'success',
                    'Shipment berhasil disubmit dan dikunci ke vendor.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '#vendor')
                ->with('error', $e->getMessage());
        }
    }

    private function requireActorId(): int
    {
        $actorId = auth()->id();

        if (!$actorId) {
            throw new \RuntimeException(
                'Sesi admin tidak ditemukan. Silakan login ulang.'
            );
        }

        return (int) $actorId;
    }
}
