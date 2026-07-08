<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomersAddCompanyId extends Migration
{
    public function up()
{
    $this->forge->addColumn('customers', [

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
        'customers',
        'company_id'
    );
}
}
