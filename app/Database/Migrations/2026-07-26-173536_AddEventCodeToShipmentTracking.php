<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEventCodeToShipmentTracking extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('event_code', 'shipment_tracking')) {
            $this->forge->addColumn('shipment_tracking', [
                'event_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down()
    {
        //
    }
}
