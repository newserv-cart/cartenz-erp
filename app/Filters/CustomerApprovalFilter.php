<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use App\Models\CustomerModel;

class CustomerApprovalFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    )
    {
        if (!auth()->loggedIn())
        {
            return redirect()->to('/login');
        }

        $userId = auth()->id();

        $customer = (new CustomerModel())
            ->where('user_id', $userId)
            ->first();

        if (!$customer)
        {
            return;
        }

        if ($customer['approval_status'] != 'APPROVED')
        {
            return redirect()
                ->to('/waiting-approval');
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
    }
}