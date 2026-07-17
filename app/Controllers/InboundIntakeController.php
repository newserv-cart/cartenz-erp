<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Models\InboundIntakeModel;
use App\Models\InboundIntakePackageModel;
use App\Services\ShipmentAcceptanceService;
use CodeIgniter\Exceptions\PageNotFoundException;

class InboundIntakeController extends BaseController
{
    protected InboundIntakeModel $intakeModel;
    protected InboundIntakePackageModel $packageModel;
    protected ShipmentAcceptanceService $acceptanceService;

    public function __construct()
    {
        $this->intakeModel       = new InboundIntakeModel();
        $this->packageModel      = new InboundIntakePackageModel();
        $this->acceptanceService = new ShipmentAcceptanceService();
    }

    public function index()
    {
        $rows = db_connect()->table('inbound_intakes ii')
            ->select('ii.*, a.agent_code, a.agent_name')
            ->join('agents a', 'a.id = ii.agent_id', 'left')
            ->orderBy('ii.id', 'DESC')
            ->get()
            ->getResultArray();

        return view('inbound_intakes/index', [
            'intakes' => $rows,
        ]);
    }

    public function create()
    {
        return view('inbound_intakes/create', [
            'agents'   => (new AgentModel())
                ->where('status', 1)
                ->orderBy('agent_name', 'ASC')
                ->findAll(),
            'branches' => $this->acceptanceService->branchOptions(),
        ]);
    }

    public function store()
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $actorId = $this->requireActorId();
            $post    = $this->request->getPost();

            $agentId = (int) ($post['agent_id'] ?? 0);
            $agent = (new AgentModel())
                ->where('id', $agentId)
                ->where('status', 1)
                ->first();

            if (!$agent) {
                throw new \RuntimeException('Agent wajib dipilih dan harus aktif.');
            }

            $arrivalDate = trim((string) ($post['arrival_date'] ?? ''));
            $date = \DateTimeImmutable::createFromFormat('Y-m-d', $arrivalDate);

            if (!$date || $date->format('Y-m-d') !== $arrivalDate) {
                throw new \RuntimeException('Arrival Date tidak valid.');
            }

            $location = trim((string) ($post['receiving_location'] ?? ''));

            if ($location === '') {
                throw new \RuntimeException('Receiving Location wajib diisi.');
            }

            $rawPackages = is_array($post['packages'] ?? null)
                ? $post['packages']
                : [];

            $packages = [];
            $receivedWeight = 0.0;

            foreach (array_values($rawPackages) as $index => $raw) {
                if (!is_array($raw)) {
                    continue;
                }

                $weight = max(0, (float) ($raw['actual_weight'] ?? 0));

                if ($weight <= 0) {
                    throw new \RuntimeException(
                        'Berat package #' . ($index + 1) . ' harus lebih dari 0.'
                    );
                }

                $packages[] = [
                    'package_no'    => $index + 1,
                    'package_barcode'=> $this->nullableText($raw['package_barcode'] ?? null),
                    'actual_weight' => round($weight, 2),
                    'length_cm'     => round(max(0, (float) ($raw['length_cm'] ?? 0)), 2),
                    'width_cm'      => round(max(0, (float) ($raw['width_cm'] ?? 0)), 2),
                    'height_cm'     => round(max(0, (float) ($raw['height_cm'] ?? 0)), 2),
                    'remarks'       => $this->nullableText($raw['remarks'] ?? null),
                ];

                $receivedWeight += $weight;
            }

            if ($packages === []) {
                throw new \RuntimeException('Minimal satu package wajib dicatat.');
            }

            $now = date('Y-m-d H:i:s');
            $intakeId = $this->intakeModel->insert([
                'intake_no'             => $this->generateIntakeNumber(),
                'acceptance_method'     => ShipmentAcceptanceService::AGENT_HANDOVER_NO_AWB,
                'agent_id'              => $agentId,
                'origin_branch_id'      => $this->nullablePositiveInt($post['origin_branch_id'] ?? null),
                'receiving_branch_id'   => $this->nullablePositiveInt($post['receiving_branch_id'] ?? null),
                'receiving_location'    => $location,
                'arrival_date'          => $arrivalDate,
                'agent_reference_no'    => $this->nullableText($post['agent_reference_no'] ?? null),
                'total_colly'           => count($packages),
                'declared_weight'       => round(max(0, (float) ($post['declared_weight'] ?? 0)), 2),
                'received_weight'       => round($receivedWeight, 2),
                'intake_status'         => 'READY_TO_CONVERT',
                'received_by'           => $actorId,
                'received_at'           => $now,
                'notes'                 => $this->nullableText($post['notes'] ?? null),
            ], true);

            if (!$intakeId) {
                throw new \RuntimeException('Inbound Intake gagal disimpan.');
            }

            foreach ($packages as $package) {
                $package['inbound_intake_id'] = (int) $intakeId;

                if (!$this->packageModel->insert($package)) {
                    throw new \RuntimeException('Package intake gagal disimpan.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi Inbound Intake gagal.');
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('inbound-intakes/' . $intakeId))
                ->with('success', 'Inbound Intake berhasil dibuat.');
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $intake = db_connect()->table('inbound_intakes ii')
            ->select('ii.*, a.agent_code, a.agent_name')
            ->join('agents a', 'a.id = ii.agent_id', 'left')
            ->where('ii.id', (int) $id)
            ->get()
            ->getRowArray();

        if (!$intake) {
            throw PageNotFoundException::forPageNotFound('Inbound Intake tidak ditemukan.');
        }

        return view('inbound_intakes/show', [
            'intake'  => $intake,
            'packages'=> $this->packageModel
                ->where('inbound_intake_id', (int) $id)
                ->orderBy('package_no', 'ASC')
                ->findAll(),
        ]);
    }

    public function startShipment($id)
    {
        $intake = $this->intakeModel->find((int) $id);

        if (!$intake) {
            throw PageNotFoundException::forPageNotFound('Inbound Intake tidak ditemukan.');
        }

        if (strtoupper((string) $intake['intake_status']) !== 'READY_TO_CONVERT') {
            return redirect()->back()->with(
                'error',
                'Intake tidak dalam status READY_TO_CONVERT.'
            );
        }

        if (!empty($intake['converted_shipment_id'])) {
            return redirect()->to(
                site_url('shipments/detail/' . $intake['converted_shipment_id'])
            );
        }

        return redirect()->to(
            site_url('shipments/create?intake_id=' . (int) $intake['id'])
        );
    }

    public function cancel($id)
    {
        $intake = $this->intakeModel->find((int) $id);

        if (!$intake) {
            throw PageNotFoundException::forPageNotFound('Inbound Intake tidak ditemukan.');
        }

        if (!empty($intake['converted_shipment_id'])) {
            return redirect()->back()->with(
                'error',
                'Intake yang sudah dikonversi tidak dapat dibatalkan.'
            );
        }

        if (!$this->intakeModel->update((int) $id, [
            'intake_status' => 'CANCELLED',
            'cancelled_by'  => $this->requireActorId(),
            'cancelled_at'  => date('Y-m-d H:i:s'),
        ])) {
            return redirect()->back()->with('error', 'Intake gagal dibatalkan.');
        }

        return redirect()->back()->with('success', 'Inbound Intake dibatalkan.');
    }

    private function generateIntakeNumber(): string
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $number = sprintf(
                'INB%s%03d',
                date('ymdHis'),
                random_int(1, 999)
            );

            if (!$this->intakeModel->where('intake_no', $number)->first()) {
                return $number;
            }
        }

        throw new \RuntimeException('Nomor Inbound Intake unik gagal dibuat.');
    }

    private function requireActorId(): int
    {
        $actorId = auth()->id();

        if (!$actorId) {
            throw new \RuntimeException('Sesi admin tidak ditemukan. Silakan login ulang.');
        }

        return (int) $actorId;
    }

    private function nullablePositiveInt($value): ?int
    {
        $value = (int) $value;
        return $value > 0 ? $value : null;
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }
}
