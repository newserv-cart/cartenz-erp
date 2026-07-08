<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterInvoicesAddGrandTotal extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [

            'grand_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn(
            'invoices',
            'grand_total'
        );
    }
}