<?php

namespace App\Services;

use App\Models\AgentInvoiceDetailModel;
use App\Models\AgentInvoiceModel;
use App\Models\AgentModel;
use App\Models\ShipmentModel;
use App\Models\UserOperationalProfileModel;

class AgentSelfBillingService
{
    public const AUTO_ISSUE = 'AUTO_ISSUE';
    public const REQUIRE_APPROVAL = 'REQUIRE_APPROVAL';

    private const ELIGIBLE_STATUSES = [
        'RECEIVED_AT_ORIGIN_BRANCH',
        'MANIFESTED',
        'SENT_TO_MAIN_WAREHOUSE',
        'RECEIVED_AT_MAIN_WAREHOUSE',
        'SUBMITTED_TO_VENDOR',
        'VENDOR_PICKED_UP',
        'DEPARTED_ORIGIN',
        'ARRIVED_AT_TRANSIT_HUB',
        'DEPARTED_TRANSIT_HUB',
        'ARRIVED_DESTINATION_COUNTRY',
        'CUSTOMS_CLEARANCE_STARTED',
        'CUSTOMS_CLEARANCE_COMPLETED',
        'CUSTOMS_EXCEPTION',
        'HANDED_TO_LAST_MILE',
        'OUT_FOR_DELIVERY',
        'DELIVERED',
    ];

    public function requireAgentContext(int $actorId): array
    {
        if ($actorId <= 0) {
            throw new \RuntimeException('Sesi user tidak ditemukan.');
        }

        $profile = (new UserOperationalProfileModel())
            ->where('user_id', $actorId)
            ->where('status', 1)
            ->first();

        if (!$profile) {
            throw new \RuntimeException('User belum mempunyai Operational Profile aktif.');
        }

        if (strtoupper((string) ($profile['staff_type'] ?? '')) === 'HEAD_OFFICE') {
            throw new \RuntimeException('Self Billing hanya tersedia untuk akun agent/cabang.');
        }

        $agentId = (int) ($profile['agent_id'] ?? 0);

        if ($agentId <= 0) {
            throw new \RuntimeException('Operational Profile belum terhubung ke agent.');
        }

        $agent = (new AgentModel())
            ->where('id', $agentId)
            ->where('status', 1)
            ->first();

        if (!$agent) {
            throw new \RuntimeException('Agent tidak ditemukan atau tidak aktif.');
        }

        if ((int) ($agent['self_billing_enabled'] ?? 0) !== 1) {
            throw new \RuntimeException('Self Billing belum diaktifkan untuk agent ini.');
        }

        return [
            'actor_id' => $actorId,
            'profile'  => $profile,
            'agent_id' => $agentId,
            'agent'    => $agent,
        ];
    }

    public function eligibleShipments(int $agentId): array
    {
        return (new ShipmentModel())
            ->where('agent_id', $agentId)
            ->whereIn('shipment_status', self::ELIGIBLE_STATUSES)
            ->groupStart()
                ->where('is_agent_invoiced', 0)
                ->orWhere('is_agent_invoiced', null)
            ->groupEnd()
            ->where('agent_invoice_id', null)
            ->where('total_charge >', 0)
            ->orderBy('shipment_date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function invoicesForAgent(int $agentId): array
    {
        return (new AgentInvoiceModel())
            ->where('agent_id', $agentId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function invoiceForAgent(int $invoiceId, int $agentId): ?array
    {
        $invoice = (new AgentInvoiceModel())
            ->where('id', $invoiceId)
            ->where('agent_id', $agentId)
            ->first();

        if (!$invoice) {
            return null;
        }

        $invoice['details'] = (new AgentInvoiceDetailModel())
            ->where('agent_invoice_id', $invoiceId)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $invoice;
    }

    public function createInvoice(int $actorId, array $shipmentIds): array
    {
        $context = $this->requireAgentContext($actorId);
        $agent   = $context['agent'];
        $agentId = (int) $context['agent_id'];

        $shipmentIds = array_values(array_unique(array_filter(
            array_map('intval', $shipmentIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($shipmentIds === []) {
            throw new \RuntimeException('Pilih minimal satu shipment.');
        }

        if (count($shipmentIds) > 200) {
            throw new \RuntimeException('Maksimal 200 shipment per invoice.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $locked = [];
            $total  = 0.0;

            foreach ($shipmentIds as $shipmentId) {
                $shipment = $db->query(
                    'SELECT * FROM shipments WHERE id = ? FOR UPDATE',
                    [$shipmentId]
                )->getRowArray();

                $this->assertEligibleShipment($shipment, $agentId);

                $lineTotal = round((float) (
                    $shipment['total_charge']
                    ?? $shipment['selling_amount']
                    ?? 0
                ), 2);

                if ($lineTotal <= 0) {
                    throw new \RuntimeException(
                        'Shipment ' . ($shipment['awb_no'] ?? '#' . $shipmentId) .
                        ' belum mempunyai total tagihan.'
                    );
                }

                $shipment['_line_total'] = $lineTotal;
                $locked[] = $shipment;
                $total += $lineTotal;
            }

            $mode = strtoupper((string) (
                $agent['self_billing_mode'] ?? self::REQUIRE_APPROVAL
            ));

            if (!in_array($mode, [self::AUTO_ISSUE, self::REQUIRE_APPROVAL], true)) {
                $mode = self::REQUIRE_APPROVAL;
            }

            $autoIssue = $mode === self::AUTO_ISSUE;
            $now = date('Y-m-d H:i:s');
            $invoiceDate = date('Y-m-d');
            $termDays = max(0, (int) ($agent['payment_term_days'] ?? 7));
            $dueDate = date('Y-m-d', strtotime('+' . $termDays . ' days'));

            $invoiceModel = new AgentInvoiceModel();
            $invoiceId = $invoiceModel->insert([
                'agent_id'        => $agentId,
                'invoice_no'      => $this->nextInvoiceNo(),
                'invoice_date'    => $invoiceDate,
                'due_date'        => $dueDate,
                'total_amount'    => round($total, 2),
                'paid_amount'     => 0,
                'balance_amount'  => round($total, 2),
                'invoice_status'  => $autoIssue ? 'UNPAID' : 'PENDING_APPROVAL',
                'source'          => 'AGENT_SELF_BILLING',
                'approval_status' => $autoIssue ? 'APPROVED' : 'PENDING_APPROVAL',
                'requested_by'    => $actorId,
                'requested_at'    => $now,
                'approved_by'     => $autoIssue ? $actorId : null,
                'approved_at'     => $autoIssue ? $now : null,
                'issued_by'       => $autoIssue ? $actorId : null,
                'issued_at'       => $autoIssue ? $now : null,
            ], true);

            if (!$invoiceId) {
                throw new \RuntimeException('Gagal membuat agent invoice.');
            }

            $detailModel   = new AgentInvoiceDetailModel();
            $shipmentModel = new ShipmentModel();

            foreach ($locked as $shipment) {
                $shipmentId = (int) $shipment['id'];

                $detailModel->insert([
                    'agent_invoice_id' => $invoiceId,
                    'shipment_id'      => $shipmentId,
                    'awb_no'           => (string) ($shipment['awb_no'] ?? ''),
                    'shipment_date'    => $shipment['shipment_date'] ?? null,
                    'chargeable_weight'=> round((float) ($shipment['chargeable_weight'] ?? 0), 2),
                    'freight_amount'   => round((float) ($shipment['freight_charge'] ?? $shipment['selling_amount'] ?? 0), 2),
                    'insurance_amount' => round((float) ($shipment['insurance_amount'] ?? 0), 2),
                    'packing_amount'   => round((float) ($shipment['packing_charge'] ?? 0), 2),
                    'additional_amount'=> round((float) ($shipment['additional_charge'] ?? 0), 2),
                    'discount_amount'  => round((float) ($shipment['discount_amount'] ?? 0), 2),
                    'line_total'       => $shipment['_line_total'],
                ]);

                $shipmentModel->update($shipmentId, [
                    'agent_invoice_id'   => $invoiceId,
                    'is_agent_invoiced' => 1,
                    'agent_invoiced_at' => $now,
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database menolak proses Self Billing.');
            }

            $db->transCommit();

            return [
                'invoice_id'      => (int) $invoiceId,
                'approval_status' => $autoIssue ? 'APPROVED' : 'PENDING_APPROVAL',
                'total_amount'    => round($total, 2),
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    private function assertEligibleShipment(?array $shipment, int $agentId): void
    {
        if (!$shipment) {
            throw new \RuntimeException('Shipment tidak ditemukan.');
        }

        if ((int) ($shipment['agent_id'] ?? 0) !== $agentId) {
            throw new \RuntimeException('Shipment bukan milik agent yang sedang login.');
        }

        $status = strtoupper((string) ($shipment['shipment_status'] ?? ''));

        if (!in_array($status, self::ELIGIBLE_STATUSES, true)) {
            throw new \RuntimeException(
                'Shipment ' . ($shipment['awb_no'] ?? '') . ' belum dapat ditagihkan.'
            );
        }

        if ((int) ($shipment['is_agent_invoiced'] ?? 0) === 1
            || !empty($shipment['agent_invoice_id'])) {
            throw new \RuntimeException(
                'Shipment ' . ($shipment['awb_no'] ?? '') . ' sudah masuk agent invoice.'
            );
        }
    }

    private function nextInvoiceNo(): string
    {
        $model = new AgentInvoiceModel();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $number = 'AINV' . date('ymdHis') . random_int(10, 99);

            if (!$model->where('invoice_no', $number)->first()) {
                return $number;
            }
        }

        throw new \RuntimeException('Gagal membuat nomor invoice unik.');
    }
}
