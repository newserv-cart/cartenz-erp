<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'invoice_no' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],

            'customer_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'invoice_date' => [
                'type' => 'DATE',
            ],

            'due_date' => [
                'type' => 'DATE',
            ],

            'total_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
            ],

            'paid_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
            ],

            'balance_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
            ],

            'invoice_status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'UNPAID',
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

        $this->forge->createTable('invoices');
    }

    public function down()
    {
        $this->forge->dropTable('invoices');
    }
}