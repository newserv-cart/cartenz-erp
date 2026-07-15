<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendorRateFoundationSafety extends Migration
{
    public function up()
    {
        $this->addUniqueIndexIfMissing(
            'vendors',
            'uq_vendors_vendor_code',
            ['vendor_code']
        );

        $this->addIndexIfMissing(
            'vendors',
            'idx_vendors_status_priority',
            ['status', 'vendor_priority']
        );

        $this->addIndexIfMissing(
            'vendor_rates',
            'idx_vendor_rates_lookup',
            [
                'vendor_id',
                'country_id',
                'service_id',
                'transport_mode_id',
                'delivery_type_id',
                'status',
            ]
        );

        $this->addIndexIfMissing(
            'vendor_rates',
            'idx_vendor_rates_weight',
            ['weight_from', 'weight_to']
        );

        $this->addIndexIfMissing(
            'express_rate_headers',
            'idx_express_headers_lookup',
            ['vendor_id', 'service_id', 'effective_date']
        );

        $this->addIndexIfMissing(
            'express_rate_details',
            'idx_express_details_lookup',
            ['header_id', 'shipment_type', 'zone', 'weight']
        );
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'express_rate_details',
            'idx_express_details_lookup'
        );

        $this->dropIndexIfExists(
            'express_rate_headers',
            'idx_express_headers_lookup'
        );

        $this->dropIndexIfExists(
            'vendor_rates',
            'idx_vendor_rates_weight'
        );

        $this->dropIndexIfExists(
            'vendor_rates',
            'idx_vendor_rates_lookup'
        );

        $this->dropIndexIfExists(
            'vendors',
            'idx_vendors_status_priority'
        );

        $this->dropIndexIfExists(
            'vendors',
            'uq_vendors_vendor_code'
        );
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
        $indexes = $this->db->getIndexData($table);

        return isset($indexes[$name]);
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
