<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentPackagesTable extends Migration
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

            'package_no' => [
                'type'    => 'INT',
                'default' => 1,
            ],

            'package_barcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'vendor_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'vendor_awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'vendor_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'vendor_label_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'is_submitted_to_vendor' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            'package_type_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'actual_weight' => [
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

            'volume_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
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
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('vendor_awb_no');

        $this->forge->createTable('shipment_packages');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_packages');
    }
}