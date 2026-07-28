<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGpsColumnsToShipmentProofOfDelivery extends Migration
{
    public function up()
    {
        $fields = [
            'delivery_latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'delivery_longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,7',
                'null'       => true,
            ],
            'location_accuracy' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'location_captured_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipment_proof_of_delivery')) {
                $this->forge->addColumn(
                    'shipment_proof_of_delivery',
                    [$name => $definition]
                );
            }
        }
    }

    public function down()
    {
        //
    }
}
