<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInternationalTransitColumns extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('vendor_departure_at', 'shipments')) {
            $fields['vendor_departure_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_booking_by',
            ];
        }

        if (!$this->db->fieldExists('vendor_departure_by', 'shipments')) {
            $fields['vendor_departure_by'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_departure_at',
            ];
        }

        if (!$this->db->fieldExists('vendor_origin_location', 'shipments')) {
            $fields['vendor_origin_location'] = [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'vendor_departure_by',
            ];
        }

        if (!$this->db->fieldExists('vendor_transport_reference', 'shipments')) {
            $fields['vendor_transport_reference'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'vendor_origin_location',
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
