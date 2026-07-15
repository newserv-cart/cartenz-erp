<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\ExpressRateDetailModel;
use App\Models\ExpressRateHeaderModel;
use App\Models\VendorModel;
use App\Models\VendorRateModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VendorRateController extends BaseController
{
    protected VendorModel $vendorModel;
    protected VendorRateModel $regularRateModel;
    protected ExpressRateHeaderModel $expressHeaderModel;
    protected ExpressRateDetailModel $expressDetailModel;

    public function __construct()
    {
        $this->vendorModel        = new VendorModel();
        $this->regularRateModel   = new VendorRateModel();
        $this->expressHeaderModel = new ExpressRateHeaderModel();
        $this->expressDetailModel = new ExpressRateDetailModel();
    }

    public function createRegular($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        return view('master/vendors/regular_rate_form', [
            'vendor'         => $vendor,
            'rate'           => null,
            'countries'      => $this->masterRows(
                'countries',
                'country_name'
            ),
            'services'       => $this->regularServices(),
            'transportModes' => $this->masterRows(
                'transport_modes',
                'id'
            ),
            'deliveryTypes'  => $this->masterRows(
                'delivery_types',
                'id'
            ),
            'formAction'     => site_url(
                'master/vendors/' .
                $vendor['id'] .
                '/regular-rates/store'
            ),
        ]);
    }

    public function storeRegular($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        try {
            $data = $this->validatedRegularRateData(
                (int) $vendor['id']
            );

            $this->assertNoRegularOverlap($data);

            if (!$this->regularRateModel->insert($data)) {
                throw new \RuntimeException(
                    'Vendor Regular Rate gagal disimpan.'
                );
            }

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' . $vendor['id']
                    )
                )
                ->with(
                    'success',
                    'Vendor Regular Rate berhasil ditambahkan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function editRegular($vendorId, $rateId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $rate   = $this->findOwnedRegularRate(
            (int) $vendor['id'],
            (int) $rateId
        );

        return view('master/vendors/regular_rate_form', [
            'vendor'         => $vendor,
            'rate'           => $rate,
            'countries'      => $this->masterRows(
                'countries',
                'country_name'
            ),
            'services'       => $this->regularServices(),
            'transportModes' => $this->masterRows(
                'transport_modes',
                'id'
            ),
            'deliveryTypes'  => $this->masterRows(
                'delivery_types',
                'id'
            ),
            'formAction'     => site_url(
                'master/vendors/' .
                $vendor['id'] .
                '/regular-rates/update/' .
                $rate['id']
            ),
        ]);
    }

    public function updateRegular($vendorId, $rateId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $rate   = $this->findOwnedRegularRate(
            (int) $vendor['id'],
            (int) $rateId
        );

        try {
            $data = $this->validatedRegularRateData(
                (int) $vendor['id']
            );

            $this->assertNoRegularOverlap(
                $data,
                (int) $rate['id']
            );

            if (
                !$this->regularRateModel->update(
                    (int) $rate['id'],
                    $data
                )
            ) {
                throw new \RuntimeException(
                    'Vendor Regular Rate gagal diperbarui.'
                );
            }

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' . $vendor['id']
                    )
                )
                ->with(
                    'success',
                    'Vendor Regular Rate berhasil diperbarui.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function deleteRegular($vendorId, $rateId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $rate   = $this->findOwnedRegularRate(
            (int) $vendor['id'],
            (int) $rateId
        );

        if (
            !$this->regularRateModel->delete(
                (int) $rate['id']
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Vendor Regular Rate gagal dihapus.'
                );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Vendor Regular Rate berhasil dihapus.'
            );
    }

    public function createExpressHeader($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        return view('master/vendors/express_header_form', [
            'vendor'            => $vendor,
            'services'          => $this->expressServices(),
            'currencies'        => $this->currencyOptions(),
            'defaultCurrencyId' => $this->defaultCurrencyId('IDR'),
            'formAction'        => site_url(
                'master/vendors/' .
                $vendor['id'] .
                '/express-rates/store'
            ),
        ]);
    }

    public function storeExpressHeader($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        try {
            $data = $this->validatedExpressHeaderData(
                (int) $vendor['id']
            );

            $duplicate = $this->expressHeaderModel
                ->where('vendor_id', $data['vendor_id'])
                ->where('service_id', $data['service_id'])
                ->where(
                    'effective_date',
                    $data['effective_date']
                )
                ->first();

            if ($duplicate) {
                throw new \RuntimeException(
                    'Express Rate Header dengan vendor, service, dan effective date yang sama sudah ada.'
                );
            }

            $headerId = $this->expressHeaderModel
                ->insert($data, true);

            if (!$headerId) {
                throw new \RuntimeException(
                    'Express Rate Header gagal disimpan.'
                );
            }

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-rates/' .
                        $headerId
                    )
                )
                ->with(
                    'success',
                    'Express Rate Header berhasil dibuat.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function showExpressHeader($vendorId, $headerId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $header = $this->findOwnedExpressHeader(
            (int) $vendor['id'],
            (int) $headerId
        );

        $service = db_connect()
            ->table('services')
            ->where('id', (int) $header['service_id'])
            ->get()
            ->getRowArray();

        $details = $this->expressDetailModel
            ->where('header_id', (int) $header['id'])
            ->orderBy('shipment_type', 'ASC')
            ->orderBy('zone', 'ASC')
            ->orderBy('weight', 'ASC')
            ->findAll();

        return view('master/vendors/express_header_show', [
            'vendor'  => $vendor,
            'header'  => $header,
            'service' => $service,
            'details' => $details,
        ]);
    }

    public function deleteExpressHeader($vendorId, $headerId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $header = $this->findOwnedExpressHeader(
            (int) $vendor['id'],
            (int) $headerId
        );

        $db = db_connect();
        $db->transBegin();

        try {
            $this->expressDetailModel
                ->where('header_id', (int) $header['id'])
                ->delete();

            if (
                !$this->expressHeaderModel->delete(
                    (int) $header['id']
                )
            ) {
                throw new \RuntimeException(
                    'Express Rate Header gagal dihapus.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penghapusan Express Rate gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' . $vendor['id']
                    )
                )
                ->with(
                    'success',
                    'Express Rate Header dan seluruh detailnya berhasil dihapus.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function storeExpressDetail($vendorId, $headerId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $header = $this->findOwnedExpressHeader(
            (int) $vendor['id'],
            (int) $headerId
        );

        try {
            $data = $this->validatedExpressDetailData(
                (int) $header['id']
            );

            $duplicate = $this->expressDetailModel
                ->where('header_id', $data['header_id'])
                ->where(
                    'shipment_type',
                    $data['shipment_type']
                )
                ->where('zone', $data['zone'])
                ->where('weight', $data['weight'])
                ->first();

            if ($duplicate) {
                throw new \RuntimeException(
                    'Detail rate dengan shipment type, zone, dan weight yang sama sudah ada.'
                );
            }

            if (!$this->expressDetailModel->insert($data)) {
                throw new \RuntimeException(
                    'Express Rate Detail gagal disimpan.'
                );
            }

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Express Rate Detail berhasil ditambahkan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function deleteExpressDetail(
        $vendorId,
        $headerId,
        $detailId
    ) {
        $vendor = $this->findVendor((int) $vendorId);
        $header = $this->findOwnedExpressHeader(
            (int) $vendor['id'],
            (int) $headerId
        );

        $detail = $this->expressDetailModel
            ->where('id', (int) $detailId)
            ->where('header_id', (int) $header['id'])
            ->first();

        if (!$detail) {
            throw PageNotFoundException::forPageNotFound(
                'Express Rate Detail tidak ditemukan.'
            );
        }

        if (
            !$this->expressDetailModel->delete(
                (int) $detail['id']
            )
        ) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Express Rate Detail gagal dihapus.'
                );
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Express Rate Detail berhasil dihapus.'
            );
    }

    private function validatedRegularRateData(
        int $vendorId
    ): array {
        $weightFrom = (float) $this->request
            ->getPost('weight_from');

        $weightTo = (float) $this->request
            ->getPost('weight_to');

        $effectiveFrom = $this->validDate(
            $this->request->getPost('effective_from'),
            'Effective From',
            false
        );

        $effectiveUntil = $this->validDate(
            $this->request->getPost('effective_until'),
            'Effective Until',
            true
        );

        if (
            $effectiveUntil !== null
            && $effectiveUntil < $effectiveFrom
        ) {
            throw new \RuntimeException(
                'Effective Until tidak boleh sebelum Effective From.'
            );
        }

        if ($weightFrom < 0 || $weightTo <= 0) {
            throw new \RuntimeException(
                'Weight range harus lebih besar dari nol.'
            );
        }

        if ($weightTo < $weightFrom) {
            throw new \RuntimeException(
                'Weight To tidak boleh lebih kecil dari Weight From.'
            );
        }

        $costPerKg = (float) $this->request
            ->getPost('cost_per_kg');

        $minimumCharge = (float) $this->request
            ->getPost('minimum_charge');

        if ($costPerKg < 0 || $minimumCharge < 0) {
            throw new \RuntimeException(
                'Vendor cost dan minimum charge tidak boleh negatif.'
            );
        }

        return [
            'vendor_id'         => $vendorId,
            'country_id'        => $this->requiredPositiveInt(
                'country_id',
                'Country'
            ),
            'service_id'        => $this->requiredPositiveInt(
                'service_id',
                'Service'
            ),
            'transport_mode_id' => $this->requiredPositiveInt(
                'transport_mode_id',
                'Transport Mode'
            ),
            'delivery_type_id'  => $this->requiredPositiveInt(
                'delivery_type_id',
                'Delivery Type'
            ),
            'weight_from'       => round($weightFrom, 2),
            'weight_to'         => round($weightTo, 2),
            'cost_per_kg'       => round($costPerKg, 2),
            'minimum_charge'    => round($minimumCharge, 2),
            'effective_from'    => $effectiveFrom,
            'effective_until'   => $effectiveUntil,
            'status'            => $this->request
                ->getPost('status') ? 1 : 0,
        ];
    }

    private function assertNoRegularOverlap(
        array $data,
        ?int $ignoreId = null
    ): void {
        $builder = db_connect()
            ->table('vendor_rates')
            ->where('vendor_id', $data['vendor_id'])
            ->where('country_id', $data['country_id'])
            ->where('service_id', $data['service_id'])
            ->where(
                'transport_mode_id',
                $data['transport_mode_id']
            )
            ->where(
                'delivery_type_id',
                $data['delivery_type_id']
            )
            ->where(
                'weight_from <=',
                $data['weight_to']
            )
            ->where(
                'weight_to >=',
                $data['weight_from']
            )
            ->groupStart()
                ->where(
                    'effective_until IS NULL',
                    null,
                    false
                )
                ->orWhere(
                    'effective_until >=',
                    $data['effective_from']
                )
            ->groupEnd();

        if ($data['effective_until'] !== null) {
            $builder
                ->where(
                    'effective_from <=',
                    $data['effective_until']
                );
        }

        if ($ignoreId !== null) {
            $builder->where('id !=', $ignoreId);
        }

        if ($builder->limit(1)->get()->getRowArray()) {
            throw new \RuntimeException(
                'Vendor Regular Rate bertumpang tindih pada kombinasi vendor, country, service, mode, delivery, weight, dan effective period yang sama.'
            );
        }
    }

    private function validatedExpressHeaderData(
        int $vendorId
    ): array {
        return [
            'vendor_id'      => $vendorId,
            'service_id'     => $this->requiredPositiveInt(
                'service_id',
                'Service'
            ),
            'currency_id'    => $this->requiredPositiveInt(
                'currency_id',
                'Currency'
            ),
            'effective_date' => $this->validDate(
                $this->request->getPost('effective_date'),
                'Effective Date',
                false
            ),
            'remarks'        => $this->nullableText(
                $this->request->getPost('remarks')
            ),
        ];
    }

    private function validatedExpressDetailData(
        int $headerId
    ): array {
        $shipmentType = strtoupper(
            trim(
                (string) $this->request
                    ->getPost('shipment_type')
            )
        );

        if (
            !in_array(
                $shipmentType,
                ['DOCUMENT', 'PACKAGE'],
                true
            )
        ) {
            throw new \RuntimeException(
                'Shipment Type Express tidak valid.'
            );
        }

        $zone = (int) $this->request->getPost('zone');
        $weight = (float) $this->request
            ->getPost('weight');
        $price = (float) $this->request
            ->getPost('price');

        if ($zone < 1 || $zone > 99) {
            throw new \RuntimeException(
                'Zone wajib antara 1 dan 99.'
            );
        }

        if ($weight <= 0 || $price < 0) {
            throw new \RuntimeException(
                'Weight harus lebih besar dari nol dan price tidak boleh negatif.'
            );
        }

        return [
            'header_id'     => $headerId,
            'shipment_type' => $shipmentType,
            'zone'          => $zone,
            'weight'        => round($weight, 2),
            'price'         => round($price, 2),
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

    private function findOwnedRegularRate(
        int $vendorId,
        int $rateId
    ): array {
        $rate = $this->regularRateModel
            ->where('id', $rateId)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$rate) {
            throw PageNotFoundException::forPageNotFound(
                'Vendor Regular Rate tidak ditemukan.'
            );
        }

        return $rate;
    }

    private function findOwnedExpressHeader(
        int $vendorId,
        int $headerId
    ): array {
        $header = $this->expressHeaderModel
            ->where('id', $headerId)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$header) {
            throw PageNotFoundException::forPageNotFound(
                'Express Rate Header tidak ditemukan.'
            );
        }

        return $header;
    }

    private function regularServices(): array
    {
        return db_connect()
            ->table('services')
            ->where('pricing_engine', 'REGULAR')
            ->where('status', 1)
            ->orderBy('service_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function expressServices(): array
    {
        return db_connect()
            ->table('services')
            ->where('pricing_engine', 'EXPRESS')
            ->where('status', 1)
            ->orderBy('service_name', 'ASC')
            ->get()
            ->getResultArray();
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

    private function currencyOptions(): array
    {
        $rows = db_connect()
            ->table('currencies')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['_label'] = $row['currency_code']
                ?? $row['code']
                ?? $row['currency_name']
                ?? $row['name']
                ?? ('Currency #' . $row['id']);
        }
        unset($row);

        return $rows;
    }

    private function defaultCurrencyId(string $code): ?int
    {
        foreach ($this->currencyOptions() as $row) {
            if (
                strtoupper((string) $row['_label'])
                === strtoupper($code)
            ) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function requiredPositiveInt(
        string $field,
        string $label
    ): int {
        $value = (int) $this->request
            ->getPost($field);

        if ($value <= 0) {
            throw new \RuntimeException(
                $label . ' wajib dipilih.'
            );
        }

        return $value;
    }

    private function validDate(
        $value,
        string $label,
        bool $nullable
    ): ?string {
        $value = trim((string) $value);

        if ($value === '' && $nullable) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $value
        );

        if (!$date || $date->format('Y-m-d') !== $value) {
            throw new \RuntimeException(
                $label . ' tidak valid.'
            );
        }

        return $value;
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
