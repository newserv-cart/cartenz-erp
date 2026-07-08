<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ShipmentModel;

class CustomerDashboardController extends BaseController
{
   public function index()
{
    $customerModel = new \App\Models\CustomerModel();
    $shipmentModel = new \App\Models\ShipmentModel();
    $invoiceModel  = new \App\Models\InvoiceModel();
    $paymentModel  = new \App\Models\PaymentModel();


    
    $customer = $customerModel
        ->where('user_id', auth()->id())
        ->first();

       if (!$customer) {

    return redirect()->to('/');

}
       


    $customerId = $customer['id'];

    $data['customer'] = $customer;

    $data['totalShipments'] = $shipmentModel
        ->where('customer_id', $customerId)
        ->countAllResults();

    $data['totalInvoices'] = $invoiceModel
        ->where('customer_id', $customerId)
        ->countAllResults();

    $data['unpaidInvoices'] = $invoiceModel
        ->where('customer_id', $customerId)
        ->where('invoice_status !=', 'PAID')
        ->countAllResults();

    $data['totalPayments'] = $paymentModel
        ->join(
            'invoices',
            'invoices.id = payments.invoice_id'
        )
        ->where(
            'invoices.customer_id',
            $customerId
        )
        ->countAllResults();

    // ✅ Recent Shipments taruh di sini
    $data['recentShipments'] = $shipmentModel
        ->where('customer_id', $customerId)
        ->orderBy('id', 'DESC')
        ->findAll(5);

    return view(
        'customer/dashboard',
        $data
    );
}


    public function myShipments()
    {
        $userId = auth()->id();

        $customer = (new CustomerModel())
            ->where('user_id', $userId)
            ->first();

        $shipments = (new ShipmentModel())
            ->where(
                'customer_id',
                $customer['id']
            )
            ->orderBy('id', 'DESC')
            ->findAll();

        return view(
            'customer/my_shipments',
            [
                'customer'  => $customer,
                'shipments' => $shipments,
            ]
        );
    }

    public function shipmentDetail($shipmentId)
{
    $shipmentModel = new \App\Models\ShipmentModel();
    $trackingModel = new \App\Models\ShipmentTrackingModel();

    $shipment = $shipmentModel->find($shipmentId);

    if (!$shipment)
    {
        return redirect()->back()
            ->with('error', 'Shipment tidak ditemukan');
    }

    $tracking = $trackingModel
        ->where('shipment_id', $shipmentId)
        ->orderBy('event_time', 'ASC')
        ->findAll();

    return view(
        'customer/shipment_detail',
        [
            'shipment' => $shipment,
            'tracking' => $tracking
        ]
    );
}
}