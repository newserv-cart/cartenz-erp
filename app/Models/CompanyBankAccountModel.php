<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyBankAccountModel extends Model
{
    protected $table = 'company_bank_accounts';

    protected $allowedFields = [

        'company_id',
        'bank_name',
        'account_name',
        'account_number',
        'swift_code',
        'currency',
        'is_default',
        'status'
    ];

    protected $useTimestamps = true;
}