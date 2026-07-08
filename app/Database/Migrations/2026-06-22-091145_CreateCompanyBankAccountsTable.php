<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyBankAccountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true
            ],

            'company_id' => [
                'type' => 'BIGINT',
                'unsigned' => true
            ],

            'bank_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],

            'account_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200
            ],

            'account_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100
            ],

            'swift_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true
            ],

            'currency' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'IDR'
            ],

            'is_default' => [
                'type' => 'TINYINT',
                'default' => 0
            ],

            'status' => [
                'type' => 'TINYINT',
                'default' => 1
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('company_bank_accounts');
    }

    public function down()
    {
        $this->forge->dropTable('company_bank_accounts');
    }
}