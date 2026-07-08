<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentChargesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'shipment_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'charge_type_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],

            'qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 1,
            ],

            'unit_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'is_vendor_charge' => [
                'type'    => 'TINYINT',
                'default' => 0,
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
        $this->forge->addKey('charge_type_id');

        $this->forge->createTable('shipment_charges');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_charges');
    }
}