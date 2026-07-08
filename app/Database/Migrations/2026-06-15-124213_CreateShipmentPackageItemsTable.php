<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentPackageItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'shipment_package_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'item_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],

            'hs_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
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

            'country_of_origin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'brand_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
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

        $this->forge->addKey('shipment_package_id');
        $this->forge->addKey('hs_code');
        $this->forge->addKey('currency_id');

        $this->forge->createTable('shipment_package_items');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_package_items');
    }
}