<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;

class AgentInvoiceController extends BaseController
{
    public function index()
    {
        $invoiceModel = new InvoiceModel();

        $invoices = $invoiceModel
            ->orderBy('id','DESC')
            ->findAll();

        return view(
            'agent/invoices',
            [
                'invoices' => $invoices
            ]
        );
    }

    public function detail($id)
    {
        $invoice = (new InvoiceModel())
            ->find($id);

        return view(
            'agent/invoice_detail',
            [
                'invoice' => $invoice
            ]
        );
    }
}