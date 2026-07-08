<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use CodeIgniter\Shield\Entities\User;

class CustomerPortalController extends BaseController
{
    // Tampilkan form register
    public function register()
    {
        return view('customer_portal/register');
    }

    // Simpan registrasi customer
   public function store()
{
$customerModel = new CustomerModel();

$existingCustomer = $customerModel
    ->where(
        'email',
        $this->request->getPost('email')
    )
    ->first();

if ($existingCustomer)
{
    return redirect()
        ->back()
        ->with(
            'error',
            'Email already registered'
        );
}

$users = auth()->getProvider();

$user = new User([
    'username' => $this->request->getPost('email'),
    'email'    => $this->request->getPost('email'),
    'password' => $this->request->getPost('password'),
    'active'   => true,
]);

$users->save($user);

$user = $users->findByCredentials([
    'email' => $this->request->getPost('email')
]);

$customerModel->insert([

    'user_id' => $user->id,

    'customer_code' => 'CUS' . time(),

    'customer_name' =>
        $this->request->getPost('customer_name'),

    'company_name' =>
        $this->request->getPost('company_name'),

    'phone' =>
        $this->request->getPost('phone'),

    'email' =>
        $this->request->getPost('email'),

    'customer_category' => 'PERSONAL',

    'customer_type' => 'CASH',

    'status' => 1,

    'approval_status' => 'PENDING',

]);

return redirect()
    ->to('/login')
    ->with(
        'success',
        'Registration Submitted'
    );
    }
}