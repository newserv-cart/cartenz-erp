<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentProofOfDeliveryTable extends Migration
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
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
            ],

            'delivered_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],

            'delivered_to' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],

            'delivery_relation' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],

            'delivery_notes' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],

            'pod_photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],

            'pod_signature_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'recorded_by' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
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
        $this->forge->addKey('shipment_id', false, true, 'uq_pod_shipment');
        $this->forge->addKey('recorded_by', false, false, 'idx_pod_recorded_by');
        $this->forge->addKey('delivered_at', false, false, 'idx_pod_delivered_at');

        $this->forge->createTable('shipment_proof_of_delivery');
    }

    public function down()
    {
        //
    }
}
