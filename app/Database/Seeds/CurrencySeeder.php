<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        $data = [

            ['currency_code'=>'IDR','currency_name'=>'Indonesian Rupiah','symbol'=>'Rp'],
            ['currency_code'=>'USD','currency_name'=>'US Dollar','symbol'=>'$'],
            ['currency_code'=>'SGD','currency_name'=>'Singapore Dollar','symbol'=>'S$'],
            ['currency_code'=>'MYR','currency_name'=>'Malaysian Ringgit','symbol'=>'RM'],
            ['currency_code'=>'AUD','currency_name'=>'Australian Dollar','symbol'=>'A$'],
            ['currency_code'=>'EUR','currency_name'=>'Euro','symbol'=>'€'],
            ['currency_code'=>'GBP','currency_name'=>'British Pound','symbol'=>'£'],
            ['currency_code'=>'AED','currency_name'=>'UAE Dirham','symbol'=>'AED'],
            ['currency_code'=>'SAR','currency_name'=>'Saudi Riyal','symbol'=>'SAR'],
            ['currency_code'=>'JPY','currency_name'=>'Japanese Yen','symbol'=>'¥'],
            ['currency_code'=>'CNY','currency_name'=>'Chinese Yuan','symbol'=>'¥'],
            ['currency_code'=>'HKD','currency_name'=>'Hong Kong Dollar','symbol'=>'HK$']

        ];

        $this->db->table('currencies')->insertBatch($data);
    }
}