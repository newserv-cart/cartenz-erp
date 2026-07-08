<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterInvoicesAddTaxFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [

            'vat_percent' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0
            ],

            'vat_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0
            ],

            'extra_charge' => [
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
            ['vat_percent', 'vat_amount', 'extra_charge']
        );
    }
}