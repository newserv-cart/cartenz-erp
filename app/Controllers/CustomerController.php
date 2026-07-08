<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class CustomerController extends BaseController
{
    public function index()
    {
        //
    }


public function pending()
{
    $customerModel = new \App\Models\CustomerModel();

    $customers = $customerModel
        ->where(
            'approval_status',
            'PENDING'
        )
        ->findAll();

    return view(
        'customers/pending',
        [
            'customers' => $customers
        ]
    );
}


public function approve($id)
{
    $customerModel =
        new \App\Models\CustomerModel();

    $customerModel->update(
        $id,
        [
            'approval_status' => 'APPROVED',
            'approved_at' => date(
                'Y-m-d H:i:s'
            ),
        ]
    );

    return redirect()
        ->back()
        ->with(
            'success',
            'Customer Approved'
        );
}

}
