<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'region_code' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],

            'region_name' => [
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

        $this->forge->createTable('regions');
    }

    public function down()
    {
        $this->forge->dropTable('regions');
    }
}