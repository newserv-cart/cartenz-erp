<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShipmentsAddShipmentMode extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shipments', [

            'shipment_mode' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'MULTI_COLLY',
                'after'      => 'awb_no',
            ],

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('shipments', 'shipment_mode');
    }
}