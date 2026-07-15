<?php

namespace App\Services;

use App\Models\ExpressRateDetailModel;
use App\Models\ExpressRateHeaderModel;
use App\Models\ExpressRateImportBatchModel;
use App\Models\VendorModel;
use CodeIgniter\Database\BaseConnection;

class ExpressRateImportService
{
    private const PREVIEW_TTL_SECONDS = 7200;

    protected BaseConnection $db;
    protected ExpressRateExcelParserService $parser;
    protected VendorModel $vendorModel;
    protected ExpressRateHeaderModel $headerModel;
    protected ExpressRateDetailModel $detailModel;
    protected ExpressRateImportBatchModel $batchModel;

    public function __construct()
    {
        $this->db          = db_connect();
        $this->parser      = new ExpressRateExcelParserService();
        $this->vendorModel = new VendorModel();
        $this->headerModel = new ExpressRateHeaderModel();
        $this->detailModel = new ExpressRateDetailModel();
        $this->batchModel  = new ExpressRateImportBatchModel();
    }

    public function createPreview(
        int $vendorId,
        array $input,
        string $filePath,
        string $originalFilename,
        int $actorId
    ): array {
        $metadata = $this->validateMetadata(
            $vendorId,
            $input
        );

        $parsed = $this->parser->parse($filePath);

        $existingHeader = $this->headerModel
            ->where('vendor_id', $vendorId)
            ->where('service_id', $metadata['service_id'])
            ->where('effective_date', $metadata['effective_date'])
            ->first();

        $conflicts = [];

        if (
            $existingHeader
            && $metadata['import_mode'] === 'APPEND'
        ) {
            $conflicts = $this->findExistingConflicts(
                (int) $existingHeader['id'],
                $parsed['rows']
            );
        }

        $token = bin2hex(random_bytes(24));
        $payload = [
            'token'             => $token,
            'actor_id'          => $actorId,
            'vendor_id'         => $vendorId,
            'service_id'        => $metadata['service_id'],
            'currency_id'       => $metadata['currency_id'],
            'effective_date'    => $metadata['effective_date'],
            'import_mode'       => $metadata['import_mode'],
            'remarks'           => $metadata['remarks'],
            'original_filename' => basename($originalFilename),
            'source_format'     => $parsed['format'],
            'sheet_name'        => $parsed['sheet_name'],
            'summary'           => $parsed['summary'],
            'warnings'          => $parsed['warnings'],
            'rows'              => $parsed['rows'],
            'existing_header_id'=> $existingHeader
                ? (int) $existingHeader['id']
                : null,
            'conflicts'         => $conflicts,
            'can_import'        => $conflicts === [],
            'created_at'        => time(),
            'expires_at'        => time() + self::PREVIEW_TTL_SECONDS,
        ];

        $this->writePreview($token, $payload);

        return $payload;
    }

    public function loadPreview(
        string $token,
        int $actorId,
        ?int $vendorId = null
    ): array {
        if (!preg_match('/^[a-f0-9]{48}$/', $token)) {
            throw new \RuntimeException(
                'Token preview tidak valid.'
            );
        }

        $path = $this->previewPath($token);

        if (!is_file($path)) {
            throw new \RuntimeException(
                'Preview import tidak ditemukan atau sudah digunakan.'
            );
        }

        $payload = json_decode(
            (string) file_get_contents($path),
            true
        );

        if (!is_array($payload)) {
            throw new \RuntimeException(
                'Data preview import rusak.'
            );
        }

        if ((int) ($payload['actor_id'] ?? 0) !== $actorId) {
            throw new \RuntimeException(
                'Preview import dimiliki oleh user lain.'
            );
        }

        if (
            $vendorId !== null
            && (int) ($payload['vendor_id'] ?? 0) !== $vendorId
        ) {
            throw new \RuntimeException(
                'Preview import tidak cocok dengan vendor.'
            );
        }

        if ((int) ($payload['expires_at'] ?? 0) < time()) {
            @unlink($path);

            throw new \RuntimeException(
                'Preview import sudah kedaluwarsa. Upload ulang file.'
            );
        }

        return $payload;
    }

    public function commit(
        string $token,
        int $actorId
    ): array {
        $payload = $this->loadPreview($token, $actorId);

        if (empty($payload['can_import'])) {
            throw new \RuntimeException(
                'Import belum dapat dilanjutkan karena ada duplikat ' .
                'dengan header lama. Pilih mode REPLACE atau perbaiki file.'
            );
        }

        $this->db->transBegin();

        try {
            $vendor = $this->lockRow(
                'vendors',
                (int) $payload['vendor_id']
            );

            if ((int) ($vendor['status'] ?? 0) !== 1) {
                throw new \RuntimeException(
                    'Vendor tidak aktif.'
                );
            }

            $service = $this->lockRow(
                'services',
                (int) $payload['service_id']
            );

            if (
                strtoupper(
                    (string) ($service['pricing_engine'] ?? '')
                ) !== 'EXPRESS'
            ) {
                throw new \RuntimeException(
                    'Service bukan Express Service.'
                );
            }

            $this->lockRow(
                'currencies',
                (int) $payload['currency_id']
            );

            $header = $this->lockHeader(
                (int) $payload['vendor_id'],
                (int) $payload['service_id'],
                (string) $payload['effective_date']
            );

            if (
                $header
                && !empty($header['currency_id'])
                && (int) $header['currency_id']
                    !== (int) $payload['currency_id']
            ) {
                throw new \RuntimeException(
                    'Currency header lama berbeda dengan currency import.'
                );
            }

            if (
                $header
                && $payload['import_mode'] === 'APPEND'
            ) {
                $conflicts = $this->findExistingConflicts(
                    (int) $header['id'],
                    $payload['rows']
                );

                if ($conflicts !== []) {
                    throw new \RuntimeException(
                        'Data berubah setelah preview. Ditemukan ' .
                        count($conflicts) . ' duplikat pada database.'
                    );
                }
            }

            $batchId = $this->batchModel->insert([
                'vendor_id'         => (int) $payload['vendor_id'],
                'service_id'        => (int) $payload['service_id'],
                'currency_id'       => (int) $payload['currency_id'],
                'header_id'         => $header
                    ? (int) $header['id']
                    : null,
                'effective_date'    => $payload['effective_date'],
                'original_filename' => $payload['original_filename'],
                'source_format'     => $payload['source_format'],
                'import_mode'       => $payload['import_mode'],
                'total_rows'        => count($payload['rows']),
                'imported_rows'     => 0,
                'status'            => 'PROCESSING',
                'error_summary'     => null,
                'imported_by'       => $actorId,
                'imported_at'       => null,
                'created_at'        => date('Y-m-d H:i:s'),
            ], true);

            if (!$batchId) {
                throw new \RuntimeException(
                    'Audit batch import gagal dibuat.'
                );
            }

            if (!$header) {
                $headerId = $this->headerModel->insert([
                    'vendor_id'      => (int) $payload['vendor_id'],
                    'service_id'     => (int) $payload['service_id'],
                    'currency_id'    => (int) $payload['currency_id'],
                    'effective_date' => $payload['effective_date'],
                    'remarks'        => $payload['remarks'],
                    'import_source'  => $payload['original_filename'],
                    'import_batch_id'=> (int) $batchId,
                ], true);

                if (!$headerId) {
                    throw new \RuntimeException(
                        'Express Rate Header gagal dibuat.'
                    );
                }
            } else {
                $headerId = (int) $header['id'];

                if (
                    $payload['import_mode'] === 'REPLACE'
                    && !$this->detailModel
                        ->where('header_id', $headerId)
                        ->delete()
                ) {
                    $remaining = $this->detailModel
                        ->where('header_id', $headerId)
                        ->countAllResults();

                    if ($remaining > 0) {
                        throw new \RuntimeException(
                            'Detail header lama gagal dibersihkan.'
                        );
                    }
                }

                if (!$this->headerModel->update($headerId, [
                    'currency_id'     => (int) $payload['currency_id'],
                    'remarks'         => $payload['remarks']
                        ?? $header['remarks']
                        ?? null,
                    'import_source'   => $payload['original_filename'],
                    'import_batch_id' => (int) $batchId,
                ])) {
                    throw new \RuntimeException(
                        'Express Rate Header gagal diperbarui.'
                    );
                }
            }

            $inserted = 0;

            foreach (array_chunk($payload['rows'], 400) as $chunk) {
                $batchRows = [];

                foreach ($chunk as $row) {
                    $batchRows[] = [
                        'header_id'     => $headerId,
                        'shipment_type' => $row['shipment_type'],
                        'zone'          => (int) $row['zone'],
                        'weight'        => (float) $row['weight'],
                        'price'         => (float) $row['price'],
                        'created_at'    => date('Y-m-d H:i:s'),
                        'updated_at'    => date('Y-m-d H:i:s'),
                    ];
                }

                $result = $this->db
                    ->table('express_rate_details')
                    ->insertBatch($batchRows);

                if ($result === false) {
                    throw new \RuntimeException(
                        'Insert batch Express Rate Detail gagal.'
                    );
                }

                $inserted += count($batchRows);
            }

            if (!$this->batchModel->update((int) $batchId, [
                'header_id'     => $headerId,
                'imported_rows' => $inserted,
                'status'        => 'COMPLETED',
                'imported_at'   => date('Y-m-d H:i:s'),
            ])) {
                throw new \RuntimeException(
                    'Audit batch import gagal diselesaikan.'
                );
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi import Express Rate gagal.'
                );
            }

            $this->db->transCommit();
            @unlink($this->previewPath($token));

            return [
                'header_id'     => $headerId,
                'imported_rows' => $inserted,
                'batch_id'      => (int) $batchId,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    private function validateMetadata(
        int $vendorId,
        array $input
    ): array {
        $vendor = $this->vendorModel->find($vendorId);

        if (!$vendor) {
            throw new \RuntimeException(
                'Vendor tidak ditemukan.'
            );
        }

        if ((int) ($vendor['status'] ?? 0) !== 1) {
            throw new \RuntimeException(
                'Vendor harus ACTIVE sebelum import tarif.'
            );
        }

        $serviceId  = (int) ($input['service_id'] ?? 0);
        $currencyId = (int) ($input['currency_id'] ?? 0);
        $service = $this->db
            ->table('services')
            ->where('id', $serviceId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (
            !$service
            || strtoupper(
                (string) ($service['pricing_engine'] ?? '')
            ) !== 'EXPRESS'
        ) {
            throw new \RuntimeException(
                'Pilih Express Service yang aktif.'
            );
        }

        $currency = $this->db
            ->table('currencies')
            ->where('id', $currencyId)
            ->get()
            ->getRowArray();

        if (!$currency) {
            throw new \RuntimeException(
                'Pilih currency tarif.'
            );
        }

        $effectiveDate = trim(
            (string) ($input['effective_date'] ?? '')
        );
        $date = \DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $effectiveDate
        );

        if (
            !$date
            || $date->format('Y-m-d') !== $effectiveDate
        ) {
            throw new \RuntimeException(
                'Effective Date tidak valid.'
            );
        }

        $importMode = strtoupper(
            trim((string) ($input['import_mode'] ?? 'REPLACE'))
        );

        if (!in_array($importMode, ['REPLACE', 'APPEND'], true)) {
            throw new \RuntimeException(
                'Import Mode tidak valid.'
            );
        }

        $remarks = trim((string) ($input['remarks'] ?? ''));

        return [
            'service_id'     => $serviceId,
            'currency_id'    => $currencyId,
            'effective_date' => $effectiveDate,
            'import_mode'    => $importMode,
            'remarks'        => $remarks !== '' ? $remarks : null,
        ];
    }

    private function findExistingConflicts(
        int $headerId,
        array $rows
    ): array {
        $existing = $this->detailModel
            ->select([
                'shipment_type',
                'zone',
                'weight',
            ])
            ->where('header_id', $headerId)
            ->findAll();

        $keys = [];

        foreach ($existing as $row) {
            $keys[$this->detailKey($row)] = true;
        }

        $conflicts = [];

        foreach ($rows as $row) {
            $key = $this->detailKey($row);

            if (isset($keys[$key])) {
                $conflicts[] = [
                    'shipment_type' => $row['shipment_type'],
                    'zone'          => (int) $row['zone'],
                    'weight'        => (float) $row['weight'],
                    'source_row'    => (int) ($row['source_row'] ?? 0),
                ];
            }
        }

        return $conflicts;
    }

    private function detailKey(array $row): string
    {
        return sprintf(
            '%s|%d|%.2f',
            strtoupper((string) $row['shipment_type']),
            (int) $row['zone'],
            round((float) $row['weight'], 2)
        );
    }

    private function lockRow(string $table, int $id): array
    {
        $row = $this->db
            ->query(
                'SELECT * FROM `' . str_replace('`', '``', $table) .
                '` WHERE id = ? FOR UPDATE',
                [$id]
            )
            ->getRowArray();

        if (!$row) {
            throw new \RuntimeException(
                ucfirst($table) . ' tidak ditemukan.'
            );
        }

        return $row;
    }

    private function lockHeader(
        int $vendorId,
        int $serviceId,
        string $effectiveDate
    ): ?array {
        return $this->db
            ->query(
                'SELECT * FROM express_rate_headers ' .
                'WHERE vendor_id = ? AND service_id = ? ' .
                'AND effective_date = ? LIMIT 1 FOR UPDATE',
                [$vendorId, $serviceId, $effectiveDate]
            )
            ->getRowArray() ?: null;
    }

    private function writePreview(
        string $token,
        array $payload
    ): void {
        $directory = $this->previewDirectory();

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new \RuntimeException(
                'Folder preview import tidak dapat dibuat.'
            );
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        if (
            file_put_contents(
                $this->previewPath($token),
                $json,
                LOCK_EX
            ) === false
        ) {
            throw new \RuntimeException(
                'Preview import gagal disimpan.'
            );
        }
    }

    private function previewDirectory(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'express_rate_imports';
    }

    private function previewPath(string $token): string
    {
        return $this->previewDirectory()
            . DIRECTORY_SEPARATOR
            . $token
            . '.json';
    }
}
