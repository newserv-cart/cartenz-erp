<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorStatusMappingModel extends Model
{
    protected $table            = 'vendor_status_mappings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'vendor_id',
        'vendor_status_code',
        'internal_status',
        'public_status',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findActiveMapping(
        int $vendorId,
        string $vendorStatusCode
    ): ?array {
        $vendorStatusCode = strtoupper(trim($vendorStatusCode));

        $specific = $this
            ->where('vendor_id', $vendorId)
            ->where('vendor_status_code', $vendorStatusCode)
            ->where('is_active', 1)
            ->first();

        if ($specific) {
            return $specific;
        }

        return $this
            ->where('vendor_id', 0)
            ->where('vendor_status_code', $vendorStatusCode)
            ->where('is_active', 1)
            ->first();
    }
}
