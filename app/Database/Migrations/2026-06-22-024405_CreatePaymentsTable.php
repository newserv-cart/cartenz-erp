<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'payment_no' => [
                'type' => 'VARCHAR',
                'constraint' => 50
            ],

            'invoice_id' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],

            'payment_date' => [
                'type' => 'DATE'
            ],

            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 30
            ],

            'reference_no' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true
            ],

            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0
            ],

            'remarks' => [
                'type' => 'TEXT',
                'null' => true
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');

        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}