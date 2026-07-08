<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpressZonesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],

            'zone_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20
            ],

            'zone_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]

        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->createTable('express_zones');
    }

    public function down()
    {
        $this->forge->dropTable('express_zones', true);
    }
}