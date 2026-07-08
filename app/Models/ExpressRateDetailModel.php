<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpressRateDetailModel extends Model
{
    protected $table         = 'express_rate_details';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object'; // Bisa diganti 'array' sesuai kebutuhan
    protected $useTimestamps = false; // Atur ke true kalau nanti butuh waktu pembuatan/perubahan

    protected $allowedFields = [
        'header_id',
        'shipment_type',
        'zone',
        'weight',
        'price'
    ];

    // Opsional: Tambahkan relasi ke tabel header jika butuh
    protected $with = [];
}