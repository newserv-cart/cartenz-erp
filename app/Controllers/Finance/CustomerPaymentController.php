<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;

class CustomerPaymentController extends BaseController
{
    public function index()
{
    $payments = (new PaymentModel())
        ->select('payments.*, invoices.invoice_no')
        ->join('invoices', 'invoices.id = payments.invoice_id', 'left')
        ->where('payments.payment_type', 'CUSTOMER')
        ->orderBy('payments.id', 'DESC')
        ->findAll();

    return view('finance/customer_payments/index', [
        'payments' => $payments
    ]);
}

    public function approve($id)
    {
        $paymentModel = new PaymentModel();
        $invoiceModel = new InvoiceModel();

        $payment = $paymentModel->find($id);

         if (!$payment) {
            return redirect()->back();
        }

        if ($payment['status'] === 'APPROVED') {
    return redirect()->back()->with('error', 'Payment sudah di-approve.');
}


    if ($payment['status'] === 'REJECTED') {
    return redirect()->back()
        ->with('error', 'Payment yang sudah REJECTED tidak dapat di-APPROVE.');
}
       

        $paymentModel->update($id, [
            'status' => 'APPROVED'
        ]);

        $invoice = $invoiceModel
            ->find($payment['invoice_id']);

        if ($invoice) {

            $invoiceModel->update(
                $invoice['id'],
                [

                    'invoice_status' => 'PAID',

                    'paid_amount' => $invoice['total_amount'],

                    'balance_amount' => 0

                ]
            );
        }

        return redirect()->back()
            ->with(
                'success',
                'Customer Payment Approved'
            );
    }

    public function reject($id)
{
    $paymentModel = new PaymentModel();

    $payment = $paymentModel->find($id);

    if (!$payment) {
        return redirect()->back();
    }

    if ($payment['status'] === 'APPROVED') {
        return redirect()->back()
            ->with('error', 'Payment yang sudah APPROVED tidak dapat di-REJECT.');
    }

    if ($payment['status'] === 'REJECTED') {
        return redirect()->back()
            ->with('error', 'Payment sudah REJECTED.');
    }

    $paymentModel->update($id, [
        'status' => 'REJECTED'
    ]);

    return redirect()->back()
        ->with('success', 'Customer Payment Rejected');
}
}