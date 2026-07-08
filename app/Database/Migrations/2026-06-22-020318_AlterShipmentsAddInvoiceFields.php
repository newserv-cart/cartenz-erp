<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShipmentsAddInvoiceFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shipments', [

            'invoice_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'profit_amount'
            ],

            'is_invoiced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'invoice_id'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('shipments', [
            'invoice_id',
            'is_invoiced'
        ]);
    }
}