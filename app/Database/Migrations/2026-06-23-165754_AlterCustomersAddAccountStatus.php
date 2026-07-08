<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomersAddAccountStatus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [

            'account_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'ACTIVE'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn(
            'customers',
            'account_status'
        );
    }
}