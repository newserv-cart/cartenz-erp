<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFuelRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true
            ],

            'service_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true
            ],

            'customer_type' => [
                'type' => 'ENUM',
                'constraint' => ['FIXED','PERCENT'],
                'default' => 'PERCENT'
            ],

            'customer_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],

            'vendor_type' => [
                'type' => 'ENUM',
                'constraint' => ['FIXED','PERCENT'],
                'default' => 'PERCENT'
            ],

            'vendor_value' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],

            'effective_from' => [
                'type' => 'DATE',
                'null' => true
            ],

            'effective_to' => [
                'type' => 'DATE',
                'null' => true
            ],

            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]

        ]);

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey(
            'service_id',
            'services',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('fuel_rules');
    }

    public function down()
    {
        $this->forge->dropTable('fuel_rules', true);
    }
}