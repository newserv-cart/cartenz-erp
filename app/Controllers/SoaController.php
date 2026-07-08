<?php

namespace App\Controllers;

class SoaController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $rows = $db->table('invoices')
            ->select('
                invoices.*,
                customers.customer_name
            ')
            ->join(
                'customers',
                'customers.id = invoices.customer_id',
                'left'
            )
            ->where(
                'balance_amount >',
                0
            )
            ->orderBy(
                'customer_name',
                'ASC'
            )
            ->get()
            ->getResultArray();

        return view(
            'soa/index',
            [
                'rows' => $rows
            ]
        );
    }
}