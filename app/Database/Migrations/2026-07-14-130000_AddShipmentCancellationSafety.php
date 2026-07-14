<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShipmentCancellationSafety extends Migration
{
    public function up()
    {
        $this->addShipmentCancellationColumns();
        $this->createDeletionAuditTable();
        $this->upsertCancelledTrackingStatus();
    }

    public function down()
    {
        if ($this->db->tableExists('shipment_deletion_audits')) {
            $this->forge->dropTable(
                'shipment_deletion_audits',
                true
            );
        }

        $columns = [
            'cancel_reason',
            'cancelled_at',
            'cancelled_by',
            'cancelled_from_status',
        ];

        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'shipments')) {
                $this->forge->dropColumn(
                    'shipments',
                    $column
                );
            }
        }

        if (
            $this->db->tableExists('tracking_statuses')
            && $this->db->tableExists('shipment_tracking')
        ) {
            $used = $this->db
                ->table('shipment_tracking')
                ->where('tracking_status', 'CANCELLED')
                ->countAllResults();

            if ($used === 0) {
                $this->db
                    ->table('tracking_statuses')
                    ->where('status_code', 'CANCELLED')
                    ->delete();
            }
        }
    }

    private function addShipmentCancellationColumns(): void
    {
        $definitions = [
            'cancel_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after'=> 'public_tracking_status',
            ],
            'cancelled_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after'=> 'cancel_reason',
            ],
            'cancelled_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'cancelled_at',
            ],
            'cancelled_from_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'cancelled_by',
            ],
        ];

        foreach ($definitions as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $this->forge->addColumn(
                    'shipments',
                    [$name => $definition]
                );
            }
        }
    }

    private function createDeletionAuditTable(): void
    {
        if ($this->db->tableExists('shipment_deletion_audits')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shipment_id_original' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'customer_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'shipment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'delete_reason' => [
                'type' => 'TEXT',
            ],
            'deleted_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
            ],
            'snapshot_json' => [
                'type' => 'LONGTEXT',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('shipment_id_original');
        $this->forge->addKey('awb_no');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('deleted_at');

        $this->forge->createTable(
            'shipment_deletion_audits',
            true
        );
    }

    private function upsertCancelledTrackingStatus(): void
    {
        if (!$this->db->tableExists('tracking_statuses')) {
            return;
        }

        $fields = $this->db->getFieldNames(
            'tracking_statuses'
        );

        $data = [
            'status_code'  => 'CANCELLED',
            'status_name'  => 'Cancelled',
            'status_color' => 'danger',
            'sort_order'   => 99,

            // Cancellation must use the dedicated lifecycle action.
            'is_active'    => 0,
        ];

        $optional = [
            'public_status_code' => 'CANCELLED',
            'is_exception'       => 1,
            'is_terminal'        => 1,
            'requires_location'  => 0,
            'requires_notes'     => 1,
        ];

        foreach ($optional as $field => $value) {
            if (in_array($field, $fields, true)) {
                $data[$field] = $value;
            }
        }

        $existing = $this->db
            ->table('tracking_statuses')
            ->where('status_code', 'CANCELLED')
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db
                ->table('tracking_statuses')
                ->where('id', $existing['id'])
                ->update($data);

            return;
        }

        $this->db
            ->table('tracking_statuses')
            ->insert($data);
    }
}
