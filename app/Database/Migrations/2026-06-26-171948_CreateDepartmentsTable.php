<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'department_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],

            'department_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['ACTIVE','INACTIVE'],
                'default'    => 'ACTIVE',
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL'

        ]);

        $this->forge->addKey('id', true);

        $this->forge->addUniqueKey('department_code');

        $this->forge->createTable('departments');
    }

    public function down()
    {
        $this->forge->dropTable('departments');
    }
}