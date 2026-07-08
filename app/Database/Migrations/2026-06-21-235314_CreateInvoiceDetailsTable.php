<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceDetailsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'invoice_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'shipment_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
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

        $this->forge->createTable('invoice_details');
    }

    public function down()
    {
        $this->forge->dropTable('invoice_details');
    }
}