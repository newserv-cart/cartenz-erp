<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ExpressZoneSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'zone_code' => 'Z1',
                'zone_name' => 'Zone 1'
            ],

            [
                'zone_code' => 'Z2',
                'zone_name' => 'Zone 2'
            ],

            [
                'zone_code' => 'Z3',
                'zone_name' => 'Zone 3'
            ],

            [
                'zone_code' => 'Z4',
                'zone_name' => 'Zone 4'
            ],

            [
                'zone_code' => 'Z5',
                'zone_name' => 'Zone 5'
            ],

            [
                'zone_code' => 'Z6',
                'zone_name' => 'Zone 6'
            ],

            [
                'zone_code' => 'Z7',
                'zone_name' => 'Zone 7'
            ],

            [
                'zone_code' => 'Z8',
                'zone_name' => 'Zone 8'
            ]

        ];

        $this->db->table('express_zones')->insertBatch($data);
    }
}