<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendorManifestColumns extends Migration
{
    public function up()
    {
        $manifestFields = [];

        if (!$this->db->fieldExists('manifest_type', 'manifests')) {
            $manifestFields['manifest_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'BRANCH',
                'null'       => false,
                'after'      => 'manifest_no',
            ];
        }

        if (!$this->db->fieldExists('vendor_id', 'manifests')) {
            $manifestFields['vendor_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'destination_branch_id',
            ];
        }

        if (!$this->db->fieldExists('total_colly', 'manifests')) {
            $manifestFields['total_colly'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'total_shipment',
            ];
        }

        if (!$this->db->fieldExists('total_vendor_cost', 'manifests')) {
            $manifestFields['total_vendor_cost'] = [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
                'null'       => false,
                'after'      => 'total_weight',
            ];
        }

        if (!empty($manifestFields)) {
            $this->forge->addColumn('manifests', $manifestFields);
        }

        $shipmentFields = [];

        if (!$this->db->fieldExists('vendor_manifest_id', 'shipments')) {
            $shipmentFields['vendor_manifest_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_id',
            ];
        }

        if (!$this->db->fieldExists('vendor_handover_at', 'shipments')) {
            $shipmentFields['vendor_handover_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_assigned_by',
            ];
        }

        if (!$this->db->fieldExists('vendor_handover_by', 'shipments')) {
            $shipmentFields['vendor_handover_by'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_handover_at',
            ];
        }

        if (!empty($shipmentFields)) {
            $this->forge->addColumn('shipments', $shipmentFields);
        }
    }

    public function down()
    {
        //
    }
}
