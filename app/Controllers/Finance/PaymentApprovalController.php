<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\InvoiceModel;

class PaymentApprovalController extends BaseController
{
    public function index()
    {
        $paymentModel = new PaymentModel();

        $payments = $paymentModel
            ->where('status', 'PENDING')
            ->findAll();

        return view('finance/payments/index', [
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

    // Update status payment
    $paymentModel->update($id, [
        'status' => 'APPROVED'
    ]);

    // CUSTOMER
    if (
        $payment['payment_type'] == 'CUSTOMER'
        && !empty($payment['invoice_id'])
    ) {

        $invoice = $invoiceModel->find($payment['invoice_id']);

        if ($invoice) {
            $invoiceModel->update($invoice['id'], [
                'invoice_status' => 'PAID',
                'paid_amount'    => $invoice['total_amount'],
                'balance_amount' => 0
            ]);
        }
    }

    // AGENT (sementara masih pakai tabel invoices)
    if (
        $payment['payment_type'] == 'AGENT'
        && !empty($payment['invoice_id'])
    ) {

        $invoice = $invoiceModel->find($payment['invoice_id']);

        if ($invoice) {
            $invoiceModel->update($invoice['id'], [
                'invoice_status' => 'PAID',
                'paid_amount'    => $invoice['total_amount'],
                'balance_amount' => 0
            ]);
        }
    }

    return redirect()->back()
        ->with('success', 'Payment Approved');
}

    public function reject($id)
    {
        $paymentModel = new PaymentModel();

        $paymentModel->update($id, [
            'status' => 'REJECTED'
        ]);

        return redirect()->back()
            ->with('success', 'Payment Rejected');
    }
}