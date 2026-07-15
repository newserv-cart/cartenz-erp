<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressRateImportBatchModel extends Model
{
    protected $table            = 'express_rate_import_batches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vendor_id',
        'service_id',
        'currency_id',
        'header_id',
        'effective_date',
        'original_filename',
        'source_format',
        'import_mode',
        'total_rows',
        'imported_rows',
        'status',
        'error_summary',
        'imported_by',
        'imported_at',
        'created_at',
    ];

    protected $useTimestamps = false;
}
