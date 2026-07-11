<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChargeTypesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'charge_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'charge_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'charge_category' => [
                'type'       => 'ENUM',
                'constraint' => ['CUSTOMER', 'VENDOR', 'BOTH'],
                'default'    => 'CUSTOMER',
            ],

            'calculation_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'FLAT',
                    'PER_KG',
                    'PERCENTAGE',
                    'PER_COLLY',
                    'PER_ITEM',
                    'PER_SHIPMENT',
                    'MANUAL',
                ],
                'default' => 'MANUAL',
            ],

            'default_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],

            'currency_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'IDR',
            ],

            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'is_required' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('charge_code');
        $this->forge->addKey('charge_category');
        $this->forge->addKey('calculation_type');
        $this->forge->addKey('is_active');

        $this->forge->createTable('charge_types', true);
    }

    public function down()
    {
        $this->forge->dropTable('charge_types', true);
    }
}