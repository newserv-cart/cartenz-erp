<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpressChargeRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
'country_id' => [
    'type' => 'BIGINT',
    'constraint' => 20,
    'unsigned' => true
],

'service_id' => [
    'type' => 'BIGINT',
    'constraint' => 20,
    'unsigned' => true
],

            // Fuel Surcharge
            'customer_fuel_type'     => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PERCENT'], 'default' => 'PERCENT'],
            'customer_fuel_value'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'vendor_fuel_type'       => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PERCENT'], 'default' => 'PERCENT'],
            'vendor_fuel_value'      => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],

            // Go Green Surcharge
            'customer_gogreen_type'  => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PER_KG'], 'default' => 'PER_KG'],
            'customer_gogreen_value' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'vendor_gogreen_type'    => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PER_KG'], 'default' => 'PER_KG'],
            'vendor_gogreen_value'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],

            // Remote Area Surcharge
            'customer_remote_type'   => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PERCENT'], 'default' => 'FIXED'],
            'customer_remote_value'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],
            'vendor_remote_type'     => ['type' => 'ENUM', 'constraint' => ['FIXED', 'PERCENT'], 'default' => 'FIXED'],
            'vendor_remote_value'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0.00],

            'effective_from'         => ['type' => 'DATE', 'null' => true],
            'effective_to'           => ['type' => 'DATE', 'null' => true],
            'status'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('country_id', 'countries', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('express_charge_rules', true);
    }

    public function down()
    {
        $this->forge->dropTable('express_charge_rules', true);
    }
}