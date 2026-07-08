<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoGreenZonesTable extends Migration{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'zone_code'     => ['type' => 'VARCHAR', 'constraint' => 10, 'unique' => true],
            'zone_name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'description'   => ['type' => 'TEXT', 'null' => true],
            'status'        => ['type' => 'TINYINT','constraint' => 1,'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('gogreen_zones', true);
    }

    public function down()
    {
        $this->forge->dropTable('gogreen_zones', true);
    }
}