<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;

class PaymentController extends BaseController
{
    public function index()
    {
        return 'Payment Controller OK';
    }

   public function create($invoiceId)
{
    $invoiceModel = new InvoiceModel();

    $invoice = $invoiceModel->find($invoiceId);

    if (!$invoice) {
        return redirect()->back()
            ->with('error', 'Invoice tidak ditemukan');
    }

    return view('customer/payment_form', [
        'invoice' => $invoice
    ]);
}

public function store()
{
    $file = $this->request->getFile('proof_file');

    $fileName = null;

    if ($file && $file->isValid()) {

        $uploadPath = FCPATH . 'uploads/payments';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $fileName = $file->getRandomName();

        $file->move($uploadPath, $fileName);
    }

    $paymentModel = new PaymentModel();

    $paymentModel->insert([

        'payment_no' => 'PAY' . date('ymdHis'),

        'invoice_id' => $this->request->getPost('invoice_id'),

        'payment_date' => $this->request->getPost('payment_date'),

        'payment_method' => $this->request->getPost('payment_method'),

        'reference_no' => $this->request->getPost('reference_no'),

        'amount' => $this->request->getPost('amount'),

        'remarks' => $this->request->getPost('remarks'),

        'proof_file' => $fileName,

        'status' => 'PENDING'
    ]);

    return redirect()
        ->to('/customer/my-invoices')
        ->with('success', 'Bukti pembayaran berhasil diupload');
}

public function myPayments()
{
    $paymentModel = new \App\Models\PaymentModel();

    $payments = $paymentModel
        ->orderBy('id', 'DESC')
        ->findAll();

    return view('customer/my_payments', [
        'payments' => $payments
    ]);
}

}