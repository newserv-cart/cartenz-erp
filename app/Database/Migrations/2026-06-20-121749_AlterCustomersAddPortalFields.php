<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomersAddPortalFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [

            
            'approval_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'PENDING',
                'after'      => 'status',
            ],

            'approved_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'approval_status',
            ],

            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by',
            ],

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('customers', [
            'user_id',
            'approval_status',
            'approved_by',
            'approved_at'
        ]);
    }
}