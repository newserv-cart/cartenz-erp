<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'service_code' => 'REGULAR',
                'service_name' => 'Regular Service',
                'description' => 'Regular International Shipping'
            ],

            [
                'service_code' => 'EXPRESS',
                'service_name' => 'Express Service',
                'description' => 'Express International Shipping'
            ],

        ];

        $this->db->table('services')->insertBatch($data);
    }
}