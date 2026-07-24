<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgentInvoiceModel;
use App\Models\PaymentModel;
use App\Services\AgentSelfBillingService;

class AgentPaymentController extends BaseController
{
    public function create($invoiceId)
    {
        try {
            $invoice = $this->ownedInvoice((int) $invoiceId);
            $this->assertPayable($invoice);

            return view('agent/payment_upload', ['invoice' => $invoice]);
        } catch (\Throwable $e) {
            return redirect()->to(site_url('agent/self-billing'))->with('error', $e->getMessage());
        }
    }

    public function store($invoiceId)
    {
        try {
            $invoice = $this->ownedInvoice((int) $invoiceId);
            $this->assertPayable($invoice);

            // Cek pembayaran pending
            $pending = (new PaymentModel())
                ->where('payment_type', 'AGENT')
                ->where('agent_invoice_id', (int) $invoice['id'])
                ->where('status', 'PENDING')
                ->first();

            if ($pending) {
                throw new \RuntimeException('Masih ada pembayaran yang menunggu verifikasi Finance.');
            }

            // Hitung total & saldo dengan cadangan
            $invoiceTotal = (float) (
                (float) ($invoice['grand_total'] ?? 0) > 0
                    ? $invoice['grand_total']
                    : ($invoice['total_amount'] ?? 0)
            );

            $amount  = round((float) ($this->request->getPost('amount') ?? 0), 2);
            $balance = round(
                (float) ($invoice['balance_amount'] ?? $invoiceTotal),
                2
            );

            if ($amount <= 0) {
                throw new \RuntimeException('Jumlah pembayaran tidak valid.');
            }

            if ($amount > $balance) {
                throw new \RuntimeException('Jumlah pembayaran melebihi sisa invoice.');
            }

            // Validasi file
            $file = $this->request->getFile('proof_file');
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                throw new \RuntimeException('Bukti pembayaran wajib diunggah.');
            }

            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
                throw new \RuntimeException('Bukti pembayaran harus JPG, JPEG, PNG, atau PDF.');
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                throw new \RuntimeException('Ukuran bukti pembayaran maksimal 5 MB.');
            }

            // Simpan file
            $directory = FCPATH . 'uploads/payments';
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $fileName = $file->getRandomName();
            $file->move($directory, $fileName);

            // Simpan data pembayaran
            (new PaymentModel())->insert([
                'payment_no'       => $this->nextPaymentNo(),
                'invoice_id'       => 0,
                'payment_type'     => 'AGENT',
                'agent_invoice_id' => (int) $invoice['id'],
                'payment_date'     => date('Y-m-d'),
                'payment_method'   => trim((string) ($this->request->getPost('payment_method') ?? '')),
                'reference_no'     => trim((string) ($this->request->getPost('reference_no') ?? '')),
                'amount'           => $amount,
                'remarks'          => trim((string) ($this->request->getPost('remarks') ?? '')),
                'proof_file'       => $fileName,
                'status'           => 'PENDING',
            ]);

            return redirect()
                ->to(site_url('agent/self-billing/invoice/' . (int) $invoice['id']))
                ->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi Finance.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    private function ownedInvoice(int $invoiceId): array
    {
        $context = (new AgentSelfBillingService())->requireAgentContext(
            (int) (auth()->id() ?? 0)
        );

        $invoice = (new AgentInvoiceModel())
            ->where('id', $invoiceId)
            ->where('agent_id', (int) $context['agent_id'])
            ->first();

        if (!$invoice) {
            throw new \RuntimeException('Agent invoice tidak ditemukan.');
        }

        return $invoice;
    }

    private function assertPayable(array $invoice): void
    {
        $approval = strtoupper((string) ($invoice['approval_status'] ?? ''));
        $status   = strtoupper((string) ($invoice['invoice_status'] ?? ''));

        if ($approval !== 'APPROVED') {
            throw new \RuntimeException('Invoice belum disetujui Finance.');
        }

        $invoiceTotal = (float) (
            (float) ($invoice['grand_total'] ?? 0) > 0
                ? $invoice['grand_total']
                : ($invoice['total_amount'] ?? 0)
        );
        $balance = (float) ($invoice['balance_amount'] ?? $invoiceTotal);

        if (!in_array($status, ['UNPAID', 'PARTIAL'], true) || $balance <= 0) {
            throw new \RuntimeException('Invoice sudah lunas atau tidak dapat dibayar saat ini.');
        }
    }

    private function nextPaymentNo(): string
    {
        $model = new PaymentModel();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $number = 'PAY' . date('ymdHis') . random_int(10, 99);
            if (!$model->where('payment_no', $number)->first()) {
                return $number;
            }
        }

        throw new \RuntimeException('Gagal membuat nomor pembayaran unik.');
    }
}