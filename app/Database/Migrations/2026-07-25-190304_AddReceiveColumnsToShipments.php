<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReceiveColumnsToShipments extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('received_by_user_id', 'shipments')) {
            $fields['received_by_user_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('package_condition', 'shipments')) {
            $fields['package_condition'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('receiving_remarks', 'shipments')) {
            $fields['receiving_remarks'] = [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('shipments', $fields);
        }
    }

    public function down()
    {
        //
    }
}
