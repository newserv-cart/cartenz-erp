<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterInvoicesAddCompanyId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [

            'company_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
                'after' => 'id'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn(
            'invoices',
            'company_id'
        );
    }
}