<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingStatusModel extends Model
{
    protected $table            = 'tracking_statuses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields = [
        'status_code',
        'status_name',
        'status_color',
        'public_status_code',
        'sort_order',
        'is_active',
        'is_exception',
        'is_terminal',
        'requires_location',
        'requires_notes',
    ];

    // Tabel tracking_statuses saat ini tidak memiliki created_at/updated_at.
    protected $useTimestamps = false;

    public function getActiveStatuses(): array
    {
        return $this
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function getByCode(string $statusCode): ?array
    {
        return $this
            ->where('status_code', strtoupper(trim($statusCode)))
            ->where('is_active', 1)
            ->first();
    }

    public function getPublicStatusCode(string $statusCode): string
    {
        $status = $this->getByCode($statusCode);

        if (!$status) {
            return 'CREATED';
        }

        return strtoupper(
            trim((string) (
                $status['public_status_code']
                ?? $status['status_code']
                ?? 'CREATED'
            ))
        );
    }
}
