<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\AgentInvoiceModel;

class AgentPaymentController extends BaseController
{
    public function index()
    {
        $payments = (new PaymentModel())
            ->where('payment_type', 'AGENT')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view(
            'finance/agent_payments/index',
            [
                'payments' => $payments
            ]
        );
    }

    public function approve($id)
    {
        $paymentModel = new PaymentModel();
        $invoiceModel = new AgentInvoiceModel();

        $payment = $paymentModel->find($id);

        if (!$payment) {
            return redirect()->back();
        }

        // Approve Payment
        $paymentModel->update($id, [
            'status' => 'APPROVED'
        ]);

        // Update Agent Invoice
        $invoice = $invoiceModel->find(
            $payment['agent_invoice_id']
        );

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
                'Agent Payment Approved'
            );
    }

    public function reject($id)
    {
        (new PaymentModel())->update(
            $id,
            [
                'status' => 'REJECTED'
            ]
        );

        return redirect()->back()
            ->with(
                'success',
                'Agent Payment Rejected'
            );
    }
}