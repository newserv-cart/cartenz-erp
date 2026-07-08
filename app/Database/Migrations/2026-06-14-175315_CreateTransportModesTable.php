<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransportModesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'mode_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],

            'mode_name' => [
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

        $this->forge->createTable('transport_modes');
    }

    public function down()
    {
        $this->forge->dropTable('transport_modes');
    }
}