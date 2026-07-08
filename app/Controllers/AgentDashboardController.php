<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ShipmentModel;
use App\Models\InvoiceModel;

class AgentDashboardController extends BaseController
{
    public function index()
    {
        $agentId = 1; // sementara AGT001

        $customerModel = new CustomerModel();
        $shipmentModel = new ShipmentModel();
        $invoiceModel  = new InvoiceModel();

        $customers = $customerModel
            ->where('agent_id', $agentId)
            ->findAll();

        $customerIds = array_column(
            $customers,
            'id'
        );

        $data['totalCustomers']
            = count($customerIds);

        $data['totalShipments']
            = empty($customerIds)
            ? 0
            : $shipmentModel
                ->whereIn(
                    'customer_id',
                    $customerIds
                )
                ->countAllResults();

        $data['totalInvoices']
            = empty($customerIds)
            ? 0
            : $invoiceModel
                ->whereIn(
                    'customer_id',
                    $customerIds
                )
                ->countAllResults();

        $outstanding = empty($customerIds)
            ? ['balance_amount'=>0]
            : $invoiceModel
                ->selectSum('balance_amount')
                ->whereIn(
                    'customer_id',
                    $customerIds
                )
                ->first();

        $data['outstanding']
            = $outstanding['balance_amount']
            ?? 0;

        $data['customers']
            = $customers;

        return view(
            'agent/dashboard',
            $data
        );
    }
}