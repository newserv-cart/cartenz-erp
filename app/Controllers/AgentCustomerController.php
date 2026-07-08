<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;

class AgentCustomerController extends BaseController
{
    public function index()
    {
        $agentId = 1;

        $customerModel = new CustomerModel();

        $customers = $customerModel
            ->where('agent_id', $agentId)
            ->findAll();

        $invoiceModel = new InvoiceModel();

        foreach ($customers as &$customer)
        {
            $outstanding = $invoiceModel
                ->selectSum('balance_amount')
                ->where('customer_id', $customer['id'])
                ->first();

            $customer['outstanding']
                = $outstanding['balance_amount']
                ?? 0;
        }

        return view(
            'agent/customers',
            [
                'customers' => $customers
            ]
        );
    }

    public function detail($id)
{
    $customerModel = new \App\Models\CustomerModel();
    $shipmentModel = new \App\Models\ShipmentModel();
    $invoiceModel  = new \App\Models\InvoiceModel();

    $customer = $customerModel->find($id);

    $shipments = $shipmentModel
        ->where('customer_id', $id)
        ->findAll();

    $invoices = $invoiceModel
        ->where('customer_id', $id)
        ->findAll();

    return view(
        'agent/customer_detail',
        [
            'customer'  => $customer,
            'shipments' => $shipments,
            'invoices'  => $invoices
        ]
    );
}
}