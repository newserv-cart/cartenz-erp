<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'invoice_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'shipment_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('invoice_id');
        $this->forge->addKey('shipment_id');

        $this->forge->createTable('invoice_items');
    }

    public function down()
    {
        $this->forge->dropTable('invoice_items');
    }
}