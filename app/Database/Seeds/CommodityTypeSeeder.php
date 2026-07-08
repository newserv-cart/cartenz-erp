<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CommodityTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            ['commodity_name'=>'Document'],
            ['commodity_name'=>'General Cargo'],
            ['commodity_name'=>'Sensitive'],
            ['commodity_name'=>'Electronic'],
            ['commodity_name'=>'Battery'],
            ['commodity_name'=>'Liquid'],
            ['commodity_name'=>'Food'],
            ['commodity_name'=>'Medicine'],
            ['commodity_name'=>'Cosmetic'],

        ];

        $this->db->table('commodity_types')->insertBatch($data);
    }
}