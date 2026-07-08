<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
        'id' => [
            'type'           => 'BIGINT',
            'unsigned'       => true,
            'auto_increment' => true,
        ],

        'customer_code' => [
            'type'       => 'VARCHAR',
            'constraint' => 30,
        ],

        'customer_name' => [
            'type'       => 'VARCHAR',
            'constraint' => 200,
        ],

        'company_name' => [
            'type'       => 'VARCHAR',
            'constraint' => 200,
            'null'       => true,
        ],

        'agent_id' => [
            'type'     => 'BIGINT',
            'unsigned' => true,
            'null'     => true,
        ],

        'customer_category' => [
            'type'       => 'VARCHAR',
            'constraint' => 30,
            'default'    => 'PERSONAL',
        ],

        'customer_type' => [
            'type'       => 'VARCHAR',
            'constraint' => 20,
            'default'    => 'CASH',
        ],

        'phone' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
            'null'       => true,
        ],

        'email' => [
            'type'       => 'VARCHAR',
            'constraint' => 150,
            'null'       => true,
        ],

        'tax_number' => [
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => true,
        ],

        'identity_number' => [
        'type' => 'VARCHAR',
        'constraint' => 100,
        'null' => true,
        ],



        'address' => [
            'type' => 'TEXT',
            'null' => true,
        ],

        'credit_limit' => [
            'type'       => 'DECIMAL',
            'constraint' => '18,2',
            'default'    => 0,
        ],

        'deposit_balance' => [
            'type'       => 'DECIMAL',
            'constraint' => '18,2',
            'default'    => 0,
        ],

        'account_locked' => [
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
    $this->forge->addKey('customer_code');
    $this->forge->addKey('agent_id');

    $this->forge->createTable('customers');
}

public function down()
{
    $this->forge->dropTable('customers');
}
}