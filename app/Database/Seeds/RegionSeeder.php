<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'region_code' => 'ASIA',
                'region_name' => 'Asia',
            ],

            [
                'region_code' => 'EUROPE',
                'region_name' => 'Europe',
            ],

            [
                'region_code' => 'USA_CANADA',
                'region_name' => 'USA & Canada',
            ],

            [
                'region_code' => 'MIDDLE_EAST',
                'region_name' => 'Middle East',
            ],

            [
                'region_code' => 'OCEANIA',
                'region_name' => 'Oceania',
            ],

        ];

        $this->db->table('regions')->insertBatch($data);
    }
}