<?php

namespace App\Database\Seeders;

use CodeIgniter\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [

                'department_code'=>'CS',

                'department_name'=>'Customer Service'

            ],

            [

                'department_code'=>'FIN',

                'department_name'=>'Finance'

            ],

            [

                'department_code'=>'WH',

                'department_name'=>'Warehouse'

            ],

            [

                'department_code'=>'CR',

                'department_name'=>'Courier'

            ],

            [

                'department_code'=>'MKT',

                'department_name'=>'Marketing'

            ],

            [

                'department_code'=>'SALES',

                'department_name'=>'Sales'

            ],

            [

                'department_code'=>'IT',

                'department_name'=>'IT'

            ],

            [

                'department_code'=>'MGT',

                'department_name'=>'Management'

            ]

        ];

        $this->db
            ->table('departments')
            ->insertBatch($data);

    }
}