<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCustomerAddressesTable extends Migration
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

            'address_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],

            'company_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
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

            'address' => [
                'type' => 'TEXT',
            ],

            'postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],

            'country_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'is_default' => [
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
        $this->forge->addKey('country_id');

        $this->forge->createTable('customer_addresses');
    }

    public function down()
    {
        $this->forge->dropTable('customer_addresses');
    }
}