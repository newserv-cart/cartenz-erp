<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ManifestModel;
use App\Services\ManifestWorkflowService;
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
        return view('manifests/index', [
            'manifests' => $this->manifestModel
                ->orderBy('id', 'DESC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        return view('manifests/create');
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

        return view('manifests/view', [
            'manifest'          => $manifest,
            'details'           => $details,
            'liveTotals'        => $liveTotals,
            'eligibleShipments' => $eligibleShipments,
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
