<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\AgentSelfBillingService;

class AgentSelfBillingController extends BaseController
{
    private AgentSelfBillingService $service;

    public function __construct()
    {
        $this->service = new AgentSelfBillingService();
    }

    public function index()
    {
        try {
            $context = $this->service->requireAgentContext($this->actorId());
            $agentId = (int) $context['agent_id'];
            $shipments = $this->service->eligibleShipments($agentId);
            $invoices  = $this->service->invoicesForAgent($agentId);

            $summary = [
                'unbilled' => array_sum(array_map(
                    static fn (array $row): float => (float) ($row['total_charge'] ?? 0),
                    $shipments
                )),
                'outstanding' => 0.0,
                'pending_approval' => 0,
                'paid' => 0.0,
            ];

            foreach ($invoices as $invoice) {
                $summary['outstanding'] += (float) ($invoice['balance_amount'] ?? 0);

                if (strtoupper((string) ($invoice['approval_status'] ?? '')) === 'PENDING_APPROVAL') {
                    $summary['pending_approval']++;
                }

                if (strtoupper((string) ($invoice['invoice_status'] ?? '')) === 'PAID') {
                    $summary['paid'] += (float) ($invoice['total_amount'] ?? 0);
                }
            }

            return view('agent/self_billing/index', [
                'agent'     => $context['agent'],
                'profile'   => $context['profile'],
                'shipments' => $shipments,
                'invoices'  => $invoices,
                'summary'   => $summary,
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('shipments'))->with('error', $e->getMessage());
        }
    }

    public function createInvoice()
    {
        try {
            $result = $this->service->createInvoice(
                $this->actorId(),
                (array) ($this->request->getPost('shipment_ids') ?? [])
            );

            $message = $result['approval_status'] === 'APPROVED'
                ? 'Agent invoice berhasil diterbitkan.'
                : 'Self Billing berhasil dibuat dan menunggu approval Finance.';

            return redirect()
                ->to(site_url('agent/self-billing/invoice/' . (int) $result['invoice_id']))
                ->with('success', $message);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($invoiceId)
    {
        return $this->renderInvoice((int) $invoiceId, 'agent/self_billing/show');
    }

    public function print($invoiceId)
    {
        return $this->renderInvoice((int) $invoiceId, 'agent/self_billing/print');
    }

    private function renderInvoice(int $invoiceId, string $view)
    {
        $context = $this->service->requireAgentContext($this->actorId());
        $invoice = $this->service->invoiceForAgent($invoiceId, (int) $context['agent_id']);

        if (!$invoice) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Agent invoice tidak ditemukan.'
            );
        }

        return view($view, [
            'agent'   => $context['agent'],
            'invoice' => $invoice,
        ]);
    }

    private function actorId(): int
    {
        $actorId = (int) (auth()->id() ?? 0);

        if ($actorId <= 0) {
            throw new \RuntimeException('Sesi user tidak ditemukan.');
        }

        return $actorId;
    }
}
