<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PackageTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'package_type_code' => 'GENERAL',
                'package_type_name' => 'General Cargo',
            ],

            [
                'package_type_code' => 'SENSITIVE',
                'package_type_name' => 'Sensitive Item',
            ],

            [
                'package_type_code' => 'ELECTRONIC',
                'package_type_name' => 'Electronic',
            ],

            [
                'package_type_code' => 'COSMETIC',
                'package_type_name' => 'Cosmetic',
            ],

            [
                'package_type_code' => 'GARMENT',
                'package_type_name' => 'Garment',
            ],

            [
                'package_type_code' => 'DOCUMENT',
                'package_type_name' => 'Document',
            ],

            [
                'package_type_code' => 'FOOD',
                'package_type_name' => 'Food',
            ]

        ];

        $this->db->table('package_types')->insertBatch($data);
    }
}