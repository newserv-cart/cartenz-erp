<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use App\Models\ShipmentModel;

class CustomerShipmentController extends BaseController
{
    public function create()
    {
        $customerModel = new CustomerModel();

        // Get customer linked to logged-in user
        $customer = $customerModel
            ->where('user_id', auth()->id())
            ->first();

        // If no customer record found
        if (!$customer) {
            return redirect()
                ->to('/customer/dashboard')
                ->with('error', 'Customer profile not found.');
        }

        // Block if account is locked
        if ($customer['account_status'] === 'LOCKED') {
            return redirect()
                ->to('/customer/dashboard')
                ->with('error', 'Account Locked. Please settle overdue invoices.');
        }

        // Get saved sender addresses
        $senderModel = new CustomerAddressModel();
        $data['senderAddresses'] = $senderModel
            ->where('customer_id', $customer['id'])
            ->where('address_type', 'SENDER')
            ->where('status', 1)
            ->orderBy('contact_name', 'ASC')
            ->findAll();

        $data['customer'] = $customer;

        return view('customer/create_shipment', $data);
    }

    public function store()
    {
        $userId = auth()->id();
        $customerModel = new CustomerModel();
        $shipmentModel = new ShipmentModel();

        // Get customer data
        $customer = $customerModel
            ->where('user_id', $userId)
            ->first();

        if (!$customer) {
            return redirect()
                ->back()
                ->with('error', 'Customer not found.');
        }

        // Basic validation
        $rules = [
            'actual_weight' => 'required|numeric|greater_than[0]',
            'remarks'       => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Generate AWB number
        $awbNo = 'CTZ' . date('ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        // Save shipment
        $shipmentModel->insert([
            'awb_no'           => $awbNo,
            'customer_id'      => $customer['id'],
            'actual_weight'    => $this->request->getPost('actual_weight'),
            'chargeable_weight'=> $this->request->getPost('chargeable_weight') ?? 0,
            'shipment_status'  => 'DRAFT',
            'remarks'          => $this->request->getPost('remarks'),
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        return redirect()
            ->to('/customer/my-shipments')
            ->with('success', 'Shipment created successfully.');
    }
}