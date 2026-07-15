<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpressRateExcelImport extends Migration
{
    public function up()
    {
        $headerFields = [
            'currency_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'service_id',
            ],
            'import_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'remarks',
            ],
            'import_batch_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'import_source',
            ],
        ];

        foreach ($headerFields as $name => $definition) {
            if (
                !$this->db->fieldExists(
                    $name,
                    'express_rate_headers'
                )
            ) {
                $this->forge->addColumn(
                    'express_rate_headers',
                    [$name => $definition]
                );
            }
        }

        if (!$this->db->tableExists('express_rate_import_batches')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'vendor_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'service_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'currency_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'header_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'effective_date' => [
                    'type' => 'DATE',
                ],
                'original_filename' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'source_format' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                ],
                'import_mode' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                ],
                'total_rows' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'imported_rows' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'PROCESSING',
                ],
                'error_summary' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'imported_by' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'imported_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('vendor_id');
            $this->forge->addKey('header_id');
            $this->forge->addKey('status');
            $this->forge->createTable(
                'express_rate_import_batches',
                true
            );
        }

        $this->addUniqueIndexIfMissing(
            'express_rate_details',
            'uq_express_rate_detail_key',
            ['header_id', 'shipment_type', 'zone', 'weight']
        );

        $this->addIndexIfMissing(
            'express_rate_headers',
            'idx_express_header_currency',
            ['currency_id']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'express_rate_headers',
            'idx_express_header_currency'
        );

        $this->dropIndexIfExists(
            'express_rate_details',
            'uq_express_rate_detail_key'
        );

        if ($this->db->tableExists('express_rate_import_batches')) {
            $this->forge->dropTable(
                'express_rate_import_batches',
                true
            );
        }

        foreach (
            ['import_batch_id', 'import_source', 'currency_id']
            as $field
        ) {
            if (
                $this->db->fieldExists(
                    $field,
                    'express_rate_headers'
                )
            ) {
                $this->forge->dropColumn(
                    'express_rate_headers',
                    $field
                );
            }
        }
    }

    private function addIndexIfMissing(
        string $table,
        string $name,
        array $columns
    ): void {
        if ($this->indexExists($table, $name)) {
            return;
        }

        $this->db->query(
            $this->buildIndexSql(
                $table,
                $name,
                $columns,
                false
            )
        );
    }

    private function addUniqueIndexIfMissing(
        string $table,
        string $name,
        array $columns
    ): void {
        if ($this->indexExists($table, $name)) {
            return;
        }

        $this->db->query(
            $this->buildIndexSql(
                $table,
                $name,
                $columns,
                true
            )
        );
    }

    private function buildIndexSql(
        string $table,
        string $name,
        array $columns,
        bool $unique
    ): string {
        $quotedColumns = array_map(
            fn (string $column): string =>
                $this->quoteIdentifier($column),
            $columns
        );

        return sprintf(
            'ALTER TABLE %s ADD %s INDEX %s (%s)',
            $this->quoteIdentifier($table),
            $unique ? 'UNIQUE' : '',
            $this->quoteIdentifier($name),
            implode(', ', $quotedColumns)
        );
    }

    private function indexExists(
        string $table,
        string $name
    ): bool {
        return isset($this->db->getIndexData($table)[$name]);
    }

    private function dropIndexIfExists(
        string $table,
        string $name
    ): void {
        if (!$this->indexExists($table, $name)) {
            return;
        }

        $this->db->query(
            sprintf(
                'ALTER TABLE %s DROP INDEX %s',
                $this->quoteIdentifier($table),
                $this->quoteIdentifier($name)
            )
        );
    }

    private function quoteIdentifier(string $value): string
    {
        return '`' . str_replace('`', '``', $value) . '`';
    }
}
