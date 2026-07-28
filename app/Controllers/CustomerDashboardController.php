<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Models\ShipmentModel;

class CustomerDashboardController extends BaseController
{
    public function index()
    {
        try {
            $customer = $this->requireCustomer();
            $customerId = (int) $customer['id'];
            $db = db_connect();

            $data = [
                'customer' => $customer,
                'totalShipments' => (new ShipmentModel())
                    ->where('customer_id', $customerId)
                    ->countAllResults(),
                'totalInvoices' => $db->tableExists('invoices')
                    ? $db->table('invoices')
                        ->where('customer_id', $customerId)
                        ->countAllResults()
                    : 0,
                'unpaidInvoices' => $db->tableExists('invoices')
                    ? $db->table('invoices')
                        ->where('customer_id', $customerId)
                        ->where('invoice_status !=', 'PAID')
                        ->countAllResults()
                    : 0,
                'totalPayments' => 0,
                'recentShipments' => (new ShipmentModel())
                    ->where('customer_id', $customerId)
                    ->orderBy('id', 'DESC')
                    ->findAll(5),
            ];

            if (
                $db->tableExists('payments')
                && $db->tableExists('invoices')
            ) {
                $data['totalPayments'] = $db
                    ->table('payments p')
                    ->join(
                        'invoices i',
                        'i.id = p.invoice_id',
                        'inner'
                    )
                    ->where('i.customer_id', $customerId)
                    ->countAllResults();
            }

            return view('customer/dashboard', $data);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('logout'))
                ->with('error', $e->getMessage());
        }
    }

    public function myShipments()
    {
        try {
            $customer = $this->requireCustomer();

            $db = db_connect();

            $shipments = $db->table('shipments s')
                ->select([
                    's.id',
                    's.awb_no',
                    's.shipment_date',
                    's.shipment_status',
                    's.public_tracking_status',
                    's.planned_handover_method',
                    's.chargeable_weight',
                    'pod.id AS pod_id',
                ])
                ->join(
                    'shipment_proof_of_delivery pod',
                    'pod.shipment_id = s.id',
                    'left'
                )
                ->where('s.customer_id', (int) $customer['id'])
                ->orderBy('s.id', 'DESC')
                ->get()
                ->getResultArray();

            return view('customer/my_shipments', [
                'customer' => $customer,
                'shipments' => $shipments,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with('error', $e->getMessage());
        }
    }

    public function shipmentDetail(int $shipmentId)
    {
        try {
            $customer = $this->requireCustomer();
            $shipment = (new ShipmentModel())
                ->where('id', $shipmentId)
                ->where('customer_id', (int) $customer['id'])
                ->first();

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan atau bukan milik customer.'
                );
            }

            $shipment = $this->externalizeShipment($shipment);

            $db = db_connect();
            $tracking = $db->tableExists('shipment_tracking')
                ? $db->table('shipment_tracking')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('event_time', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];

            $tracking = (new \App\Services\ExternalTrackingPresenter())
                ->presentMany($tracking);
            $packages = $db->tableExists('shipment_packages')
                ? $db->table('shipment_packages')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('package_no', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];
            $items = $db->tableExists('shipment_items')
                ? $db->table('shipment_items')
                    ->where('shipment_id', $shipmentId)
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray()
                : [];

            $podQuery = $db
                ->table('shipment_proof_of_delivery pod')
                ->select([
                    'pod.id',
                    'pod.delivered_at',
                    'pod.delivered_to',
                    'pod.delivery_relation',
                    'pod.delivery_notes',
                    'pod.pod_photo_path',
                    'pod.pod_signature_path',
                    'pod.created_at',
                ])
                ->where('pod.shipment_id', $shipmentId)
                ->get();

            if ($podQuery === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Query Proof of Delivery gagal: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            $pod = $podQuery->getRowArray();

            return view('customer/shipment_detail', [
                'customer' => $customer,
                'shipment' => $shipment,
                'tracking' => $tracking,
                'packages' => $packages,
                'items' => $items,
                'pod' => $pod,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/my-shipments'))
                ->with('error', $e->getMessage());
        }
    }

    private function externalizeShipment(array $shipment): array
    {
        return [
            'id'                => $shipment['id'] ?? 0,
            'awb_no'            => $shipment['awb_no'] ?? '',
            'shipment_status'   => $shipment['shipment_status'] ?? '',
            'shipment_date'     => $shipment['shipment_date'] ?? null,
            'service_name'      => $shipment['service_name'] ?? '',
            'country_name'      => $shipment['country_name'] ?? '',
            'total_colly'       => $shipment['total_colly'] ?? 0,
            'actual_weight'     => $shipment['actual_weight'] ?? 0,
            'volume_weight'     => $shipment['volume_weight'] ?? 0,
            'chargeable_weight' => $shipment['chargeable_weight'] ?? 0,
            'sender_name'       => $shipment['sender_name'] ?? '',
            'sender_company'    => $shipment['sender_company'] ?? '',
            'sender_phone'      => $shipment['sender_phone'] ?? '',
            'sender_address'    => $shipment['sender_address'] ?? '',
            'sender_city'       => $shipment['sender_city'] ?? '',
            'sender_state'      => $shipment['sender_state'] ?? '',
            'sender_postal_code'=> $shipment['sender_postal_code'] ?? '',
            'receiver_name'     => $shipment['receiver_name'] ?? '',
            'receiver_company'  => $shipment['receiver_company'] ?? '',
            'receiver_phone'    => $shipment['receiver_phone'] ?? '',
            'receiver_address'  => $shipment['receiver_address'] ?? '',
            'receiver_city'     => $shipment['receiver_city'] ?? '',
            'receiver_state'    => $shipment['receiver_state'] ?? '',
            'receiver_postal_code' => $shipment['receiver_postal_code'] ?? '',
            'planned_handover_method' => $shipment['planned_handover_method'] ?? '',
            'total_charge'      => $shipment['total_charge'] ?? 0,
            'declared_value'    => $shipment['declared_value'] ?? 0,
            'customer_reference_no' => $shipment['customer_reference_no'] ?? '',
            'public_tracking_status' => $shipment['public_tracking_status'] ?? '',
        ];
    }

    private function requireCustomer(): array
    {
        $user = auth()->user();
        $userId = (int) (auth()->id() ?? 0);

        if (
            $user === null
            || $userId <= 0
            || !$user->inGroup('user')
        ) {
            throw new \RuntimeException(
                'Akses customer tidak valid.'
            );
        }

        $customer = (new CustomerModel())
            ->where('user_id', $userId)
            ->first();

        if (!$customer) {
            throw new \RuntimeException(
                'Akun belum terhubung ke profil customer.'
            );
        }

        return $customer;
    }
}
