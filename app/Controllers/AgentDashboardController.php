<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentInvoiceModel;
use App\Models\CustomerModel;
use App\Models\ShipmentModel;
use App\Services\AgentSelfBillingService;

class AgentDashboardController extends BaseController
{
    public function index()
    {
        try {
            $context = (new AgentSelfBillingService())->requireAgentContext(
                (int) (auth()->id() ?? 0)
            );

            $agentId = (int) $context['agent_id'];
            $customers = (new CustomerModel())
                ->where('agent_id', $agentId)
                ->findAll();

            $outstanding = (new AgentInvoiceModel())
                ->selectSum('balance_amount')
                ->where('agent_id', $agentId)
                ->first();

            return view('agent/dashboard', [
                'agent'          => $context['agent'],
                'totalCustomers' => count($customers),
                'totalShipments' => (new ShipmentModel())
                    ->where('agent_id', $agentId)
                    ->countAllResults(),
                'totalInvoices'  => (new AgentInvoiceModel())
                    ->where('agent_id', $agentId)
                    ->countAllResults(),
                'outstanding'    => (float) ($outstanding['balance_amount'] ?? 0),
            ]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('shipments'))->with('error', $e->getMessage());
        }
    }
}
