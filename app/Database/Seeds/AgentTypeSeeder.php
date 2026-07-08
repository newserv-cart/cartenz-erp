<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AgentTypeSeeder extends Seeder
{
    public function run()
    {
        $data = [

            [
                'agent_type_code' => 'A',
                'agent_type_name' => 'Agent Type A'
            ],

            [
                'agent_type_code' => 'B',
                'agent_type_name' => 'Agent Type B'
            ],

            [
                'agent_type_code' => 'C',
                'agent_type_name' => 'Agent Type C'
            ],

            [
                'agent_type_code' => 'WHITE_LABEL',
                'agent_type_name' => 'White Label Partner'
            ],

        ];

        $this->db->table('agent_types')->insertBatch($data);
    }
}