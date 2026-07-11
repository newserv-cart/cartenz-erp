<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessRuleConditionsTable extends Migration
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

            'field_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],

            'operator' => [
                'type'       => 'ENUM',
                'constraint' => [
                    '=',
                    '!=',
                    '>',
                    '>=',
                    '<',
                    '<=',
                    'IN',
                    'NOT_IN',
                    'BETWEEN',
                    'CONTAINS',
                    'IS_NULL',
                    'IS_NOT_NULL',
                ],
                'default' => '=',
            ],

            'compare_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'compare_value_2' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'logical_operator' => [
                'type'       => 'ENUM',
                'constraint' => ['AND', 'OR'],
                'default'    => 'AND',
            ],

            'condition_group' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],

            'display_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addKey('field_name');

        $this->forge->addForeignKey(
            'rule_id',
            'business_rules',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('business_rule_conditions', true);
    }

    public function down()
    {
        $this->forge->dropTable('business_rule_conditions', true);
    }
}