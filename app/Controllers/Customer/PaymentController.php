<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;

class PaymentController extends BaseController
{
    public function index()
    {
        return redirect()->to(
            site_url('customer/my-payments')
        );
    }

    public function create($invoiceId)
    {
        $customer = $this->currentCustomer();

        if (!$customer) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with(
                    'error',
                    'Akun belum terhubung dengan data customer.'
                );
        }

        $invoice = (new InvoiceModel())
            ->where('id', (int) $invoiceId)
            ->where('customer_id', (int) $customer['id'])
            ->first();

        if (!$invoice) {
            return redirect()
                ->to(site_url('customer/my-invoices'))
                ->with(
                    'error',
                    'Invoice tidak ditemukan atau bukan milik Anda.'
                );
        }

        $financial = $this->invoiceFinancialState($invoice);

        if (
            $financial['status'] === 'PAID'
            || $financial['balance'] <= 0
        ) {
            return redirect()
                ->to(site_url('customer/my-invoices'))
                ->with(
                    'error',
                    'Invoice sudah lunas. Pembayaran baru tidak diperlukan.'
                );
        }

        $pendingPayment = (new PaymentModel())
            ->where('payment_type', 'CUSTOMER')
            ->where('invoice_id', (int) $invoice['id'])
            ->where('status', 'PENDING')
            ->first();

        if ($pendingPayment) {
            return redirect()
                ->to(site_url('customer/my-invoices'))
                ->with(
                    'error',
                    'Masih ada pembayaran yang menunggu verifikasi Finance.'
                );
        }

        return view('customer/payment_form', [
            'invoice' => $invoice,
        ]);
    }

    public function store()
    {
        $customer = $this->currentCustomer();

        if (!$customer) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with(
                    'error',
                    'Akun belum terhubung dengan data customer.'
                );
        }

        $invoiceId = (int) (
            $this->request->getPost('invoice_id') ?? 0
        );

        if ($invoiceId <= 0) {
            return redirect()
                ->to(site_url('customer/my-invoices'))
                ->with(
                    'error',
                    'Invoice tidak valid.'
                );
        }

        $paymentDate = trim(
            (string) $this->request->getPost('payment_date')
        );

        $paymentMethod = trim(
            (string) $this->request->getPost('payment_method')
        );

        $referenceNo = trim(
            (string) $this->request->getPost('reference_no')
        );

        $remarks = trim(
            (string) $this->request->getPost('remarks')
        );

        $paymentAmount = round(
            (float) $this->request->getPost('amount'),
            2
        );

        if (
            $paymentDate === ''
            || !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $paymentDate
            )
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal transfer tidak valid.'
                );
        }

        if ($paymentMethod === '') {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Metode pembayaran wajib diisi.'
                );
        }

        if ($referenceNo === '') {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Nomor referensi transfer wajib diisi.'
                );
        }

        if ($paymentAmount <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Jumlah pembayaran tidak valid.'
                );
        }

        $file = $this->request->getFile('proof_file');

        if (
            !$file
            || !$file->isValid()
            || $file->hasMoved()
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Bukti transfer wajib diunggah.'
                );
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
        ];

        $mimeType = strtolower(
            (string) $file->getMimeType()
        );

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Bukti transfer harus berupa JPG, PNG, atau PDF.'
                );
        }

        $maximumFileSize = 5 * 1024 * 1024;

        if ((int) $file->getSize() > $maximumFileSize) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Ukuran bukti transfer maksimal 5 MB.'
                );
        }

        $db = db_connect();

        $uploadPath = FCPATH . 'uploads/payments';
        $fileName   = null;
        $savedPath  = null;

        $db->transBegin();

        try {
            /*
             * Lock invoice agar dua submit bersamaan tidak dapat
             * membuat dua payment PENDING.
             */
            $invoice = $db->query(
                '
                    SELECT *
                    FROM invoices
                    WHERE id = ?
                      AND customer_id = ?
                    FOR UPDATE
                ',
                [
                    $invoiceId,
                    (int) $customer['id'],
                ]
            )->getRowArray();

            if (!$invoice) {
                throw new \RuntimeException(
                    'Invoice tidak ditemukan atau bukan milik Anda.'
                );
            }

            $financial = $this->invoiceFinancialState(
                $invoice
            );

            if (
                $financial['status'] === 'PAID'
                || $financial['balance'] <= 0
            ) {
                throw new \RuntimeException(
                    'Invoice sudah lunas. Payment tidak dapat dibuat.'
                );
            }

            if ($paymentAmount > $financial['balance']) {
                throw new \RuntimeException(
                    'Jumlah pembayaran melebihi sisa invoice.'
                );
            }

            $pendingPayment = $db->query(
                '
                    SELECT id
                    FROM payments
                    WHERE invoice_id = ?
                      AND payment_type = ?
                      AND status = ?
                    LIMIT 1
                    FOR UPDATE
                ',
                [
                    $invoiceId,
                    'CUSTOMER',
                    'PENDING',
                ]
            )->getRowArray();

            if ($pendingPayment) {
                throw new \RuntimeException(
                    'Masih ada pembayaran yang menunggu verifikasi Finance.'
                );
            }

            if (!is_dir($uploadPath)) {
                if (
                    !mkdir(
                        $uploadPath,
                        0775,
                        true
                    )
                    && !is_dir($uploadPath)
                ) {
                    throw new \RuntimeException(
                        'Folder upload pembayaran gagal dibuat.'
                    );
                }
            }

            $fileName = $file->getRandomName();

            $file->move(
                $uploadPath,
                $fileName
            );

            $savedPath = $uploadPath
                . DIRECTORY_SEPARATOR
                . $fileName;

            $paymentNo = 'PAY'
                . date('ymdHis')
                . random_int(10, 99);

            $paymentModel = new PaymentModel();

            $paymentId = $paymentModel->insert(
                [
                    'payment_no'      => $paymentNo,
                    'invoice_id'      => $invoiceId,
                    'payment_type'    => 'CUSTOMER',
                    'agent_invoice_id'=> null,
                    'payment_date'    => $paymentDate,
                    'payment_method'  => $paymentMethod,
                    'reference_no'    => $referenceNo,
                    'amount'          => $paymentAmount,
                    'remarks'         => $remarks,
                    'proof_file'      => $fileName,
                    'status'          => 'PENDING',
                ],
                true
            );

            if ($paymentId === false) {
                throw new \RuntimeException(
                    'Database menolak penyimpanan pembayaran: '
                    . implode(
                        ', ',
                        $paymentModel->errors()
                    )
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi database pembayaran gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('customer/my-invoices'))
                ->with(
                    'success',
                    'Bukti pembayaran berhasil diupload dan menunggu verifikasi Finance.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            if (
                $savedPath !== null
                && is_file($savedPath)
            ) {
                @unlink($savedPath);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function myPayments()
    {
        $customer = $this->currentCustomer();

        if (!$customer) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with(
                    'error',
                    'Akun belum terhubung dengan data customer.'
                );
        }

        /*
         * Hanya tampilkan pembayaran milik customer login.
         * Jangan memakai findAll() tanpa filter.
         */
        $payments = (new PaymentModel())
            ->select([
                'payments.*',
                'invoices.invoice_no',
            ])
            ->join(
                'invoices',
                'invoices.id = payments.invoice_id',
                'inner'
            )
            ->where(
                'invoices.customer_id',
                (int) $customer['id']
            )
            ->where(
                'payments.payment_type',
                'CUSTOMER'
            )
            ->orderBy(
                'payments.id',
                'DESC'
            )
            ->findAll();

        return view('customer/my_payments', [
            'customer' => $customer,
            'payments' => $payments,
        ]);
    }

    private function currentCustomer(): ?array
    {
        $userId = (int) (auth()->id() ?? 0);

        if ($userId <= 0) {
            return null;
        }

        return (new CustomerModel())
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();
    }

    private function invoiceFinancialState(
        array $invoice
    ): array {
        $grandTotal = round(
            (float) ($invoice['grand_total'] ?? 0),
            2
        );

        $totalAmount = round(
            (float) ($invoice['total_amount'] ?? 0),
            2
        );

        $total = $grandTotal > 0
            ? $grandTotal
            : $totalAmount;

        $paid = round(
            (float) ($invoice['paid_amount'] ?? 0),
            2
        );

        $balance = array_key_exists(
            'balance_amount',
            $invoice
        )
            ? round(
                (float) $invoice['balance_amount'],
                2
            )
            : round(
                max(0, $total - $paid),
                2
            );

        return [
            'total'   => $total,
            'paid'    => $paid,
            'balance' => $balance,
            'status'  => strtoupper(
                trim(
                    (string) (
                        $invoice['invoice_status']
                        ?? 'UNPAID'
                    )
                )
            ),
        ];
    }
}