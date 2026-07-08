<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManifestsTable extends Migration
{
    public function up()
    {
    $this->forge->addField([

        'id' => [
            'type'           => 'BIGINT',
            'constraint'     => 20,
            'unsigned'       => true,
            'auto_increment' => true,
        ],

        'manifest_no' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
        ],

        'origin_branch_id' => [
            'type'       => 'BIGINT',
            'constraint' => 20,
            'unsigned'   => true,
        ],

        'destination_branch_id' => [
            'type'       => 'BIGINT',
            'constraint' => 20,
            'unsigned'   => true,
        ],

        'manifest_date' => [
            'type' => 'DATE',
        ],

        'total_shipment' => [
            'type'       => 'INT',
            'constraint' => 11,
            'default'    => 0,
        ],

        'total_weight' => [
            'type'       => 'DECIMAL',
            'constraint' => '12,2',
            'default'    => 0,
        ],

        'status' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'default'    => 'OPEN',
        ],

        'remarks' => [
            'type' => 'TEXT',
            'null' => true,
        ],

        'created_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],

        'updated_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],

    ]);

    $this->forge->addKey('id', true);

    $this->forge->addKey('manifest_no');

    $this->forge->addKey('origin_branch_id');

    $this->forge->addKey('destination_branch_id');

    $this->forge->createTable('manifests');
}

    public function down()
    {
         $this->forge->dropTable('manifests');
    }
}
