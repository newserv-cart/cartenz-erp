<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $invoiceModel = new InvoiceModel();
        $paymentModel = new PaymentModel();

        $data = [

            'totalInvoice' => $invoiceModel
                ->selectSum('grand_total')
                ->first()['grand_total'] ?? 0,

            'totalPaid' => $invoiceModel
                ->where('invoice_status','PAID')
                ->selectSum('paid_amount')
                ->first()['paid_amount'] ?? 0,

            'totalUnpaid' => $invoiceModel
                ->where('invoice_status','UNPAID')
                ->selectSum('balance_amount')
                ->first()['balance_amount'] ?? 0,

            'pendingPayments' => $paymentModel
                ->where('status','PENDING')
                ->countAllResults()

        ];

        return view('finance/dashboard', $data);
    }
}