<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBusinessRulesTable extends Migration
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

            'rule_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'rule_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            'rule_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'CHARGE',
                    'DISCOUNT',
                    'TAX',
                    'COMMISSION',
                    'PRICING',
                ],
                'default' => 'CHARGE',
            ],

            'priority' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 100,
            ],

            'stop_processing' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            'valid_from' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'valid_until' => [
                'type' => 'DATE',
                'null' => true,
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
        $this->forge->addUniqueKey('rule_code');
        $this->forge->addKey('rule_type');
        $this->forge->addKey('priority');
        $this->forge->addKey('is_active');

        $this->forge->createTable('business_rules', true);
    }

    public function down()
    {
        $this->forge->dropTable('business_rules', true);
    }
}