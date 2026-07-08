<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentItemsTable extends Migration
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

            'item_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'hs_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],

            'quantity' => [
                'type'    => 'INT',
                'default' => 1,
            ],

            'unit_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'PCS',
            ],

            'unit_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'currency_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'total_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'item_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'length_cm' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'width_cm' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'height_cm' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'is_dangerous_goods' => [
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
        $this->forge->addKey('currency_id');

        $this->forge->createTable('shipment_items');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_items');
    }
}