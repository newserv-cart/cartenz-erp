<?php

namespace App\Models;

use CodeIgniter\Model;

class TransportModeModel extends Model
{
    protected $table = 'transport_modes';

    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'mode_code',
        'mode_name',
        'status'
    ];

    protected $useTimestamps = true;
}