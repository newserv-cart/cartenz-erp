<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'customer_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'receiver_address_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'agent_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'vendor_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'country_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'service_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'transport_mode_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'delivery_type_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'package_type_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'total_colly' => [
                'type' => 'INT',
                'default' => 1,
            ],

            'actual_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'volume_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'chargeable_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
            ],

            'selling_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'vendor_cost' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'total_charge' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'profit_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            'shipment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'DRAFT',
            ],

            'tracking_no_vendor' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
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

        $this->forge->addKey('awb_no');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('receiver_address_id');
        $this->forge->addKey('agent_id');
        $this->forge->addKey('vendor_id');

        $this->forge->createTable('shipments');
    }

    public function down()
    {
        $this->forge->dropTable('shipments');
    }
}