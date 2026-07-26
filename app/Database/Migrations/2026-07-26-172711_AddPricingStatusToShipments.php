<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingStatusToShipments extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('pricing_status', 'shipments')) {
            $this->forge->addColumn('shipments', [
                'pricing_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => false,
                    'default'    => 'PENDING_VERIFICATION',
                ],
            ]);
        }
    }

    public function down()
    {
        //
    }
}
