<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentCostDetailsTable extends Migration
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

            'shipment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],

            // CUSTOMER / VENDOR
            'party' => [
                'type'       => 'ENUM',
                'constraint' => ['CUSTOMER','VENDOR'],
            ],

            // FREIGHT, FUEL, REMOTE, INSURANCE dll
            'component_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'component_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
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

        $this->forge->addKey('shipment_id');

        $this->forge->createTable('shipment_cost_details');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_cost_details');
    }
}