<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChargeTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'charge_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],

            // FIXED | PER_KG | PERCENTAGE
            'calculation_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'FIXED',
            ],

            // Tampil otomatis oleh sistem
            'is_system_charge' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            // Bisa di-hide dari agent/customer
            'is_hidden' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],

            // Bisa diinput manual operator
            'allow_manual_input' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('charge_code');

        $this->forge->createTable('charge_types');
    }

    public function down()
    {
        $this->forge->dropTable('charge_types');
    }
}