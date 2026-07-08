<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table = 'companies';

    protected $primaryKey = 'id';

    protected $allowedFields = [

        'company_code',
        'company_name',
        'legal_name',

        'address',
        'city',
        'country',

        'phone',
        'email',
        'website',

        'tax_name',
        'tax_number',
        'vat_number',

        'invoice_notes',

        'logo',
        'status'
    ];

    protected $useTimestamps = true;
}