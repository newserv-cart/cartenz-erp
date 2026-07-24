<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $data = [

            ['region_id' => 1, 'country_code' => 'ID', 'country_name' => 'Indonesia'],
            ['region_id' => 1, 'country_code' => 'SG', 'country_name' => 'Singapore'],
            ['region_id' => 1, 'country_code' => 'MY', 'country_name' => 'Malaysia'],
            ['region_id' => 1, 'country_code' => 'HK', 'country_name' => 'Hong Kong'],
            ['region_id' => 1, 'country_code' => 'TW', 'country_name' => 'Taiwan'],
            ['region_id' => 1, 'country_code' => 'CN', 'country_name' => 'China'],
            ['region_id' => 1, 'country_code' => 'JP', 'country_name' => 'Japan'],
            ['region_id' => 1, 'country_code' => 'BN', 'country_name' => 'Brunei'],
            ['region_id' => 1, 'country_code' => 'PH', 'country_name' => 'Philippines'],
            ['region_id' => 1, 'country_code' => 'TL', 'country_name' => 'Timor Leste'],

            ['region_id' => 2, 'country_code' => 'GB', 'country_name' => 'United Kingdom'],
            ['region_id' => 2, 'country_code' => 'DE', 'country_name' => 'Germany'],

            ['region_id' => 3, 'country_code' => 'US', 'country_name' => 'United States'],
            ['region_id' => 3, 'country_code' => 'CA', 'country_name' => 'Canada'],

            ['region_id' => 4, 'country_code' => 'AE', 'country_name' => 'United Arab Emirates'],
            ['region_id' => 4, 'country_code' => 'SA', 'country_name' => 'Saudi Arabia'],

            ['region_id' => 5, 'country_code' => 'AU', 'country_name' => 'Australia'],

        ];

        $this->db->table('countries')->insertBatch($data);
    }
}