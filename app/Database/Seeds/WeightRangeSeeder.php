<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class WeightRangeSeeder extends Seeder
{
    public function run()
    {
        $rows = [

            [
                'service_id' => 1,
                'transport_mode_id' => 1,
                'weight_from' => 1,
                'weight_to' => 1,
            ],

            [
                'service_id' => 1,
                'transport_mode_id' => 1,
                'weight_from' => 2,
                'weight_to' => 5,
            ],

            [
                'service_id' => 1,
                'transport_mode_id' => 1,
                'weight_from' => 6,
                'weight_to' => 29,
            ],

            [
                'service_id' => 1,
                'transport_mode_id' => 1,
                'weight_from' => 30,
                'weight_to' => 99,
            ],

            [
                'service_id' => 1,
                'transport_mode_id' => 1,
                'weight_from' => 100,
                'weight_to' => 299,
            ],

        ];

        $this->db->table('weight_ranges')->insertBatch($rows);
    }
}