<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\AgentInvoiceModel;
use App\Models\PaymentModel;
use App\Services\BranchOperationalContextService;

class AgentPaymentController extends BaseController
{
    public function index()
    {
        try {
            $this->requireHeadOfficeActor();
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('agent/self-billing'))
                ->with(
                    'error',
                    'Halaman verifikasi pembayaran hanya tersedia untuk JKT-HO / Finance.'
                );
        }

        $payments = (new PaymentModel())
            ->select([
                'payments.*',
                'agent_invoices.invoice_no',
                'agents.agent_code',
                'agents.agent_name',
            ])
            ->join(
                'agent_invoices',
                'agent_invoices.id = payments.agent_invoice_id',
                'left'
            )
            ->join(
                'agents',
                'agents.id = agent_invoices.agent_id',
                'left'
            )
            ->where('payments.payment_type', 'AGENT')
            ->orderBy('payments.id', 'DESC')
            ->findAll();

        return view('finance/agent_payments/index', [
            'payments' => $payments,
        ]);
    }

    public function approve($id)
    {
        try {
            $headOffice = $this->requireHeadOfficeActor();
            $actorId    = (int) $headOffice['actor_id'];
            $db = db_connect();
            $db->transBegin();

            $payment = $db->query(
                'SELECT * FROM payments WHERE id = ? FOR UPDATE',
                [(int) $id]
            )->getRowArray();

            if (!$payment
                || strtoupper((string) ($payment['payment_type'] ?? '')) !== 'AGENT') {
                throw new \RuntimeException('Agent payment tidak ditemukan.');
            }

            if (strtoupper((string) ($payment['status'] ?? '')) !== 'PENDING') {
                throw new \RuntimeException('Payment sudah pernah diproses.');
            }

            $invoiceId = (int) ($payment['agent_invoice_id'] ?? 0);
            $invoice = $db->query(
                'SELECT * FROM agent_invoices WHERE id = ? FOR UPDATE',
                [$invoiceId]
            )->getRowArray();

            if (!$invoice) {
                throw new \RuntimeException('Agent invoice tidak ditemukan.');
            }

            if (strtoupper((string) ($invoice['approval_status'] ?? '')) !== 'APPROVED') {
                throw new \RuntimeException('Agent invoice belum disetujui.');
            }

            $amount  = round((float) ($payment['amount'] ?? 0), 2);
            $balance = round((float) ($invoice['balance_amount'] ?? 0), 2);

            if ($amount <= 0 || $amount > $balance) {
                throw new \RuntimeException('Nominal payment tidak valid terhadap balance invoice.');
            }

            $newPaid = round((float) ($invoice['paid_amount'] ?? 0) + $amount, 2);
            $newBalance = max(0, round($balance - $amount, 2));
            $invoiceStatus = $newBalance <= 0 ? 'PAID' : 'PARTIAL';

            (new PaymentModel())->update((int) $payment['id'], [
                'status' => 'APPROVED',
            ]);

            (new AgentInvoiceModel())->update($invoiceId, [
                'paid_amount'    => $newPaid,
                'balance_amount' => $newBalance,
                'invoice_status' => $invoiceStatus,
            ]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Database menolak approval payment.');
            }

            $db->transCommit();

            return redirect()->back()->with('success', 'Agent payment berhasil disetujui.');
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->transRollback();
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject($id)
    {
        try {
            $headOffice = $this->requireHeadOfficeActor();
            $actorId    = (int) $headOffice['actor_id'];
            $model = new PaymentModel();
            $payment = $model->find((int) $id);

            if (!$payment
                || strtoupper((string) ($payment['payment_type'] ?? '')) !== 'AGENT') {
                throw new \RuntimeException('Agent payment tidak ditemukan.');
            }

            if (strtoupper((string) ($payment['status'] ?? '')) !== 'PENDING') {
                throw new \RuntimeException('Payment sudah pernah diproses.');
            }

            $model->update((int) $id, ['status' => 'REJECTED']);

            return redirect()->back()->with('success', 'Agent payment ditolak.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function requireHeadOfficeActor(): array
    {
        $actorId = (int) (auth()->id() ?? 0);

        if ($actorId <= 0) {
            throw new \RuntimeException('Sesi user tidak ditemukan.');
        }

        $context = new BranchOperationalContextService();
        $profile = $context->requireProfile($actorId);

        if (!$context->profileIsMain($profile)) {
            throw new \RuntimeException(
                'Finance Agent hanya boleh dikelola oleh JKT-HO / Head Office.'
            );
        }

        return [
            'actor_id' => $actorId,
            'profile'  => $profile,
        ];
    }
}
