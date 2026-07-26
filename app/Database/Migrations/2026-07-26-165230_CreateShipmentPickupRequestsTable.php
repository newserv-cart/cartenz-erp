<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentPickupRequestsTable extends Migration
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
                'null'     => false,
            ],

            'customer_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],

            'pickup_address_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'pickup_contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],

            'pickup_contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],

            'requested_pickup_date' => [
                'type' => 'DATE',
                'null' => false,
            ],

            'requested_time_start' => [
                'type' => 'TIME',
                'null' => true,
            ],

            'requested_time_end' => [
                'type' => 'TIME',
                'null' => true,
            ],

            'pickup_remarks' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => true,
            ],

            'pickup_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
                'default'    => 'REQUESTED',
            ],

            'scheduled_pickup_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'scheduled_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'assigned_courier_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'assigned_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'picked_up_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'picked_up_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'cancelled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'cancelled_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'cancellation_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],

            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
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
        $this->forge->addKey('shipment_id', false, true, 'uq_pickup_request_shipment');
        $this->forge->addKey('customer_id', false, false, 'idx_pickup_customer');
        $this->forge->addKey('pickup_status', false, false, 'idx_pickup_status');
        $this->forge->addKey('requested_pickup_date', false, false, 'idx_pickup_date');
        $this->forge->addKey('assigned_courier_user_id', false, false, 'idx_pickup_courier');

        $this->forge->createTable('shipment_pickup_requests');
    }

    public function down()
    {
        //
    }
}
