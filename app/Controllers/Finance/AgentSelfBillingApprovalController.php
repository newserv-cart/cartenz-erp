<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\AgentInvoiceModel;
use App\Models\ShipmentModel;
use App\Services\BranchOperationalContextService;

class AgentSelfBillingApprovalController extends BaseController
{
    public function index()
    {
        $this->requireHeadOfficeActor();

        $invoices = (new AgentInvoiceModel())
            ->select([
                'agent_invoices.*',
                'agents.agent_code',
                'agents.agent_name',
            ])
            ->join('agents', 'agents.id = agent_invoices.agent_id', 'left')
            ->where('agent_invoices.source', 'AGENT_SELF_BILLING')
            ->orderBy('agent_invoices.id', 'DESC')
            ->findAll();

        return view('finance/agent_self_billing/index', [
            'invoices' => $invoices,
        ]);
    }

    public function approve($invoiceId)
    {
        try {
            $actorId = $this->requireHeadOfficeActor();
            $db = db_connect();
            $db->transBegin();

            $invoice = $db->query(
                'SELECT * FROM agent_invoices WHERE id = ? FOR UPDATE',
                [(int) $invoiceId]
            )->getRowArray();

            $this->assertPending($invoice);
            $now = date('Y-m-d H:i:s');

            (new AgentInvoiceModel())->update((int) $invoice['id'], [
                'approval_status' => 'APPROVED',
                'invoice_status'  => 'UNPAID',
                'approved_by'     => $actorId,
                'approved_at'     => $now,
                'issued_by'       => $actorId,
                'issued_at'       => $now,
                'rejected_by'     => null,
                'rejected_at'     => null,
                'rejection_reason'=> null,
            ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database menolak approval invoice.');
            }

            $db->transCommit();

            return redirect()->back()->with(
                'success',
                'Self Billing agent berhasil disetujui dan diterbitkan.'
            );
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject($invoiceId)
    {
        try {
            $actorId = $this->requireHeadOfficeActor();
            $reason = trim((string) ($this->request->getPost('rejection_reason') ?? ''));

            if (mb_strlen($reason) < 10) {
                throw new \RuntimeException('Alasan penolakan minimal 10 karakter.');
            }

            $db = db_connect();
            $db->transBegin();

            $invoice = $db->query(
                'SELECT * FROM agent_invoices WHERE id = ? FOR UPDATE',
                [(int) $invoiceId]
            )->getRowArray();

            $this->assertPending($invoice);

            $details = $db->table('agent_invoice_details')
                ->select('shipment_id')
                ->where('agent_invoice_id', (int) $invoice['id'])
                ->get()
                ->getResultArray();

            $shipmentModel = new ShipmentModel();

            foreach ($details as $detail) {
                $shipmentModel->update((int) $detail['shipment_id'], [
                    'agent_invoice_id'   => null,
                    'is_agent_invoiced' => 0,
                    'agent_invoiced_at' => null,
                ]);
            }

            (new AgentInvoiceModel())->update((int) $invoice['id'], [
                'approval_status' => 'REJECTED',
                'invoice_status'  => 'REJECTED',
                'rejected_by'     => $actorId,
                'rejected_at'     => date('Y-m-d H:i:s'),
                'rejection_reason'=> $reason,
            ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database menolak rejection invoice.');
            }

            $db->transCommit();

            return redirect()->back()->with(
                'success',
                'Self Billing ditolak. Shipment telah dilepas agar dapat diajukan ulang.'
            );
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function assertPending(?array $invoice): void
    {
        if (!$invoice) {
            throw new \RuntimeException('Agent invoice tidak ditemukan.');
        }

        if (strtoupper((string) ($invoice['source'] ?? '')) !== 'AGENT_SELF_BILLING') {
            throw new \RuntimeException('Invoice bukan berasal dari Self Billing.');
        }

        if (strtoupper((string) ($invoice['approval_status'] ?? '')) !== 'PENDING_APPROVAL') {
            throw new \RuntimeException('Invoice sudah pernah diproses.');
        }
    }

    private function requireHeadOfficeActor(): int
    {
        $actorId = (int) (auth()->id() ?? 0);

        if ($actorId <= 0) {
            throw new \RuntimeException('Sesi user tidak ditemukan.');
        }

        $context = new BranchOperationalContextService();
        $profile = $context->requireProfile($actorId);

        if (!$context->profileIsMain($profile)) {
            throw new \RuntimeException(
                'Approval Self Billing hanya boleh dilakukan JKT-HO / Head Office.'
            );
        }

        return $actorId;
    }
}
