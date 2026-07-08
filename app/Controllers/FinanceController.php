<?php

namespace App\Controllers;

class FinanceController extends BaseController
{
    public function lockOverdueCustomers()
    {
        $db = \Config\Database::connect();

        $overdueInvoices = $db->table('invoices')
    ->where('invoice_status !=', 'PAID')
    ->where('due_date <', date('Y-m-d'))
    ->get()
    ->getResultArray();

        foreach ($overdueInvoices as $invoice)
        {
            $db->table('customers')
                ->where(
                    'id',
                    $invoice['customer_id']
                )
                ->update([
                    'account_status' => 'LOCKED'
                ]);
        }

        echo count($overdueInvoices)
            .' customer locked';
    }

    
}