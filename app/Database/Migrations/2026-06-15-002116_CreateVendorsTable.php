<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'vendor_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],

            'vendor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],

            'vendor_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'INTERNATIONAL_CARGO',
            ],

            'country_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'contact_person' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
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

            'website' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],

            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'api_available' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            'tracking_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addKey('vendor_code');
        $this->forge->addKey('vendor_type');
        $this->forge->addKey('country_id');

        $this->forge->createTable('vendors');
    }

    public function down()
    {
        $this->forge->dropTable('vendors');
    }
}