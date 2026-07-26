<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHandoverColumnsToShipments extends Migration
{
    public function up()
    {
        $fields = [
            'owner_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'entry_channel' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'handover_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'created_by_user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $this->forge->addColumn(
                    'shipments',
                    [$name => $definition]
                );
            }
        }
    }

    public function down()
    {
        //
    }
}
