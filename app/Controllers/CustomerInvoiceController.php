<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;

class CustomerInvoiceController extends BaseController
{
    public function index()
    {
        $customer = (new CustomerModel())
            ->where('user_id', auth()->id())
            ->first();

        $invoices = (new InvoiceModel())
            ->where('customer_id', $customer['id'])
            ->orderBy('id', 'DESC')
            ->findAll();

        return view(
            'customer/my_invoices',
            [
                'invoices' => $invoices
            ]
        );
    }
}