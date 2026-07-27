<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentModel;
use App\Models\InboundIntakeModel;
use App\Models\InboundIntakePackageModel;
use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;
use App\Services\BranchOperationalContextService;
use App\Services\RegularCalculationService;
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

    public function receivePickup(int $shipmentId)
    {
        $context = new BranchOperationalContextService();
        $actorId = $this->requireActorId();
        $profile = $context->requireProfile($actorId);

        $db    = db_connect();
        $now   = date('Y-m-d H:i:s');
        $userId = $actorId;

        $db->transBegin();

        try {
            $shipment = $db->query(
                'SELECT * FROM shipments WHERE id = ? FOR UPDATE',
                [$shipmentId]
            )->getRowArray();

            if (!$shipment) {
                throw PageNotFoundException::forPageNotFound(
                    'Shipment tidak ditemukan.'
                );
            }

            if (
                strtoupper(
                    (string) ($shipment['shipment_status'] ?? '')
                ) !== 'PICKED_UP'
            ) {
                throw new \RuntimeException(
                    'Shipment tidak berstatus PICKED_UP.'
                );
            }

            if (
                strtoupper(
                    (string) ($shipment['handover_method'] ?? '')
                ) !== 'CUSTOMER_PICKUP_REQUEST'
            ) {
                throw new \RuntimeException(
                    'Shipment bukan berasal dari customer pickup request.'
                );
            }

            $branchId = (int) ($profile['branch_id'] ?? 0);
            $branch   = $context->branchById($branchId);

            if (!$branch) {
                throw new \RuntimeException(
                    'Branch user tidak valid.'
                );
            }

            if ($context->isMainBranch($branch)) {
                throw new \RuntimeException(
                    'Penerimaan pickup hanya boleh dilakukan di origin branch.'
                );
            }

            $location = $context->locationLabel($branch);

            $verifiedActualWeight = (float) $this->request->getPost('verified_actual_weight');

            if ($verifiedActualWeight <= 0) {
                throw new \RuntimeException(
                    'Berat actual terverifikasi harus lebih dari 0 Kg.'
                );
            }

            $verifiedLength = (float) $this->request->getPost('verified_length_cm');
            $verifiedWidth  = (float) $this->request->getPost('verified_width_cm');
            $verifiedHeight = (float) $this->request->getPost('verified_height_cm');

            if ($verifiedLength > 0 && $verifiedWidth > 0 && $verifiedHeight > 0) {
                $verifiedVolumeWeight = round(
                    ($verifiedLength * $verifiedWidth * $verifiedHeight) / 5000,
                    2
                );
            } else {
                $verifiedVolumeWeight = (float) ($shipment['volume_weight'] ?? 0);
            }

            $verifiedChargeableWeight = max(
                $verifiedActualWeight,
                $verifiedVolumeWeight
            );

            if ($verifiedChargeableWeight <= 0) {
                throw new \RuntimeException(
                    'Chargeable weight terverifikasi tidak valid.'
                );
            }

            $verifiedColly = (int) $this->request->getPost('verified_total_colly');

            if ($verifiedColly <= 0) {
                $verifiedColly = (int) ($shipment['total_colly'] ?? 1);

                if ($verifiedColly <= 0) {
                    $verifiedColly = 1;
                }
            }

            $ownerType = strtoupper(
                trim((string) ($shipment['owner_type'] ?? 'CUSTOMER'))
            );

            $ownerId = (int) (
                $shipment['owner_id']
                ?? (
                    $ownerType === 'AGENT'
                        ? ($shipment['agent_id'] ?? 0)
                        : ($shipment['customer_id'] ?? 0)
                )
            );

            $pricing = (new RegularCalculationService())->calculate([
                'owner_type'         => $ownerType,
                'owner_id'           => $ownerId,
                'customer_id'        =>
                    $ownerType === 'CUSTOMER'
                        ? (int) ($shipment['customer_id'] ?? 0)
                        : null,
                'agent_id'           =>
                    $ownerType === 'AGENT'
                        ? (int) ($shipment['agent_id'] ?? 0)
                        : null,
                'country_id'         => (int) ($shipment['country_id'] ?? 0),
                'service_id'         => (int) ($shipment['service_id'] ?? 0),
                'transport_mode_id'  => (int) ($shipment['transport_mode_id'] ?? 0),
                'delivery_type_id'   => (int) ($shipment['delivery_type_id'] ?? 0),
                'package_type_id'    => (int) ($shipment['package_type_id'] ?? 0),
                'commodity_type_id'  => (int) ($shipment['commodity_type_id'] ?? 0),
                'actual_weight'      => $verifiedActualWeight,
                'volume_weight'      => $verifiedVolumeWeight,
                'chargeable_weight'  => $verifiedChargeableWeight,
                'total_colly'        => $verifiedColly,
                'shipment_date'      => $shipment['shipment_date'] ?? date('Y-m-d'),
                'insurance_amount'   => (float) ($shipment['insurance_amount'] ?? 0),
                'packing_charge'     => (float) ($shipment['packing_charge'] ?? 0),
                'additional_charge'  => (float) ($shipment['additional_charge'] ?? 0),
                'discount_amount'    => (float) ($shipment['discount_amount'] ?? 0),
            ]);

            $freightCharge    = (float) ($pricing['freight'] ?? $pricing['freight_charge'] ?? $pricing['selling_amount'] ?? 0);
            $remoteCharge     = (float) ($pricing['remote_charge'] ?? $pricing['remote'] ?? 0);
            $packingCharge    = (float) ($pricing['packing'] ?? $pricing['packing_charge'] ?? 0);
            $additionalCharge = (float) ($pricing['additional'] ?? $pricing['additional_charge'] ?? 0);
            $discountAmount   = (float) ($pricing['discount'] ?? $pricing['discount_amount'] ?? 0);
            $finalTotal       = (float) ($pricing['customer_total'] ?? 0);
            $vendorTotal      = (float) ($pricing['vendor_total'] ?? 0);

            if ($finalTotal <= 0) {
                throw new \RuntimeException(
                    'Tarif final tidak ditemukan. Pricing belum dapat difinalkan.'
                );
            }

            $shipmentModel = new ShipmentModel();

            if (!$shipmentModel->update($shipmentId, [
                'shipment_status'        => 'RECEIVED_AT_ORIGIN_BRANCH',
                'public_tracking_status' => 'PICKED_UP',
                'pricing_status'         => 'FINAL',
                'acceptance_method'      => ShipmentAcceptanceService::COURIER_PICKUP,
                'acceptance_branch_id'   => $branchId,
                'receiving_branch_id'    => $branchId,
                'current_branch_id'      => $branchId,
                'receiving_location'     => $location,
                'actual_weight'          => $verifiedActualWeight,
                'volume_weight'          => $verifiedVolumeWeight,
                'chargeable_weight'      => $verifiedChargeableWeight,
                'total_colly'            => $verifiedColly,
                'freight_charge'         => $freightCharge,
                'remote_charge'          => $remoteCharge,
                'packing_charge'         => $packingCharge,
                'additional_charge'      => $additionalCharge,
                'discount_amount'        => $discountAmount,
                'total_charge'           => $finalTotal,
                'selling_amount'         => $freightCharge,
                'vendor_cost'            => $vendorTotal,
                'vendor_freight'         => $vendorTotal,
                'vendor_total'           => $vendorTotal,
                'profit_amount'          => (float) ($pricing['profit'] ?? 0),
                'margin_percent'         => (float) ($pricing['margin'] ?? 0),
                'markup_percent'         => (float) ($pricing['markup'] ?? 0),
                'received_at'            => $now,
                'received_by'            => $userId,
                'last_scan_branch_id'    => $branchId,
                'last_scan_type'         => 'PICKUP_RECEIVE',
                'last_scan_at'           => $now,
                'last_scan_by'           => $userId,
                'updated_at'             => $now,
            ])) {
                throw new \RuntimeException(
                    'Status shipment gagal diperbarui.'
                );
            }

            $tracking = new ShipmentTrackingModel();

            if (!$tracking->insert([
                'shipment_id'       => $shipmentId,
                'branch_id'         => $branchId,
                'tracking_code'     => $shipment['awb_no'],
                'tracking_status'   => 'RECEIVED_AT_ORIGIN_BRANCH',
                'public_status'     => 'PICKED_UP',
                'event_code'        => 'RECEIVED_AT_ORIGIN_BRANCH',
                'event_type'        => 'RECEIVED_AT_ORIGIN_BRANCH',
                'scan_type'         => 'PICKUP_RECEIVE',
                'source_reference'  => $shipment['awb_no'],
                'tracking_location' => $location,
                'tracking_notes'    => 'Shipment dari customer pickup request diterima di origin branch. Verifikasi operasional selesai, pricing status FINAL.',
                'event_time'        => $now,
                'created_by'        => $userId,
                'created_at'        => $now,
            ])) {
                throw new \RuntimeException(
                    'Tracking penerimaan origin branch gagal disimpan.'
                );
            }

            $history = new ShipmentHistoryModel();

            if (!$history->insert([
                'shipment_id'    => $shipmentId,
                'event_type'     => 'RECEIVED_AT_ORIGIN_BRANCH',
                'event_title'    => 'Received at Origin Branch',
                'description'    => 'Shipment dari customer pickup request diterima di origin branch. Verifikasi operasional selesai, pricing status FINAL.',
                'changed_fields' => json_encode([
                    'previous_status' => $shipment['shipment_status'],
                    'new_status'      => 'RECEIVED_AT_ORIGIN_BRANCH',
                    'pricing_status'  => 'FINAL',
                    'branch_id'       => $branchId,
                    'location'        => $location,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_by' => $userId,
                'created_at' => $now,
            ])) {
                throw new \RuntimeException(
                    'History penerimaan origin branch gagal disimpan.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penerimaan pickup gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('shipments/detail/' . $shipmentId))
                ->with(
                    'success',
                    'Shipment '
                        . ($shipment['awb_no'] ?? '')
                        . ' berhasil diterima di origin branch.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            if ($e instanceof PageNotFoundException) {
                throw $e;
            }

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
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
