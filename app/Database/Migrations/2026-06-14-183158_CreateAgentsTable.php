<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'agent_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],

            'agent_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],

            'agent_type_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'branch_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'parent_agent_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'handover_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'HEAD_OFFICE',
            ],

            'handover_agent_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'white_label_enabled' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],

            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'tracking_domain' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'status' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('agent_code');

        $this->forge->addKey('agent_type_id');

        $this->forge->createTable('agents');
    }

    public function down()
    {
        $this->forge->dropTable('agents');
    }
}