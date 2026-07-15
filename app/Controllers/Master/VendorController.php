<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\VendorModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VendorController extends BaseController
{
    protected VendorModel $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $vendors = $this->vendorModel
            ->orderBy('vendor_priority', 'ASC')
            ->orderBy('vendor_name', 'ASC')
            ->findAll();

        return view('master/vendors/index', [
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        return view('master/vendors/form', [
            'vendor'    => null,
            'countries' => $this->masterRows(
                'countries',
                'country_name'
            ),
            'formTitle' => 'Add Vendor',
            'formAction'=> site_url('master/vendors/store'),
        ]);
    }

    public function store()
    {
        try {
            $data = $this->validatedVendorData();

            if (!$this->vendorModel->insert($data)) {
                throw new \RuntimeException(
                    'Vendor gagal disimpan.'
                );
            }

            return redirect()
                ->to(site_url('master/vendors'))
                ->with('success', 'Vendor berhasil ditambahkan.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $vendor = $this->findVendor((int) $id);
        $db     = db_connect();

        $regularRates = $db
            ->table('vendor_rates vr')
            ->select([
                'vr.*',
                'c.country_name',
                's.service_name',
            ])
            ->join(
                'countries c',
                'c.id = vr.country_id',
                'left'
            )
            ->join(
                'services s',
                's.id = vr.service_id',
                'left'
            )
            ->where('vr.vendor_id', (int) $vendor['id'])
            ->orderBy('c.country_name', 'ASC')
            ->orderBy('vr.weight_from', 'ASC')
            ->get()
            ->getResultArray();

        $expressHeaders = $db
            ->table('express_rate_headers erh')
            ->select([
                'erh.*',
                's.service_name',
                'COUNT(erd.id) AS total_details',
            ])
            ->join(
                'services s',
                's.id = erh.service_id',
                'left'
            )
            ->join(
                'express_rate_details erd',
                'erd.header_id = erh.id',
                'left'
            )
            ->where('erh.vendor_id', (int) $vendor['id'])
            ->groupBy('erh.id')
            ->orderBy('erh.effective_date', 'DESC')
            ->get()
            ->getResultArray();

        $currencyRows = $db
            ->table('currencies')
            ->get()
            ->getResultArray();

        $currencyMap = [];

        foreach ($currencyRows as $currency) {
            $label = $currency['currency_code']
                ?? $currency['code']
                ?? $currency['currency_name']
                ?? $currency['name']
                ?? ('Currency #' . $currency['id']);

            $currencyMap[(int) $currency['id']] = $label;
        }

        foreach ($expressHeaders as &$header) {
            $header['currency_label'] = !empty($header['currency_id'])
                ? ($currencyMap[(int) $header['currency_id']] ?? '-')
                : '-';
        }
        unset($header);

        return view('master/vendors/show', [
            'vendor'         => $vendor,
            'regularRates'   => $regularRates,
            'expressHeaders' => $expressHeaders,
        ]);
    }

    public function edit($id)
    {
        $vendor = $this->findVendor((int) $id);

        return view('master/vendors/form', [
            'vendor'    => $vendor,
            'countries' => $this->masterRows(
                'countries',
                'country_name'
            ),
            'formTitle' => 'Edit Vendor',
            'formAction'=> site_url(
                'master/vendors/update/' . $vendor['id']
            ),
        ]);
    }

    public function update($id)
    {
        $vendor = $this->findVendor((int) $id);

        try {
            $data = $this->validatedVendorData(
                (int) $vendor['id']
            );

            if (
                !$this->vendorModel->update(
                    (int) $vendor['id'],
                    $data
                )
            ) {
                throw new \RuntimeException(
                    'Vendor gagal diperbarui.'
                );
            }

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' . $vendor['id']
                    )
                )
                ->with('success', 'Vendor berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $vendor = $this->findVendor((int) $id);

        try {
            $newStatus = (int) ($vendor['status'] ?? 0) === 1
                ? 0
                : 1;

            if (
                !$this->vendorModel->update(
                    (int) $vendor['id'],
                    ['status' => $newStatus]
                )
            ) {
                throw new \RuntimeException(
                    'Status vendor gagal diperbarui.'
                );
            }

            return redirect()
                ->back()
                ->with(
                    'success',
                    $newStatus === 1
                        ? 'Vendor berhasil diaktifkan.'
                        : 'Vendor berhasil dinonaktifkan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    private function validatedVendorData(
        ?int $ignoreId = null
    ): array {
        $vendorCode = strtoupper(
            trim(
                (string) $this->request
                    ->getPost('vendor_code')
            )
        );

        $vendorName = trim(
            (string) $this->request
                ->getPost('vendor_name')
        );

        if (
            $vendorCode === ''
            || !preg_match('/^[A-Z0-9_\-]{2,30}$/', $vendorCode)
        ) {
            throw new \RuntimeException(
                'Vendor Code wajib 2–30 karakter: huruf, angka, tanda hubung, atau underscore.'
            );
        }

        if (mb_strlen($vendorName) < 2) {
            throw new \RuntimeException(
                'Vendor Name wajib minimal 2 karakter.'
            );
        }

        $duplicate = $this->vendorModel
            ->where('vendor_code', $vendorCode);

        if ($ignoreId !== null) {
            $duplicate->where('id !=', $ignoreId);
        }

        if ($duplicate->first()) {
            throw new \RuntimeException(
                'Vendor Code sudah digunakan.'
            );
        }

        $email = trim(
            (string) $this->request->getPost('email')
        );

        if (
            $email !== ''
            && !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new \RuntimeException(
                'Format email vendor tidak valid.'
            );
        }

        return [
            'vendor_code'       => $vendorCode,
            'vendor_name'       => $vendorName,
            'vendor_category'   => $this->nullableText(
                $this->request->getPost('vendor_category')
            ),
            'vendor_type'       => $this->nullableText(
                $this->request->getPost('vendor_type')
            ),
            'vendor_priority'   => max(
                0,
                (int) $this->request
                    ->getPost('vendor_priority')
            ),
            'country_id'        => $this->nullablePositiveInt(
                $this->request->getPost('country_id')
            ),
            'contact_person'    => $this->nullableText(
                $this->request->getPost('contact_person')
            ),
            'phone'             => $this->nullableText(
                $this->request->getPost('phone')
            ),
            'email'             => $email !== '' ? $email : null,
            'website'           => $this->nullableText(
                $this->request->getPost('website')
            ),
            'address'           => $this->nullableText(
                $this->request->getPost('address')
            ),
            'api_available'     => $this->request
                ->getPost('api_available') ? 1 : 0,
            'tracking_url'      => $this->nullableText(
                $this->request->getPost('tracking_url')
            ),
            'credit_limit'      => max(
                0,
                (float) $this->request
                    ->getPost('credit_limit')
            ),
            'deposit_balance'   => max(
                0,
                (float) $this->request
                    ->getPost('deposit_balance')
            ),
            'payment_term_days' => max(
                0,
                (int) $this->request
                    ->getPost('payment_term_days')
            ),
            'status'            => $this->request
                ->getPost('status') ? 1 : 0,
            'notes'             => $this->nullableText(
                $this->request->getPost('notes')
            ),
        ];
    }

    private function findVendor(int $id): array
    {
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            throw PageNotFoundException::forPageNotFound(
                'Vendor tidak ditemukan.'
            );
        }

        return $vendor;
    }

    private function masterRows(
        string $table,
        string $orderColumn
    ): array {
        return db_connect()
            ->table($table)
            ->orderBy($orderColumn, 'ASC')
            ->get()
            ->getResultArray();
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function nullablePositiveInt($value): ?int
    {
        $number = (int) $value;

        return $number > 0 ? $number : null;
    }
}
