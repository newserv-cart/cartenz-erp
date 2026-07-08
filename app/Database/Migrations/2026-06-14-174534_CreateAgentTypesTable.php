<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'agent_type_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],

            'agent_type_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],

            'status' => [
                'type' => 'TINYINT',
                'default' => 1,
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('agent_types');
    }

    public function down()
    {
        $this->forge->dropTable('agent_types');
    }
}