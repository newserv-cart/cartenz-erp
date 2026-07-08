<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\InvoiceModel;

class AgingReportController extends BaseController
{
    public function index()
    {
        $invoiceModel = new InvoiceModel();

        $invoices = $invoiceModel
            ->where('invoice_status !=', 'PAID')
            ->findAll();

        foreach ($invoices as &$invoice) {

            $days = floor(
                (time() - strtotime($invoice['due_date']))
                / 86400
            );

            $invoice['aging_days'] = max(0, $days);

            if ($days <= 30) {
                $invoice['bucket'] = '0-30';
            } elseif ($days <= 60) {
                $invoice['bucket'] = '31-60';
            } elseif ($days <= 90) {
                $invoice['bucket'] = '61-90';
            } else {
                $invoice['bucket'] = '90+';
            }
        }

        return view('finance/aging_report', [
            'invoices' => $invoices
        ]);
    }
}