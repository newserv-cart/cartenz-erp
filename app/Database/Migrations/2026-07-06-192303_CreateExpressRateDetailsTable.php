<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpressRateDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'header_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false
            ],
            'shipment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['DOCUMENT', 'PACKAGE'],
                'null'       => false
            ],
            'zone' => [
                'type'       => 'TINYINT',
                'constraint' => 2,
                'unsigned'   => true,
                'null'       => false
            ],
            'weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '6,2',
                'null'       => false
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => false
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
        $this->forge->addKey('header_id');
        $this->forge->addKey(['zone', 'weight']); // Mempercepat pencarian tarif

        $this->forge->createTable('express_rate_details', true);
    }

    public function down()
    {
        $this->forge->dropTable('express_rate_details', true);
    }
}