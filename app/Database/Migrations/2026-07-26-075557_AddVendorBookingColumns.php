<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendorBookingColumns extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('vendor_awb', 'shipments')) {
            $fields['vendor_awb'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'vendor_manifest_id',
            ];
        }

        if (!$this->db->fieldExists('vendor_booking_reference', 'shipments')) {
            $fields['vendor_booking_reference'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'vendor_awb',
            ];
        }

        if (!$this->db->fieldExists('vendor_service_code', 'shipments')) {
            $fields['vendor_service_code'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'vendor_booking_reference',
            ];
        }

        if (!$this->db->fieldExists('vendor_booking_status', 'shipments')) {
            $fields['vendor_booking_status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'vendor_service_code',
            ];
        }

        if (!$this->db->fieldExists('vendor_booking_mode', 'shipments')) {
            $fields['vendor_booking_mode'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'vendor_booking_status',
            ];
        }

        if (!$this->db->fieldExists('vendor_booking_at', 'shipments')) {
            $fields['vendor_booking_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_booking_mode',
            ];
        }

        if (!$this->db->fieldExists('vendor_booking_by', 'shipments')) {
            $fields['vendor_booking_by'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_booking_at',
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
