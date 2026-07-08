<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentTrackingTable extends Migration
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

            'tracking_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'tracking_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'tracking_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],

            'tracking_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'event_time' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('shipment_id');
        $this->forge->addKey('tracking_code');

        $this->forge->createTable('shipment_tracking');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_tracking');
    }
}