<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterRegularTariffsV3 extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('agent_a_price', 'regular_tariffs')) {
            $fields['agent_a_price'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ];
        }

        if (!$this->db->fieldExists('agent_b_price', 'regular_tariffs')) {
            $fields['agent_b_price'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ];
        }

        if (!$this->db->fieldExists('agent_c_price', 'regular_tariffs')) {
            $fields['agent_c_price'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ];
        }

        if (!$this->db->fieldExists('publish_price', 'regular_tariffs')) {
            $fields['publish_price'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ];
        }

        if (!$this->db->fieldExists('vendor_cost', 'regular_tariffs')) {
            $fields['vendor_cost'] = [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('regular_tariffs', $fields);
        }
    }

    public function down()
    {
        //
    }
}