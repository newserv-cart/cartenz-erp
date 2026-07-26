<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerModel;
use App\Services\CustomerShipmentService;

class CustomerShipmentController extends BaseController
{
    public function create()
    {
        try {
            $customer = $this->requireCustomer();
            $this->assertCustomerCanCreate($customer);

            return view(
                'customer/create_shipment',
                (new CustomerShipmentService())
                    ->formData($customer)
            );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with('error', $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $customer = $this->requireCustomer();
            $this->assertCustomerCanCreate($customer);

            $actorId = (int) (auth()->id() ?? 0);

            if ($actorId <= 0) {
                throw new \RuntimeException(
                    'Sesi user tidak ditemukan.'
                );
            }

            $handoverMethod = strtoupper(
                trim(
                    (string) $this->request->getPost(
                        'handover_method'
                    )
                )
            );

            $allowedHandoverMethods = [
                'CUSTOMER_DROP_OFF_BRANCH',
                'CUSTOMER_PICKUP_REQUEST',
            ];

            if (!in_array(
                $handoverMethod,
                $allowedHandoverMethods,
                true
            )) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Handover Method tidak valid.'
                    );
            }

            $input = $this->request->getPost();
            $input['handover_method'] = $handoverMethod;

            if ($handoverMethod === 'CUSTOMER_PICKUP_REQUEST') {
                $requestedPickupDate = trim(
                    (string) $this->request->getPost(
                        'requested_pickup_date'
                    )
                );

                $pickupContactName = trim(
                    (string) $this->request->getPost(
                        'pickup_contact_name'
                    )
                );

                $pickupContactPhone = trim(
                    (string) $this->request->getPost(
                        'pickup_contact_phone'
                    )
                );

                if ($requestedPickupDate === '') {
                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Tanggal pickup wajib diisi.'
                        );
                }

                if (
                    strtotime($requestedPickupDate) <
                    strtotime(date('Y-m-d'))
                ) {
                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Tanggal pickup tidak boleh berada di masa lalu.'
                        );
                }

                if ($pickupContactName === '') {
                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Nama PIC pickup wajib diisi.'
                        );
                }

                if ($pickupContactPhone === '') {
                    return redirect()->back()
                        ->withInput()
                        ->with(
                            'error',
                            'Nomor telepon PIC pickup wajib diisi.'
                        );
                }

                $input['pickup_request'] = [
                    'requested_pickup_date' =>
                        $this->request->getPost(
                            'requested_pickup_date'
                        ),

                    'requested_time_start' =>
                        $this->request->getPost(
                            'requested_time_start'
                        ),

                    'requested_time_end' =>
                        $this->request->getPost(
                            'requested_time_end'
                        ),

                    'pickup_contact_name' =>
                        $this->request->getPost(
                            'pickup_contact_name'
                        ),

                    'pickup_contact_phone' =>
                        $this->request->getPost(
                            'pickup_contact_phone'
                        ),

                    'pickup_remarks' =>
                        $this->request->getPost(
                            'pickup_remarks'
                        ),
                ];
            }

            $result = (new CustomerShipmentService())->create(
                $customer,
                $input,
                $actorId
            );

            if ($handoverMethod === 'CUSTOMER_PICKUP_REQUEST') {
                $successMessage =
                    'Shipment '
                    . $result['awb_no']
                    . ' berhasil dibuat dan pickup request telah dikirim. '
                    . 'Tim CARTENZ akan menghubungi PIC pickup.';
            } else {
                $successMessage =
                    'Shipment '
                    . $result['awb_no']
                    . ' berhasil dibuat. '
                    . 'Silakan drop-off barang ke cabang CARTENZ.';
            }

            return redirect()
                ->to(
                    site_url(
                        'customer/shipment/' .
                        (int) $result['shipment_id']
                    )
                )
                ->with(
                    'success',
                    'Shipment ' . $result['awb_no'] .
                    ' berhasil dibuat. Status masih CREATED sampai barang dipindai oleh petugas CARTENZ.'
                );
        } catch (\Throwable $e) {
            log_message(
                'error',
                'Customer self-service shipment failed: {message}',
                ['message' => $e->getMessage()]
            );

            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
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
                'Halaman ini hanya tersedia untuk akun customer.'
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

    private function assertCustomerCanCreate(array $customer): void
    {
        if ((int) ($customer['status'] ?? 1) !== 1) {
            throw new \RuntimeException(
                'Profil customer tidak aktif.'
            );
        }

        if (
            strtoupper((string) (
                $customer['account_status'] ?? 'ACTIVE'
            )) !== 'ACTIVE'
        ) {
            throw new \RuntimeException(
                'Account tidak aktif atau sedang dikunci. Hubungi admin CARTENZ.'
            );
        }

        $approvalStatus = strtoupper((string) (
            $customer['approval_status'] ?? 'APPROVED'
        ));

        if (
            $approvalStatus !== ''
            && $approvalStatus !== 'APPROVED'
        ) {
            throw new \RuntimeException(
                'Customer belum disetujui untuk membuat shipment.'
            );
        }
    }
}
