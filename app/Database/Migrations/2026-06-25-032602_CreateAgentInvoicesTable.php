<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentInvoicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => true,
            ],

            'agent_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],

            'invoice_no' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
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
                'constraint' => 20,
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

        $this->forge->createTable('agent_invoices');
    }

    public function down()
    {
        $this->forge->dropTable('agent_invoices');
    }
}