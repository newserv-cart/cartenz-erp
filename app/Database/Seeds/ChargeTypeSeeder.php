<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ChargeTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'charge_code' => 'SENSITIVE',
                'charge_name' => 'Sensitive Item',
                'calculation_type' => 'PER_KG',
            ],

            [
                'charge_code' => 'ELECTRONIC',
                'charge_name' => 'Electronic Fee',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'OVERSIZE',
                'charge_name' => 'Oversize Charge',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'PACKING',
                'charge_name' => 'Packing Fee',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'WOODEN_PACKING',
                'charge_name' => 'Wooden Packing',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'FUMIGATION',
                'charge_name' => 'Fumigation',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'INSURANCE',
                'charge_name' => 'Insurance',
                'calculation_type' => 'PERCENTAGE',
            ],

            [
                'charge_code' => 'HANDLING',
                'charge_name' => 'Handling Fee',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'STORAGE',
                'charge_name' => 'Storage Fee',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'REMOTE_AREA',
                'charge_name' => 'Remote Area Charge',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'FUEL_SURCHARGE',
                'charge_name' => 'Fuel Surcharge',
                'calculation_type' => 'PERCENTAGE',
            ],

            [
                'charge_code' => 'GO_GREEN',
                'charge_name' => 'Go Green Fee',
                'calculation_type' => 'PER_KG',
            ],

            [
                'charge_code' => 'CUSTOMS',
                'charge_name' => 'Customs Clearance',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'DOCUMENTATION',
                'charge_name' => 'Documentation Fee',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'AIRPORT_HANDLING',
                'charge_name' => 'Airport Handling',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'PORT_HANDLING',
                'charge_name' => 'Port Handling',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'EXTRA_CHARGE',
                'charge_name' => 'Extra Charge',
                'calculation_type' => 'FIXED',
            ],

            [
                'charge_code' => 'OTHER',
                'charge_name' => 'Other Charge',
                'calculation_type' => 'FIXED',
            ],

        ];

        $this->db->table('charge_types')->insertBatch($data);
    }
}