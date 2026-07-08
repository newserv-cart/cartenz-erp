<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'currency_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],

            'currency_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],

            'symbol' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],

            'status' => [
                'type' => 'TINYINT',
                'default' => 1,
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('currencies');
    }

    public function down()
    {
        $this->forge->dropTable('currencies');
    }
}