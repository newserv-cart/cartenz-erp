<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class GoGreenZoneSeeder extends Seeder
{
    public function run()
    {
        $data=[

            [

                'zone_code'=>'RRC',

                'zone_name'=>'RRC / Hong Kong'

            ],

            [

                'zone_code'=>'OCE',

                'zone_name'=>'Oceania'

            ],

            [

                'zone_code'=>'EUR',

                'zone_name'=>'Europe'

            ],

            [

                'zone_code'=>'AMR',

                'zone_name'=>'America'

            ],

            [

                'zone_code'=>'MEA',

                'zone_name'=>'Middle East & North Africa'

            ],

            [

                'zone_code'=>'ROW',

                'zone_name'=>'Rest Of World'

            ]

        ];

        $this->db->table('gogreen_zones')->insertBatch($data);
    }
}