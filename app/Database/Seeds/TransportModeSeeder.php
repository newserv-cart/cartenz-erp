<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransportModeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'mode_code' => 'AIR',
                'mode_name' => 'Air Freight',
            ],

            [
                'mode_code' => 'SEA',
                'mode_name' => 'Sea Freight',
            ],

            [
                'mode_code' => 'LAND',
                'mode_name' => 'Land Transport',
            ],

        ];

        $this->db->table('transport_modes')->insertBatch($data);
    }
}