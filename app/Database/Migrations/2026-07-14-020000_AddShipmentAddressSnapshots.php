<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShipmentAddressSnapshots extends Migration
{
    public function up()
    {
        $fields = [
            'sender_address_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'sender_country_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'sender_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'sender_company' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'sender_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'sender_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'sender_identity_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sender_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sender_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sender_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sender_postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'receiver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'receiver_company' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'receiver_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'receiver_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'receiver_id_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'receiver_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'receiver_city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'receiver_state' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'receiver_postal_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
        ];

        $missing = [];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $missing[$name] = $definition;
            }
        }

        if ($missing !== []) {
            $this->forge->addColumn('shipments', $missing);
        }
    }

    public function down()
    {
        $columns = [
            'sender_address_id',
            'sender_country_id',
            'sender_name',
            'sender_company',
            'sender_phone',
            'sender_email',
            'sender_identity_number',
            'sender_address',
            'sender_city',
            'sender_state',
            'sender_postal_code',
            'receiver_name',
            'receiver_company',
            'receiver_phone',
            'receiver_email',
            'receiver_id_number',
            'receiver_address',
            'receiver_city',
            'receiver_state',
            'receiver_postal_code',
        ];

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'shipments')) {
                $this->forge->dropColumn('shipments', $column);
            }
        }
    }
}
