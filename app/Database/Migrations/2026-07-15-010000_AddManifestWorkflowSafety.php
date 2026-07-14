<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddManifestWorkflowSafety extends Migration
{
    public function up()
    {
        $manifestFields = [
            'created_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'remarks',
            ],
            'dispatched_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'created_by',
            ],
            'dispatched_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'dispatched_at',
            ],
            'received_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'dispatched_by',
            ],
            'received_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'received_at',
            ],
            'closed_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'received_by',
            ],
            'closed_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'closed_at',
            ],
        ];

        foreach ($manifestFields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'manifests')) {
                $this->forge->addColumn(
                    'manifests',
                    [$name => $definition]
                );
            }
        }

        $detailFields = [
            'previous_shipment_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'actual_weight',
            ],
            'previous_public_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'previous_shipment_status',
            ],
            'added_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'previous_public_status',
            ],
            'added_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'added_at',
            ],
        ];

        foreach ($detailFields as $name => $definition) {
            if (
                !$this->db->fieldExists(
                    $name,
                    'manifest_details'
                )
            ) {
                $this->forge->addColumn(
                    'manifest_details',
                    [$name => $definition]
                );
            }
        }

        $this->addIndexIfMissing(
            'manifests',
            'idx_manifests_status',
            ['status']
        );

        $this->addIndexIfMissing(
            'manifest_details',
            'idx_manifest_details_manifest',
            ['manifest_id']
        );

        $this->addIndexIfMissing(
            'manifest_details',
            'idx_manifest_details_shipment',
            ['shipment_id']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'manifest_details',
            'idx_manifest_details_shipment'
        );

        $this->dropIndexIfExists(
            'manifest_details',
            'idx_manifest_details_manifest'
        );

        $this->dropIndexIfExists(
            'manifests',
            'idx_manifests_status'
        );

        foreach (
            [
                'added_by',
                'added_at',
                'previous_public_status',
                'previous_shipment_status',
            ] as $field
        ) {
            if (
                $this->db->fieldExists(
                    $field,
                    'manifest_details'
                )
            ) {
                $this->forge->dropColumn(
                    'manifest_details',
                    $field
                );
            }
        }

        foreach (
            [
                'closed_by',
                'closed_at',
                'received_by',
                'received_at',
                'dispatched_by',
                'dispatched_at',
                'created_by',
            ] as $field
        ) {
            if ($this->db->fieldExists($field, 'manifests')) {
                $this->forge->dropColumn(
                    'manifests',
                    $field
                );
            }
        }
    }

    private function addIndexIfMissing(
        string $table,
        string $indexName,
        array $columns
    ): void {
        $indexes = $this->db->getIndexData($table);

        if (isset($indexes[$indexName])) {
            return;
        }

        $quotedColumns = array_map(
            static fn (string $column): string =>
                '`' . str_replace('`', '``', $column) . '`',
            $columns
        );

        $this->db->query(
            'ALTER TABLE `' .
            str_replace('`', '``', $table) .
            '` ADD INDEX `' .
            str_replace('`', '``', $indexName) .
            '` (' .
            implode(', ', $quotedColumns) .
            ')'
        );
    }

    private function dropIndexIfExists(
        string $table,
        string $indexName
    ): void {
        $indexes = $this->db->getIndexData($table);

        if (!isset($indexes[$indexName])) {
            return;
        }

        $this->db->query(
            'ALTER TABLE `' .
            str_replace('`', '``', $table) .
            '` DROP INDEX `' .
            str_replace('`', '``', $indexName) .
            '`'
        );
    }
}
