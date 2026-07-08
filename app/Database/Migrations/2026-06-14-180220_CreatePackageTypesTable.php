<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePackageTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'package_type_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],

            'package_type_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],

            'status' => [
                'type' => 'TINYINT',
                'default' => 1,
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('package_types');
    }

    public function down()
    {
        $this->forge->dropTable('package_types');
    }
}