<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DeliveryTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'delivery_type_code' => 'DOOR_TO_DOOR',
                'delivery_type_name' => 'Door To Door',
            ],

            [
                'delivery_type_code' => 'DOOR_TO_PORT',
                'delivery_type_name' => 'Door To Port',
            ],

            [
                'delivery_type_code' => 'PORT_TO_PORT',
                'delivery_type_name' => 'Port To Port',
            ],

        ];

        $this->db->table('delivery_types')->insertBatch($data);
    }
}