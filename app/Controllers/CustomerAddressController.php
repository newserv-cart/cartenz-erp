<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerAddressModel;
use App\Models\CustomerModel;
use App\Services\ShipmentOwnerContextService;

class CustomerAddressController extends BaseController
{
    public function index()
    {
        try {
            $customer = $this->requireCustomer();
            $db = db_connect();

            $builder = $db
                ->table('customer_addresses ca')
                ->select('ca.*');

            if ($db->tableExists('countries')) {
                $builder
                    ->select('c.country_name')
                    ->join(
                        'countries c',
                        'c.id = ca.country_id',
                        'left'
                    );
            }

            $addresses = $builder
                ->where('ca.customer_id', (int) $customer['id'])
                ->where('ca.status', 1)
                ->orderBy('ca.address_type', 'ASC')
                ->orderBy('ca.is_default', 'DESC')
                ->orderBy('ca.contact_name', 'ASC')
                ->get()
                ->getResultArray();

            return view('customer/addresses/index', [
                'customer' => $customer,
                'addresses' => $addresses,
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            return view('customer/addresses/create', [
                'customer' => $this->requireCustomer(),
                'countries' => $this->countryOptions(),
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/dashboard'))
                ->with('error', $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $customerId = $this->resolveCustomerId();
            $data = $this->validatedAddressData(
                $customerId
            );

            $model = new CustomerAddressModel();
            $id = $model->insert($data, true);

            if (!$id) {
                throw new \RuntimeException(
                    'Alamat gagal disimpan.'
                );
            }

            if ((int) ($data['is_default'] ?? 0) === 1) {
                $this->makeDefault(
                    (int) $customer['id'],
                    (int) $id,
                    (string) $data['address_type']
                );
            }

            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('success', 'Alamat berhasil disimpan.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        try {
            $customer = $this->requireCustomer();
            $address = $this->ownedAddress(
                (int) $customer['id'],
                $id
            );

            return view('customer/addresses/edit', [
                'customer' => $customer,
                'address' => $address,
                'countries' => $this->countryOptions(),
            ]);
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('error', $e->getMessage());
        }
    }

    public function update(int $id)
    {
        try {
            $customer = $this->requireCustomer();
            $this->ownedAddress((int) $customer['id'], $id);
            $data = $this->validatedAddressData(
                (int) $customer['id']
            );

            if (!(new CustomerAddressModel())->update($id, $data)) {
                throw new \RuntimeException(
                    'Alamat gagal diperbarui.'
                );
            }

            if ((int) ($data['is_default'] ?? 0) === 1) {
                $this->makeDefault(
                    (int) $customer['id'],
                    $id,
                    (string) $data['address_type']
                );
            }

            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('success', 'Alamat berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        try {
            $customer = $this->requireCustomer();
            $address = $this->ownedAddress(
                (int) $customer['id'],
                $id
            );

            if (!(new CustomerAddressModel())->update($id, [
                'status' => 0,
                'is_default' => 0,
            ])) {
                throw new \RuntimeException(
                    'Alamat gagal dinonaktifkan.'
                );
            }

            return redirect()
                ->to(site_url('customer/addresses'))
                ->with(
                    'success',
                    'Alamat ' . ($address['address_label'] ?? '') .
                    ' berhasil dinonaktifkan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('error', $e->getMessage());
        }
    }

    public function setDefault(int $id)
    {
        try {
            $customer = $this->requireCustomer();
            $address = $this->ownedAddress(
                (int) $customer['id'],
                $id
            );

            $this->makeDefault(
                (int) $customer['id'],
                $id,
                (string) $address['address_type']
            );

            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('success', 'Alamat default berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('customer/addresses'))
                ->with('error', $e->getMessage());
        }
    }

    public function saveAddress()
    {
        if (!$this->request->isAJAX()) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid request.',
                ]);
        }

        $addressType = strtoupper(
            trim(
                (string) $this->request
                    ->getPost('address_type')
            )
        );

        $personLabel = $addressType === 'SENDER'
            ? 'Sender'
            : 'Receiver';

        $rules = [
            'address_type' => [
                'label' => 'Address Type',
                'rules' => 'required|in_list[SENDER,RECEIVER]',
            ],

            'contact_name' => [
                'label' => $personLabel . ' Name',
                'rules' => 'required|min_length[2]|max_length[200]',
            ],

            'phone' => [
                'label' => $personLabel . ' Phone',
                'rules' => 'required|max_length[50]',
            ],

            'country_id' => [
                'label' => $personLabel . ' Country',
                'rules' => 'required|is_natural_no_zero',
            ],

            'address' => [
                'label' => 'Complete Address',
                'rules' => 'required|min_length[3]|max_length[5000]',
            ],

            'email' => [
                'label' => 'Email',
                'rules' => 'permit_empty|valid_email|max_length[150]',
            ],
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();

            log_message(
                'error',
                'SAVE {type} VALIDATION FAILED: {errors}',
                [
                    'type' => $personLabel,
                    'errors' => json_encode(
                        $errors,
                        JSON_UNESCAPED_SLASHES
                    ),
                ]
            );

            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Data ' .
                        strtolower($personLabel) .
                        ' belum valid.',
                    'errors'  => $errors,
                ]);
        }

        $postedOwnerType = strtoupper(
            trim(
                (string) $this->request
                    ->getPost('owner_type')
            )
        );

        $postedOwnerId = (int) $this->request
            ->getPost('owner_id');

        if ($postedOwnerType === '') {
            $legacyCustomerId = (int) $this->request
                ->getPost('customer_id');

            $legacyAgentId = (int) $this->request
                ->getPost('agent_id');

            if ($legacyCustomerId > 0) {
                $postedOwnerType = 'CUSTOMER';
                $postedOwnerId   = $legacyCustomerId;
            } elseif ($legacyAgentId > 0) {
                $postedOwnerType = 'AGENT';
                $postedOwnerId   = $legacyAgentId;
            }
        }

        $userId = (int) (auth()->id() ?? 0);

        if ($userId <= 0) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Sesi user tidak ditemukan.',
                ]);
        }

        try {
            $ownerContext = (new ShipmentOwnerContextService())
                ->resolve(
                    $userId,
                    $this->isOperationalUser(),
                    $postedOwnerType,
                    $postedOwnerId
                );
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ]);
        }

        $type = $addressType;

        try {
            $province = trim(
                (string) (
                    $this->request->getPost('state')
                    ?: $this->request->getPost('province')
                    ?: ''
                )
            );

            $data = [
                'customer_id'     =>
                    $ownerContext['customer_id'],
                'owner_type'      =>
                    $ownerContext['owner_type'],
                'owner_id'        =>
                    $ownerContext['owner_id'],
                'address_type'    => $type,
                'source'          => 'SHIPMENT_INPUT',
                'is_primary'      => 0,
                'address_label'   => $this->nullableText(
                    $this->request->getPost('address_label'),
                    100
                ) ?? ucfirst(strtolower($type)),
                'contact_name'    => mb_substr(
                    trim(
                        (string) $this->request
                            ->getPost('contact_name')
                    ),
                    0,
                    200
                ),
                'company_name'    => $this->nullableText(
                    $this->request->getPost('company_name'),
                    200
                ),
                'phone'           => mb_substr(
                    trim(
                        (string) $this->request
                            ->getPost('phone')
                    ),
                    0,
                    50
                ),
                'email'           => $this->nullableText(
                    $this->request->getPost('email'),
                    150
                ),
                'identity_number' => $this->nullableText(
                    $this->request->getPost('identity_number'),
                    100
                ),
                'country_id'      => (int) $this->request
                    ->getPost('country_id'),
                'state'           => $province,
                'city'            => $this->nullableText(
                    $this->request->getPost('city'),
                    100
                ),
                'postal_code'     => $this->nullableText(
                    $this->request->getPost('postal_code'),
                    20
                ),
                'address'         => mb_substr(
                    trim(
                        (string) $this->request
                            ->getPost('address')
                    ),
                    0,
                    5000
                ),
                'is_default'      => $this->request
                    ->getPost('is_default') ? 1 : 0,
                'status'          => 1,
            ];

            $model = new CustomerAddressModel();
            $id = $model->insert($data, true);

            if (!$id) {
                $modelErrors = $model->errors();

                throw new \RuntimeException(
                    !empty($modelErrors)
                        ? implode(', ', $modelErrors)
                        : 'Alamat gagal disimpan.'
                );
            }

            $address = $model->find($id);

            $country = db_connect()
                ->table('countries')
                ->select(
                    'countries.*, ' .
                    'express_zones.zone_code AS express_zone_code'
                )
                ->join(
                    'express_zones',
                    'express_zones.id = countries.express_zone_id',
                    'left'
                )
                ->where(
                    'countries.id',
                    (int) ($address['country_id'] ?? 0)
                )
                ->get()
                ->getRowArray();

            $address['country_name'] =
                $country['country_name'] ?? '';
            $address['country_code'] =
                $country['country_code'] ?? '';
            $address['express_zone_code'] =
                $country['express_zone_code'] ?? '';

            return $this->response
                ->setStatusCode(201)
                ->setJSON([
                    'status'          => 'success',
                    'success'         => true,
                    'message'         => 'Receiver berhasil disimpan.',
                    'id'              => (int) $id,
                    'data'            => [
                        'id' => (int) $id,
                        'text' => trim(
                            (string) $address['contact_name'] .
                            (!empty($address['company_name'])
                                ? ' - ' . $address['company_name']
                                : '')
                        ),
                    ],
                    'details'         => $address,
                    'address_details' => $address,
                ]);
        } catch (\Throwable $e) {
            log_message(
                'error',
                'SAVE RECEIVER FAILED: {message}',
                [
                    'message' => $e->getMessage(),
                ]
            );

            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ]);
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
                'Halaman alamat hanya tersedia untuk customer.'
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

    private function isOperationalUser(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return $user->inGroup('superadmin')
            || $user->inGroup('admin');
    }

    private function resolveCustomerId(): int
    {
        $postedCustomerId = (int) $this->request
            ->getPost('customer_id');

        if ($this->isOperationalUser()) {
            if ($postedCustomerId <= 0) {
                throw new \RuntimeException(
                    'Customer belum dipilih.'
                );
            }

            $customer = (new CustomerModel())
                ->where('id', $postedCustomerId)
                ->where('status', 1)
                ->first();

            if (!$customer) {
                throw new \RuntimeException(
                    'Customer tidak ditemukan.'
                );
            }

            return (int) $customer['id'];
        }

        $customer = $this->requireCustomer();

        return (int) $customer['id'];
    }

    private function ownedAddress(
        int $customerId,
        int $id
    ): array {
        $address = (new CustomerAddressModel())
            ->where('id', $id)
            ->where('customer_id', $customerId)
            ->where('status', 1)
            ->first();

        if (!$address) {
            throw new \RuntimeException(
                'Alamat tidak ditemukan atau bukan milik customer.'
            );
        }

        return $address;
    }

    private function validatedAddressData(int $customerId): array
    {
        $type = strtoupper(trim((string) $this->request
            ->getPost('address_type')));

        if (!in_array($type, ['SENDER', 'RECEIVER'], true)) {
            throw new \RuntimeException(
                'Address Type harus SENDER atau RECEIVER.'
            );
        }

        $contactName = trim((string) $this->request
            ->getPost('contact_name'));
        $phone = trim((string) $this->request->getPost('phone'));
        $address = trim((string) $this->request
            ->getPost('address'));
        $countryId = (int) $this->request->getPost('country_id');

        if ($contactName === '' || $phone === '' || $address === '') {
            throw new \RuntimeException(
                'Contact Name, Phone, dan Full Address wajib diisi.'
            );
        }

        if ($countryId <= 0) {
            throw new \RuntimeException('Country wajib dipilih.');
        }

        $countryExists = db_connect()
            ->table('countries')
            ->where('id', $countryId)
            ->countAllResults();

        if (!$countryExists) {
            throw new \RuntimeException('Country tidak valid.');
        }

        return [
            'customer_id' => $customerId,
            'owner_type' => 'CUSTOMER',
            'owner_id' => $customerId,
            'address_type' => $type,
            'source' => 'MANUAL',
            'address_label' => $this->nullableText(
                $this->request->getPost('address_label'),
                100
            ) ?? ucfirst(strtolower($type)),
            'contact_name' => mb_substr($contactName, 0, 200),
            'company_name' => $this->nullableText(
                $this->request->getPost('company_name'),
                200
            ),
            'phone' => mb_substr($phone, 0, 50),
            'email' => $this->nullableText(
                $this->request->getPost('email'),
                150
            ),
            'identity_number' => $this->nullableText(
                $this->request->getPost('identity_number'),
                100
            ),
            'address' => mb_substr($address, 0, 5000),
            'city' => $this->nullableText(
                $this->request->getPost('city'),
                100
            ),
            'state' => $this->nullableText(
                $this->request->getPost('state')
                    ?? $this->request->getPost('province'),
                100
            ),
            'postal_code' => $this->nullableText(
                $this->request->getPost('postal_code'),
                20
            ),
            'country_id' => $countryId,
            'is_default' => $this->request
                ->getPost('is_default') ? 1 : 0,
            'status' => 1,
        ];
    }

    private function makeDefault(
        int $customerId,
        int $addressId,
        string $type
    ): void {
        $db = db_connect();
        $db->transBegin();

        try {
            $db->table('customer_addresses')
                ->where('customer_id', $customerId)
                ->where('address_type', strtoupper($type))
                ->update(['is_default' => 0]);

            $db->table('customer_addresses')
                ->where('id', $addressId)
                ->where('customer_id', $customerId)
                ->update(['is_default' => 1]);

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Alamat default gagal diperbarui.'
                );
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    public function search()
    {
        $queryText = trim(
            (string) $this->request->getGet('q')
        );

        $addressType = strtoupper(
            trim(
                (string) $this->request
                    ->getGet('address_type')
            )
        );

        if (!in_array(
            $addressType,
            ['SENDER', 'RECEIVER'],
            true
        )) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Address type tidak valid.',
                    'data'    => [],
                ]);
        }

        $postedOwnerType = strtoupper(
            trim(
                (string) $this->request
                    ->getGet('owner_type')
            )
        );

        $postedOwnerId = (int) $this->request
            ->getGet('owner_id');

        try {
            $ownerContext = (
                new ShipmentOwnerContextService()
            )->resolve(
                (int) (auth()->id() ?? 0),
                $this->isOperationalUser(),
                $postedOwnerType,
                $postedOwnerId
            );
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'data'    => [],
                ]);
        }

        $builder = db_connect()
            ->table('customer_addresses AS ca')
            ->select([
                'ca.id',
                'ca.owner_type',
                'ca.owner_id',
                'ca.customer_id',
                'ca.address_type',
                'ca.contact_name',
                'ca.company_name',
                'ca.phone',
                'ca.email',
                'ca.identity_number',
                'ca.country_id',
                'ca.state',
                'ca.city',
                'ca.postal_code',
                'ca.address',
                'ca.is_primary',
                'ca.source',
                'countries.country_name',
            ])
            ->join(
                'countries',
                'countries.id = ca.country_id',
                'left'
            )
            ->where(
                'ca.owner_type',
                $ownerContext['owner_type']
            )
            ->where(
                'ca.owner_id',
                $ownerContext['owner_id']
            )
            ->where(
                'ca.address_type',
                $addressType
            )
            ->where('ca.status', 1);

        /*
         * Kosong:
         * tampilkan riwayat terbaru.
         *
         * Ada keyword:
         * cari nama, perusahaan, telepon,
         * kota, kode pos, dan alamat.
         */
        if ($queryText !== '') {
            $builder
                ->groupStart()
                    ->like(
                        'ca.contact_name',
                        $queryText
                    )
                    ->orLike(
                        'ca.company_name',
                        $queryText
                    )
                    ->orLike(
                        'ca.phone',
                        $queryText
                    )
                    ->orLike(
                        'ca.email',
                        $queryText
                    )
                    ->orLike(
                        'ca.city',
                        $queryText
                    )
                    ->orLike(
                        'ca.state',
                        $queryText
                    )
                    ->orLike(
                        'ca.postal_code',
                        $queryText
                    )
                    ->orLike(
                        'ca.address',
                        $queryText
                    )
                ->groupEnd();

            $builder
                ->orderBy(
                    'ca.contact_name',
                    'ASC'
                );
        } else {
            $builder
                ->orderBy(
                    'ca.is_primary',
                    'DESC'
                )
                ->orderBy(
                    'ca.updated_at',
                    'DESC'
                )
                ->orderBy(
                    'ca.id',
                    'DESC'
                );
        }

        $addresses = $builder
            ->limit(20)
            ->get()
            ->getResultArray();

        $results = array_map(
            static function (array $address): array {
                $contactName = trim(
                    (string) (
                        $address['contact_name'] ?? ''
                    )
                );

                $companyName = trim(
                    (string) (
                        $address['company_name'] ?? ''
                    )
                );

                $city = trim(
                    (string) (
                        $address['city'] ?? ''
                    )
                );

                $phone = trim(
                    (string) (
                        $address['phone'] ?? ''
                    )
                );

                $labelParts = [];

                if ($contactName !== '') {
                    $labelParts[] = $contactName;
                }

                if (
                    $companyName !== ''
                    && strcasecmp(
                        $companyName,
                        $contactName
                    ) !== 0
                ) {
                    $labelParts[] = $companyName;
                }

                if ($city !== '') {
                    $labelParts[] = $city;
                }

                if ($phone !== '') {
                    $labelParts[] = $phone;
                }

                return [
                    'id' => (int) (
                        $address['id'] ?? 0
                    ),

                    'label' => implode(
                        ' · ',
                        $labelParts
                    ),

                    'contact_name' =>
                        $contactName,

                    'company_name' =>
                        $companyName,

                    'phone' => $phone,

                    'email' => (string) (
                        $address['email'] ?? ''
                    ),

                    'identity_number' => (string) (
                        $address['identity_number']
                        ?? ''
                    ),

                    'country_id' => (int) (
                        $address['country_id'] ?? 0
                    ),

                    'country_name' => (string) (
                        $address['country_name'] ?? ''
                    ),

                    'state' => (string) (
                        $address['state'] ?? ''
                    ),

                    'city' => $city,

                    'postal_code' => (string) (
                        $address['postal_code'] ?? ''
                    ),

                    'address' => (string) (
                        $address['address'] ?? ''
                    ),

                    'is_primary' => (int) (
                        $address['is_primary'] ?? 0
                    ),

                    'source' => (string) (
                        $address['source'] ?? ''
                    ),
                ];
            },
            $addresses
        );

        return $this->response->setJSON([
            'status' => 'success',

            'owner' => [
                'type' => $ownerContext['owner_type'],
                'id'   => $ownerContext['owner_id'],
                'name' => $ownerContext['owner_name'],
            ],

            'address_type' => $addressType,
            'data'         => $results,
        ]);
    }

    private function countryOptions(): array
    {
        $db = db_connect();
        $builder = $db->table('countries');

        if ($db->fieldExists('status', 'countries')) {
            $builder->where('status', 1);
        }

        return $builder
            ->orderBy('country_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function nullableText($value, int $maxLength): ?string
    {
        $text = trim((string) $value);

        return $text === ''
            ? null
            : mb_substr($text, 0, $maxLength);
    }
}
