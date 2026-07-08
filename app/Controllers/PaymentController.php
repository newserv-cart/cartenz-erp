<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PaymentController extends BaseController
{
    public function receive($invoiceId)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $paymentModel = new \App\Models\PaymentModel();

        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice) {
            return redirect()->back();
        }

        $amount = $this->request->getPost('amount');

        $paymentModel->insert([
            'payment_no' => 'PAY' . date('ymdHis'),
            'invoice_id' => $invoiceId,
            'payment_date' => date('Y-m-d'),
            'payment_method' => $this->request->getPost('payment_method'),
            'reference_no' => $this->request->getPost('reference_no'),
            'amount' => $amount,
            'remarks' => $this->request->getPost('remarks')
        ]);


        $amount = (float) $this->request->getPost('amount');

if ($amount <= 0) {
    return redirect()->back()
        ->with('error', 'Nominal pembayaran tidak valid');
}

        $paidAmount =
            $invoice['paid_amount'] + $amount;

        $balance =
            $invoice['total_amount'] - $paidAmount;

        $status = 'PARTIAL';

        if ($balance <= 0) {
            $status = 'PAID';
            $balance = 0;
        }

        $invoiceModel->update(
            $invoiceId,
            [
                'paid_amount' => $paidAmount,
                'balance_amount' => $balance,
                'invoice_status' => $status
            ]
        );

        return redirect()->to('/invoices');
    }


    
}