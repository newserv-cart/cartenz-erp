<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CustomerAddressModel;
use App\Models\CustomerModel;

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
            $customer = $this->requireCustomer();
            $data = $this->validatedAddressData(
                (int) $customer['id']
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

        try {
            $customer = $this->requireCustomer();
            $data = $this->validatedAddressData(
                (int) $customer['id']
            );
            $model = new CustomerAddressModel();
            $id = $model->insert($data, true);

            if (!$id) {
                throw new \RuntimeException(
                    'Alamat gagal disimpan.'
                );
            }

            $address = $model->find($id);
            $country = db_connect()
                ->table('countries')
                ->where('id', (int) ($address['country_id'] ?? 0))
                ->get()
                ->getRowArray();
            $address['country_name'] =
                $country['country_name'] ?? '';

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => (int) $id,
                    'text' => trim(
                        (string) $address['contact_name'] .
                        (!empty($address['company_name'])
                            ? ' - ' . $address['company_name']
                            : '')
                    ),
                ],
                'address_details' => $address,
            ]);
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'status' => 'error',
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
            'address_type' => $type,
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
