<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoiceWeightToShipments extends Migration
{
    public function up()
    {
        if (
            $this->db->fieldExists(
                'invoice_weight',
                'shipments'
            )
        ) {
            return;
        }

        $this->forge->addColumn('shipments', [
            'invoice_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'chargeable_weight',
            ],
        ]);
    }

    public function down()
    {
        if (
            $this->db->fieldExists(
                'invoice_weight',
                'shipments'
            )
        ) {
            $this->forge->dropColumn(
                'shipments',
                'invoice_weight'
            );
        }
    }
}
