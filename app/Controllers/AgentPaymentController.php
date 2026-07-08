<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\InvoiceModel;

class AgentPaymentController extends BaseController
{
    public function create($invoiceId)
    {
        

        $invoice = (new InvoiceModel())
    ->find($invoiceId);


        if (!$invoice)
        {
            return redirect()->back();
        }

        return view(
            'agent/payment_upload',
            [
                'invoice' => $invoice
            ]
        );
    }

    public function store($invoiceId)
    {
        $invoice = (new InvoiceModel())
    ->find($invoiceId);

        if (!$invoice)
        {
            return redirect()->back();
        }

        $file = $this->request
            ->getFile('proof_file');

        $fileName = null;

        if ($file && $file->isValid())
        {
            $fileName =
                $file->getRandomName();

            $file->move(
                FCPATH.'uploads/payments',
                $fileName
            );
        }

        (new PaymentModel())->insert([

            'payment_no' =>
                'PAY'.date('ymdHis'),

            'payment_type' =>
                'AGENT',

            'agent_invoice_id' =>
                $invoiceId,

            'payment_date' =>
                date('Y-m-d'),

            'payment_method' =>
                $this->request->getPost('payment_method'),

            'reference_no' =>
                $this->request->getPost('reference_no'),

            'amount' =>
                $this->request->getPost('amount'),

            'remarks' =>
                $this->request->getPost('remarks'),

            'proof_file' =>
                $fileName,

            'status' =>
                'PENDING'

        ]);

        return redirect()
            ->to(
                site_url(
                    'agent/invoices-cartenz'
                )
            )
            ->with(
                'success',
                'Payment Uploaded'
            );
    }

    public function test()
{
    dd('AGENT PAYMENT HIDUP');
}
}