<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ManifestModel;
use App\Services\ManifestWorkflowService;
use App\Services\BranchOperationalContextService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;

class ManifestController extends BaseController
{
    protected ManifestWorkflowService $workflow;
    protected ManifestModel $manifestModel;

    public function __construct()
    {
        $this->workflow      = new ManifestWorkflowService();
        $this->manifestModel = new ManifestModel();
    }

    public function index()
    {
        $manifests = db_connect()
            ->table('manifests m')
            ->select([
                'm.*',
                'ob.branch_name AS origin_branch_name',
                'db.branch_name AS destination_branch_name',
            ])
            ->join(
                'branches ob',
                'ob.id = m.origin_branch_id',
                'left'
            )
            ->join(
                'branches db',
                'db.id = m.destination_branch_id',
                'left'
            )
            ->orderBy('m.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('manifests/index', [
            'manifests' => $manifests,
        ]);
    }

    public function create()
    {
        try {
            $actorId = $this->requireActorId();
            $context = new BranchOperationalContextService();
            $profile = $context->requireProfile($actorId);

            return view('manifests/create', [
                'operationalProfile' => $profile,
                'originBranch' => $context->branchById(
                    (int) $profile['branch_id']
                ),
                'destinationBranches' =>
                    $context->destinationOptions(
                        (int) $profile['branch_id']
                    ),
                'mainBranch' => $context->mainBranch(),
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('manifests'))
                ->with('error', $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $manifestId = $this->workflow->createManifest(
                $this->request->getPost(),
                $this->requireActorId()
            );

            return redirect()
                ->to(site_url('manifests/view/' . $manifestId))
                ->with(
                    'success',
                    'Manifest berhasil dibuat.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function view($id)
    {
        $manifestId = (int) $id;
        $manifest   = $this->manifestModel->find(
            $manifestId
        );

        if (!$manifest) {
            throw PageNotFoundException::forPageNotFound(
                'Manifest tidak ditemukan.'
            );
        }

        $details = $this->workflow
            ->getManifestDetails($manifestId);

        $liveTotals = $this->workflow
            ->calculateLiveTotals($details);

        $eligibleShipments = [];

        if (
            strtoupper((string) $manifest['status'])
            === 'OPEN'
        ) {
            $eligibleShipments = $this->workflow
                ->getEligibleShipments($manifestId);
        }

        $context = new BranchOperationalContextService();

        return view('manifests/view', [
            'manifest'          => $manifest,
            'details'           => $details,
            'liveTotals'        => $liveTotals,
            'eligibleShipments' => $eligibleShipments,
            'originBranch'      => !empty(
                $manifest['origin_branch_id']
            )
                ? $context->branchById(
                    (int) $manifest['origin_branch_id']
                )
                : null,
            'destinationBranch' => !empty(
                $manifest['destination_branch_id']
            )
                ? $context->branchById(
                    (int) $manifest[
                        'destination_branch_id'
                    ]
                )
                : null,
            'operationalProfile'=> $context->profileForUser(
                $this->requireActorId()
            ),
        ]);
    }

    public function addShipment($manifestId)
    {
        try {
            $shipmentIds = $this->request
                ->getPost('shipment_ids');

            $added = $this->workflow->addShipments(
                (int) $manifestId,
                is_array($shipmentIds) ? $shipmentIds : [],
                $this->requireActorId()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    $added .
                    ' shipment berhasil ditambahkan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function removeShipment(
        $manifestId,
        $shipmentId
    ) {
        try {
            $this->workflow->removeShipment(
                (int) $manifestId,
                (int) $shipmentId,
                $this->requireActorId()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Shipment berhasil dilepas dari manifest.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function dispatch($manifestId)
    {
        try {
            $this->workflow->dispatch(
                (int) $manifestId,
                $this->requireActorId()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Manifest berhasil didispatch.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function receive($manifestId)
    {
        try {
            $this->workflow->receive(
                (int) $manifestId,
                $this->requireActorId()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Manifest berhasil diterima.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function close($manifestId)
    {
        try {
            $this->workflow->close(
                (int) $manifestId,
                $this->requireActorId()
            );

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Manifest berhasil ditutup dan dikunci.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function printManifest($id)
    {
        $manifestId = (int) $id;
        $manifest   = $this->manifestModel->find(
            $manifestId
        );

        if (!$manifest) {
            throw PageNotFoundException::forPageNotFound(
                'Manifest tidak ditemukan.'
            );
        }

        $details = $this->workflow
            ->getManifestDetails($manifestId);

        $html = view('manifests/pdf', [
            'manifest'   => $manifest,
            'details'    => $details,
            'liveTotals' => $this->workflow
                ->calculateLiveTotals($details),
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream(
            $manifest['manifest_no'] . '.pdf',
            ['Attachment' => false]
        );

        return null;
    }

    public function scan()
    {
        return view('manifests/scan');
    }

    public function scanSearch()
    {
        $manifestNo = strtoupper(
            trim(
                (string) $this->request
                    ->getPost('manifest_no')
            )
        );

        if ($manifestNo === '') {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Masukkan nomor manifest.'
                );
        }

        $manifest = $this->manifestModel
            ->where('manifest_no', $manifestNo)
            ->first();

        if (!$manifest) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Manifest tidak ditemukan.'
                );
        }

        return redirect()->to(
            site_url(
                'manifests/view/' . $manifest['id']
            )
        );
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
