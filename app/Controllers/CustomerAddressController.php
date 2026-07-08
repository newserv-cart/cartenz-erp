<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use App\Models\CountryModel;

class CustomerAddressController extends BaseController
{
    public function saveAddress()
    {
        if (!$this->request->isAJAX()) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid request'
            ]);

        }

        // Development Mode
        $customer = (new CustomerModel())->find(1);

        if (!$customer) {

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Customer not found'
            ]);

        }

        $model = new CustomerAddressModel();

        $data = [

            'customer_id'      => $customer['id'],

            'address_type'     => strtoupper(
                $this->request->getPost('address_type')
            ),

            'contact_name'     => $this->request->getPost('contact_name'),

            'company_name'     => $this->request->getPost('company_name'),

            'phone'            => $this->request->getPost('phone'),

            'email'            => $this->request->getPost('email'),

            'address'          => $this->request->getPost('address'),

            'postal_code'      => $this->request->getPost('postal_code'),

            'city'             => $this->request->getPost('city'),

            'state'            => $this->request->getPost('province'),

            'country_id'       => $this->request->getPost('country_id'),

            'identity_number'  => $this->request->getPost('identity_number'),

            'status'           => 1

        ];

        $id = $model->insert($data, true);

        $address = $model->find($id);


        $country = (new CountryModel())->find($address['country_id']);

        $address['country_name'] = $country['country_name'] ?? '';

        return $this->response->setJSON([

            'status'  => 'success',

            'address' => $address

        ]);

    }
}