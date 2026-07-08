<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBillingProfilesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'customer_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            // CASH | DEPOSIT | CREDIT
            'billing_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'CASH',
            ],

            // DAILY | WEEKLY | MONTHLY
            'invoice_cycle' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'DAILY',
            ],

            // Untuk customer kredit
            'credit_days' => [
                'type'    => 'INT',
                'default' => 0,
            ],

            // Limit kredit customer
            'credit_limit' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],

            // Lock customer jika overdue
            'auto_lock' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],

            // Hold shipment jika overdue
            'auto_hold_shipment' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],

            // Override khusus
            'allow_over_credit' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            'status' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('billing_type');

        $this->forge->createTable('billing_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('billing_profiles');
    }
}