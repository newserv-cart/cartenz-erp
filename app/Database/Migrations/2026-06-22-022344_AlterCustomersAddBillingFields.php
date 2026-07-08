<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCustomersAddBillingFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('customers', [

            'billing_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'CASH',
                'after' => 'customer_type'
            ],

            'credit_days' => [
                'type' => 'INT',
                'default' => 0,
                'after' => 'billing_type'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('customers', [
            'billing_type',
            'credit_days'
        ]);
    }
}