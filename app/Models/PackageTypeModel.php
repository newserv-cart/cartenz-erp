<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageTypeModel extends Model
{
    protected $table = 'package_types';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'package_type_name',
        'status'
    ];
}