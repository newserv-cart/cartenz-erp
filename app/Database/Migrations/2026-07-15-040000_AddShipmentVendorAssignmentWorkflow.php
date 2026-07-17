<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShipmentVendorAssignmentWorkflow extends Migration
{
    public function up()
    {
        $shipmentFields = [
            'vendor_assignment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'UNASSIGNED',
                'after'      => 'tracking_no_vendor',
            ],
            'vendor_assignment_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'vendor_assignment_status',
            ],
            'vendor_recommendation_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_assignment_source',
            ],
            'vendor_rate_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'vendor_recommendation_id',
            ],
            'vendor_rate_reference_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_rate_source',
            ],
            'vendor_rate_currency_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_rate_reference_id',
            ],
            'vendor_rate_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'vendor_rate_currency_id',
            ],
            'vendor_assigned_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_rate_weight',
            ],
            'vendor_assigned_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_assigned_at',
            ],
            'vendor_override_reason' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'vendor_assigned_by',
            ],
            'vendor_cost_locked_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_override_reason',
            ],
            'vendor_cost_locked_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_cost_locked_at',
            ],
            'vendor_submission_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'NOT_SUBMITTED',
                'after'      => 'vendor_cost_locked_by',
            ],
            'vendor_submitted_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'vendor_submission_status',
            ],
            'vendor_submitted_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'vendor_submitted_at',
            ],
        ];

        foreach ($shipmentFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $this->forge->addColumn('shipments', [$name => $definition]);
            }
        }

        $recommendationFields = [
            'rank_no' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'recommended',
            ],
            'rate_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'rank_no',
            ],
            'rate_reference_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'rate_source',
            ],
            'currency_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'rate_reference_id',
            ],
            'chargeable_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'currency_id',
            ],
            'rate_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
                'after'      => 'chargeable_weight',
            ],
            'rate_effective_date' => [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'rate_weight',
            ],
            'calculation_json' => [
                'type'  => 'LONGTEXT',
                'null'  => true,
                'after' => 'rate_effective_date',
            ],
            'generated_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'calculation_json',
            ],
        ];

        foreach ($recommendationFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipment_vendor_recommendations')) {
                $this->forge->addColumn(
                    'shipment_vendor_recommendations',
                    [$name => $definition]
                );
            }
        }

        if (!$this->db->fieldExists('currency_id', 'vendor_rates')) {
            $this->forge->addColumn('vendor_rates', [
                'currency_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'minimum_charge',
                ],
            ]);
        }

        $idr = $this->db
            ->table('currencies')
            ->select('id')
            ->where('currency_code', 'IDR')
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!empty($idr['id'])) {
            $this->db
                ->table('vendor_rates')
                ->where('currency_id', null)
                ->update(['currency_id' => (int) $idr['id']]);
        }

        $this->db->query(
            "UPDATE shipments SET vendor_assignment_status = " .
            "CASE WHEN vendor_id IS NULL OR vendor_id = 0 " .
            "THEN 'UNASSIGNED' ELSE 'LEGACY_ASSIGNED' END " .
            "WHERE vendor_assignment_status IS NULL " .
            "OR vendor_assignment_status = ''"
        );

        $this->addIndexIfMissing(
            'shipment_vendor_recommendations',
            'idx_vendor_recommendation_shipment_rank',
            ['shipment_id', 'rank_no']
        );

        $this->addIndexIfMissing(
            'shipments',
            'idx_shipments_vendor_submission',
            ['vendor_id', 'vendor_submission_status']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'shipments',
            'idx_shipments_vendor_submission'
        );
        $this->dropIndexIfExists(
            'shipment_vendor_recommendations',
            'idx_vendor_recommendation_shipment_rank'
        );

        foreach (
            [
                'generated_by',
                'calculation_json',
                'rate_effective_date',
                'rate_weight',
                'chargeable_weight',
                'currency_id',
                'rate_reference_id',
                'rate_source',
                'rank_no',
            ] as $field
        ) {
            if ($this->db->fieldExists($field, 'shipment_vendor_recommendations')) {
                $this->forge->dropColumn('shipment_vendor_recommendations', $field);
            }
        }

        foreach (
            [
                'vendor_submitted_by',
                'vendor_submitted_at',
                'vendor_submission_status',
                'vendor_cost_locked_by',
                'vendor_cost_locked_at',
                'vendor_override_reason',
                'vendor_assigned_by',
                'vendor_assigned_at',
                'vendor_rate_weight',
                'vendor_rate_currency_id',
                'vendor_rate_reference_id',
                'vendor_rate_source',
                'vendor_recommendation_id',
                'vendor_assignment_source',
                'vendor_assignment_status',
            ] as $field
        ) {
            if ($this->db->fieldExists($field, 'shipments')) {
                $this->forge->dropColumn('shipments', $field);
            }
        }

        if ($this->db->fieldExists('currency_id', 'vendor_rates')) {
            $this->forge->dropColumn('vendor_rates', 'currency_id');
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
