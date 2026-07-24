<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;

class CustomerInvoiceController extends BaseController
{
    public function index()
    {
        $userId = (int) (auth()->id() ?? 0);

        // Cek apakah user sudah login
        if ($userId <= 0) {
            return redirect()->to(site_url('login'))->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cari data customer yang aktif
        $customer = (new CustomerModel())
            ->where('user_id', $userId)
            ->where('status', 1)
            ->first();

        // Cek apakah data customer ditemukan
        if (empty($customer)) {
            return redirect()->to(site_url('/'))->with('error', 'Akun ini belum terhubung dengan data customer.');
        }

        // Ambil daftar invoice customer
        $invoices = (new InvoiceModel())
            ->where('customer_id', (int) $customer['id'])
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('customer/my_invoices', [
            'customer'  => $customer,
            'invoices'  => $invoices,
        ]);
    }
}