<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ShipmentVendorWorkflowService;
use App\Services\VendorTransitTrackingService;

class ShipmentVendorController extends BaseController
{
    protected ShipmentVendorWorkflowService $workflow;
    protected VendorTransitTrackingService $transitTracking;

    public function __construct()
    {
        $this->workflow        = new ShipmentVendorWorkflowService();
        $this->transitTracking = new VendorTransitTrackingService();
    }

    public function generate($shipmentId)
    {
        try {
            $headOffice = $this->requireHeadOfficeActor();
            $actorId    = (int) $headOffice['actor_id'];

            $total = $this->workflow->generateRecommendations(
                (int) $shipmentId,
                $actorId
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '?tab=vendor')
                ->with(
                    'success',
                    $total . ' rekomendasi vendor berhasil dihitung.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '?tab=vendor')
                ->with('error', $e->getMessage());
        }
    }

    public function assign($shipmentId)
    {
        try {
            $headOffice = $this->requireHeadOfficeActor();
            $actorId    = (int) $headOffice['actor_id'];

            $this->workflow->assignVendor(
                (int) $shipmentId,
                (int) $this->request->getPost('recommendation_id'),
                $this->request->getPost('override_reason'),
                $actorId
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '?tab=vendor')
                ->with(
                    'success',
                    'Vendor berhasil di-assign dan vendor cost dikunci.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId) . '?tab=vendor')
                ->with('error', $e->getMessage());
        }
    }

    public function submit($shipmentId)
    {
        try {
            $headOffice = $this->requireHeadOfficeActor();
            $actorId    = (int) $headOffice['actor_id'];

            $this->workflow->submitToVendor(
                (int) $shipmentId,
                (string) $this->request->getPost('vendor_awb_no'),
                $this->request->getPost('master_awb_no'),
                $this->request->getPost('tracking_no_vendor'),
                $actorId
            );

            return redirect()
                ->to(
    site_url(
        'shipments/detail/' . (int) $shipmentId
    )
    . '?tab=vendor'
    . '&anchor=add-vendor-tracking'
    . '#add-vendor-tracking'
)
                ->with(
                    'success',
                    'Shipment berhasil disubmit dan dikunci ke vendor.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(
    site_url(
        'shipments/detail/' . (int) $shipmentId
    )
    . '?tab=vendor'
    . '&anchor=add-vendor-tracking'
    . '#add-vendor-tracking'
)
                ->with('error', $e->getMessage());
        }
    }

public function addTrackingEvent($shipmentId)
{
    try {
        $headOffice = $this->requireHeadOfficeActor();
        $actorId    = (int) $headOffice['actor_id'];

        $result = $this->transitTracking->recordManualEvent(
            (int) $shipmentId,
            [
                    'internal_status'   => $this->request->getPost('internal_status'),
                    'event_time'        => $this->request->getPost('event_time'),
                    'country_code'      => $this->request->getPost('country_code'),
                    'country_name'      => $this->request->getPost('country_name'),
                    'city'              => $this->request->getPost('city'),
                    'hub_code'          => $this->request->getPost('hub_code'),
                    'location_name'     => $this->request->getPost('location_name'),
                    'timezone_name'     => $this->request->getPost('timezone_name'),
                    'vendor_status_code'=> $this->request->getPost('vendor_status_code'),
                    'source_event_id'   => $this->request->getPost('source_event_id'),
                    'tracking_notes'    => $this->request->getPost('tracking_notes'),
                    'is_public'         => $this->request->getPost('is_public') ? 1 : 0,
                ],
                $actorId
            );

            $message = !empty($result['duplicate'])
                ? 'Vendor tracking event duplikat diabaikan.'
                : 'Vendor tracking event berhasil disimpan.';

            if (
                empty($result['duplicate'])
                && empty($result['promoted'])
            ) {
                $message .= ' Event tersimpan sebagai riwayat tanpa memundurkan status shipment.';
            }

            return redirect()
                ->to(
    site_url(
        'shipments/detail/' . (int) $shipmentId
    )
    . '?tab=vendor'
    . '&anchor=add-vendor-tracking'
    . '#add-vendor-tracking'
)
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()
               ->to(
    site_url(
        'shipments/detail/' . (int) $shipmentId
    )
    . '?tab=vendor'
    . '&anchor=add-vendor-tracking'
    . '#add-vendor-tracking'
)
                ->withInput()
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

    private function requireHeadOfficeActor(): array
{
    $actorId = (int) (auth()->id() ?? 0);

    if ($actorId <= 0) {
        throw new \RuntimeException(
            'Sesi user tidak ditemukan. Silakan login ulang.'
        );
    }

    $context = new \App\Services\BranchOperationalContextService();
    $profile = $context->requireProfile($actorId);

    if (!$context->profileIsMain($profile)) {
        throw new \RuntimeException(
            'Vendor workflow hanya boleh dikelola oleh JKT-HO / Head Office.'
        );
    }

    return [
        'actor_id' => $actorId,
        'profile'  => $profile,
    ];
}
}
