<?php

namespace App\Controllers\Operations;

use App\Controllers\BaseController;
use App\Services\BranchOperationalContextService;
use App\Services\BranchScanService;

class BranchScanController extends BaseController
{
    public function index()
    {
        try {
            $actorId = $this->requireActorId();

            return view(
                'operations/branch_scan/index',
                [
                    'profile' =>
                        (new BranchOperationalContextService())
                            ->requireProfile($actorId),
                ]
            );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments'))
                ->with('error', $e->getMessage());
        }
    }

    public function submit()
    {
        try {
            $result = (new BranchScanService())
                ->scanByAwb(
                    (string) $this->request
                        ->getPost('awb_no'),
                    (string) $this->request
                        ->getPost('scan_type'),
                    $this->requireActorId(),
                    (string) $this->request
                        ->getPost('notes')
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
                    sprintf(
                        '%s berhasil. %s sekarang berstatus %s di %s.',
                        $result['scan_type'],
                        $result['awb_no'],
                        $result['new_status'],
                        $result['branch_label']
                    )
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
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
