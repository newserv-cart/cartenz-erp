<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShipmentAcceptanceAndInboundIntakes extends Migration
{
    public function up()
    {
        $shipmentFields = [
            'inbound_intake_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'agent_id',
            ],
            'acceptance_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'inbound_intake_id',
            ],
            'receiving_branch_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'acceptance_method',
            ],
            'receiving_location' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'receiving_branch_id',
            ],
            'received_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'receiving_location',
            ],
            'received_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'received_at',
            ],
        ];

        foreach ($shipmentFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $this->forge->addColumn('shipments', [$name => $definition]);
            }
        }

        if (!$this->db->tableExists('inbound_intakes')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'intake_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                ],
                'acceptance_method' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'AGENT_HANDOVER_NO_AWB',
                ],
                'agent_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'origin_branch_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'receiving_branch_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'receiving_location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                ],
                'arrival_date' => [
                    'type' => 'DATE',
                ],
                'agent_reference_no' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'total_colly' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'declared_weight' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'received_weight' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'intake_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'default'    => 'RECEIVED_UNREGISTERED',
                ],
                'converted_shipment_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'received_by' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'received_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'cancelled_by' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'cancelled_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
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
            $this->forge->addUniqueKey('intake_no');
            $this->forge->addKey('agent_id');
            $this->forge->addKey('intake_status');
            $this->forge->addKey('converted_shipment_id');
            $this->forge->createTable('inbound_intakes', true);
        }

        if (!$this->db->tableExists('inbound_intake_packages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'inbound_intake_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'package_no' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                ],
                'package_barcode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                ],
                'actual_weight' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'length_cm' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'width_cm' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'height_cm' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,2',
                    'default'    => 0,
                ],
                'remarks' => [
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
            $this->forge->addKey('inbound_intake_id');
            $this->forge->addUniqueKey(['inbound_intake_id', 'package_no']);
            $this->forge->createTable('inbound_intake_packages', true);
        }

        $this->addIndexIfMissing(
            'shipments',
            'idx_shipments_inbound_intake',
            ['inbound_intake_id']
        );

        $status = [
            'status_code'        => 'RECEIVED_AT_ORIGIN_BRANCH',
            'status_name'        => 'Received At Origin Branch',
            'status_color'       => 'info',
            'public_status_code' => 'PICKED_UP',
            'sort_order'         => 45,
            'is_active'          => 1,
            'is_exception'       => 0,
            'is_terminal'        => 0,
            'requires_location'  => 1,
            'requires_notes'     => 0,
        ];

        $existing = $this->db->table('tracking_statuses')
            ->where('status_code', $status['status_code'])
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('tracking_statuses')
                ->where('id', $existing['id'])
                ->update($status);
        } else {
            $this->db->table('tracking_statuses')->insert($status);
        }
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'shipments',
            'idx_shipments_inbound_intake'
        );

        $this->forge->dropTable('inbound_intake_packages', true);
        $this->forge->dropTable('inbound_intakes', true);

        foreach ([
            'received_by',
            'received_at',
            'receiving_location',
            'receiving_branch_id',
            'acceptance_method',
            'inbound_intake_id',
        ] as $field) {
            if ($this->db->fieldExists($field, 'shipments')) {
                $this->forge->dropColumn('shipments', $field);
            }
        }
    }

    private function addIndexIfMissing(
        string $table,
        string $name,
        array $columns
    ): void {
        $indexes = $this->db->getIndexData($table);

        if (isset($indexes[$name])) {
            return;
        }

        $quoted = array_map(
            static fn (string $column): string =>
                '`' . str_replace('`', '``', $column) . '`',
            $columns
        );

        $this->db->query(
            'ALTER TABLE `' . str_replace('`', '``', $table) .
            '` ADD INDEX `' . str_replace('`', '``', $name) .
            '` (' . implode(', ', $quoted) . ')'
        );
    }

    private function dropIndexIfExists(
        string $table,
        string $name
    ): void {
        $indexes = $this->db->getIndexData($table);

        if (!isset($indexes[$name])) {
            return;
        }

        $this->db->query(
            'ALTER TABLE `' . str_replace('`', '``', $table) .
            '` DROP INDEX `' . str_replace('`', '``', $name) . '`'
        );
    }
}
