<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWeightRangesTable extends Migration
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

            'service_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            'transport_mode_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 1, // By Air
            ],

            'weight_from' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],

            'weight_to' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],

            'status' => [
                'type'    => 'TINYINT',
                'default' => 1,
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
        $this->forge->addKey('service_id');
        $this->forge->addKey('transport_mode_id');

        $this->forge->createTable('weight_ranges');
    }

    public function down()
    {
        $this->forge->dropTable('weight_ranges');
    }
}