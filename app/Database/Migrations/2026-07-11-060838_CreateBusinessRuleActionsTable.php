<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessRuleActionsTable extends Migration
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

            'rule_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],

            // ✅ Diubah jadi BIGINT UNSIGNED sesuai charge_types.id
            'charge_type_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],

            'action_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'ADD_CHARGE',
                    'ADD_DISCOUNT',
                    'ADD_TAX',
                    'SET_PRICE',
                    'SET_VENDOR_COST',
                    'ADD_COMMISSION',
                ],
                'default' => 'ADD_CHARGE',
            ],

            'charge_side' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'CUSTOMER',
                    'VENDOR',
                    'BOTH',
                ],
                'default' => 'CUSTOMER',
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
                'default' => 'FLAT',
            ],

            'action_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'default'    => 0,
            ],

            'base_field' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],

            'minimum_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],

            'maximum_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],

            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],

            'remarks' => [
                'type' => 'TEXT',
                'null' => true,
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
        $this->forge->addKey('rule_id');
        $this->forge->addKey('charge_type_id');
        $this->forge->addKey('action_type');

        // ✅ Foreign key sesuai aturan
        $this->forge->addForeignKey(
            'rule_id',
            'business_rules',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'charge_type_id',
            'charge_types',
            'id',
            'CASCADE',
            'SET NULL'
        );

        $this->forge->createTable('business_rule_actions', true);
    }

    public function down()
    {
        $this->forge->dropTable('business_rule_actions', true);
    }
}