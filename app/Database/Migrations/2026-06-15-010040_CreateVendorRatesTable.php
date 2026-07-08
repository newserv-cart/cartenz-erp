<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendorRatesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'vendor_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'country_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'service_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'transport_mode_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'delivery_type_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'null' => true,
            ],

            'weight_from' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],

            'weight_to' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],

            'cost_per_kg' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
            ],

            'minimum_charge' => [
                'type' => 'DECIMAL',
                'constraint' => '18,2',
                'default' => 0,
            ],

            'effective_from' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'effective_until' => [
                'type' => 'DATE',
                'null' => true,
            ],

            'status' => [
                'type' => 'TINYINT',
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

        $this->forge->addKey('vendor_id');
        $this->forge->addKey('country_id');
        $this->forge->addKey('service_id');
        $this->forge->addKey('transport_mode_id');

        $this->forge->createTable('vendor_rates');
    }

    public function down()
    {
        $this->forge->dropTable('vendor_rates');
    }
}