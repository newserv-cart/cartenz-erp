<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePickupRequestsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'shipment_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],

            'pickup_date' => [
                'type' => 'DATE',
            ],

            'pickup_time' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'pickup_address' => [
                'type' => 'TEXT',
            ],

            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],

            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],

            'pickup_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'REQUESTED',
            ],

            'driver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],

            'notes' => [
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

        $this->forge->createTable('pickup_requests');
    }

    public function down()
    {
        $this->forge->dropTable('pickup_requests');
    }
}