<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\VendorModel;
use App\Services\ExpressRateImportService;
use CodeIgniter\Exceptions\PageNotFoundException;

class ExpressRateImportController extends BaseController
{
    protected VendorModel $vendorModel;
    protected ExpressRateImportService $importService;

    public function __construct()
    {
        $this->vendorModel   = new VendorModel();
        $this->importService = new ExpressRateImportService();
    }

    public function form($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        return view('master/vendors/express_import_form', [
            'vendor'            => $vendor,
            'services'          => $this->expressServices(),
            'currencies'        => $this->currencyOptions(),
            'defaultCurrencyId' => $this->defaultCurrencyId('IDR'),
        ]);
    }

    public function preview($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);

        try {
            $file = $this->request->getFile('rate_file');

            if (!$file || !$file->isValid()) {
                throw new \RuntimeException(
                    $file?->getErrorString()
                    ?: 'Pilih file XLSX yang akan diimpor.'
                );
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new \RuntimeException(
                    'Ukuran file maksimal 10 MB.'
                );
            }

            $originalName = $file->getClientName();
            $extension = strtolower(
                pathinfo($originalName, PATHINFO_EXTENSION)
            );

            if ($extension !== 'xlsx') {
                throw new \RuntimeException(
                    'Format file wajib .xlsx.'
                );
            }

            $preview = $this->importService->createPreview(
                (int) $vendor['id'],
                $this->request->getPost(),
                $file->getTempName(),
                $originalName,
                $this->requireActorId()
            );

            return redirect()->to(
                site_url(
                    'master/vendors/' .
                    $vendor['id'] .
                    '/express-import/preview/' .
                    $preview['token']
                )
            );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function showPreview($vendorId, $token)
    {
        $vendor = $this->findVendor((int) $vendorId);

        try {
            $preview = $this->importService->loadPreview(
                (string) $token,
                $this->requireActorId(),
                (int) $vendor['id']
            );

            return view(
                'master/vendors/express_import_preview',
                [
                    'vendor'   => $vendor,
                    'preview'  => $preview,
                    'service'  => $this->serviceById(
                        (int) $preview['service_id']
                    ),
                    'currency' => $this->currencyById(
                        (int) $preview['currency_id']
                    ),
                ]
            );
        } catch (\Throwable $e) {
            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-import'
                    )
                )
                ->with('error', $e->getMessage());
        }
    }

    public function commit($vendorId)
    {
        $vendor = $this->findVendor((int) $vendorId);
        $token  = trim(
            (string) $this->request->getPost('token')
        );

        try {
            $result = $this->importService->commit(
                $token,
                $this->requireActorId()
            );

            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-rates/' .
                        $result['header_id']
                    )
                )
                ->with(
                    'success',
                    number_format($result['imported_rows']) .
                    ' tarif Express berhasil diimpor secara transactional.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(
                    site_url(
                        'master/vendors/' .
                        $vendor['id'] .
                        '/express-import/preview/' .
                        rawurlencode($token)
                    )
                )
                ->with('error', $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $path = FCPATH
            . 'templates'
            . DIRECTORY_SEPARATOR
            . 'express_rate_import_template.xlsx';

        if (!is_file($path)) {
            throw PageNotFoundException::forPageNotFound(
                'Template import tidak ditemukan.'
            );
        }

        return $this->response
            ->download($path, null)
            ->setFileName(
                'CARTENZ_Express_Rate_Import_Template.xlsx'
            );
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

    private function currencyOptions(): array
    {
        $rows = db_connect()
            ->table('currencies')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['_label'] = $this->currencyLabel($row);
        }
        unset($row);

        return $rows;
    }

    private function defaultCurrencyId(string $code): ?int
    {
        foreach ($this->currencyOptions() as $row) {
            if (
                strtoupper(
                    (string) ($row['_label'] ?? '')
                ) === strtoupper($code)
                || strtoupper(
                    (string) ($row['currency_code'] ?? '')
                ) === strtoupper($code)
                || strtoupper(
                    (string) ($row['code'] ?? '')
                ) === strtoupper($code)
            ) {
                return (int) $row['id'];
            }
        }

        return null;
    }

    private function serviceById(int $id): array
    {
        return db_connect()
            ->table('services')
            ->where('id', $id)
            ->get()
            ->getRowArray() ?? [];
    }

    private function currencyById(int $id): array
    {
        $row = db_connect()
            ->table('currencies')
            ->where('id', $id)
            ->get()
            ->getRowArray() ?? [];

        $row['_label'] = $this->currencyLabel($row);

        return $row;
    }

    private function currencyLabel(array $row): string
    {
        foreach (
            [
                'currency_code',
                'code',
                'currency_name',
                'name',
            ] as $field
        ) {
            if (!empty($row[$field])) {
                return (string) $row[$field];
            }
        }

        return 'Currency #' . ($row['id'] ?? '-');
    }

    private function requireActorId(): int
    {
        $actorId = auth()->id();

        if (!$actorId) {
            throw new \RuntimeException(
                'Sesi admin tidak ditemukan. Silakan login ulang.'
            );
        }

        return (int) $actorId;
    }
}
