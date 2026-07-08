<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShipmentsAddCompanyId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('shipments', [

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
            'shipments',
            'company_id'
        );
    }
}