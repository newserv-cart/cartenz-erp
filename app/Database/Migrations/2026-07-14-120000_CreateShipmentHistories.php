<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentHistories extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('shipment_histories')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shipment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'event_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'event_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'changed_fields' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('shipment_id');
        $this->forge->addKey('event_type');
        $this->forge->addKey('created_at');

        $this->forge->createTable(
            'shipment_histories',
            true
        );
    }

    public function down()
    {
        $this->forge->dropTable(
            'shipment_histories',
            true
        );
    }
}
