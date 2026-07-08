<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomersAddUserId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [

            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'id',
            ],

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn(
            'customers',
            'user_id'
        );
    }
}