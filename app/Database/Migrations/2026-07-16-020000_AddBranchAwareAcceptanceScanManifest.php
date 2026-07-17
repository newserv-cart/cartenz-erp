<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBranchAwareAcceptanceScanManifest extends Migration
{
    public function up()
    {
        $this->createOperationalProfiles();
        $this->addShipmentBranchFields();
        $this->addTrackingBranchFields();
        $this->addManifestBranchFields();
        $this->addPickupAuditFields();
        $this->addIndexes();
        $this->seedTrackingStatus();
        $this->seedCartenzAdminProfile();
        $this->backfillCurrentBranch();
    }

    public function down()
    {
        $this->dropIndexIfExists(
            'pickup_requests',
            'idx_pickup_requests_assigned_user'
        );

        $this->dropIndexIfExists(
            'manifests',
            'idx_manifests_origin_status'
        );

        $this->dropIndexIfExists(
            'manifests',
            'idx_manifests_destination_status'
        );

        $this->dropIndexIfExists(
            'shipment_tracking',
            'idx_shipment_tracking_branch'
        );

        $this->dropIndexIfExists(
            'shipments',
            'idx_shipments_current_branch_status'
        );

        $this->dropIndexIfExists(
            'shipments',
            'idx_shipments_planned_dropoff'
        );

        $this->dropColumnsIfPresent(
            'pickup_requests',
            [
                'assigned_user_id',
                'picked_up_at',
                'picked_up_by',
            ]
        );

        $this->dropColumnsIfPresent(
            'manifests',
            [
                'dispatched_branch_id',
                'received_branch_id',
                'closed_branch_id',
            ]
        );

        $this->dropColumnsIfPresent(
            'shipment_tracking',
            [
                'branch_id',
                'event_type',
                'scan_type',
                'source_reference',
            ]
        );

        $this->dropColumnsIfPresent(
            'shipments',
            [
                'planned_handover_method',
                'planned_dropoff_branch_id',
                'acceptance_branch_id',
                'current_branch_id',
                'last_scan_branch_id',
                'last_scan_type',
                'last_scan_at',
                'last_scan_by',
            ]
        );

        if (
            $this->db->tableExists(
                'user_operational_profiles'
            )
        ) {
            $this->forge->dropTable(
                'user_operational_profiles',
                true
            );
        }
    }

    private function createOperationalProfiles(): void
    {
        if (
            $this->db->tableExists(
                'user_operational_profiles'
            )
        ) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'branch_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
            ],
            'agent_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
            ],
            'staff_type' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'default' => 'BRANCH',
            ],
            'can_scan_pickup' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'can_scan_dropoff' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->addUniqueKey('user_id');
        $this->forge->addKey('branch_id');
        $this->forge->addKey('agent_id');

        $this->forge->createTable(
            'user_operational_profiles',
            true
        );
    }

    private function addShipmentBranchFields(): void
    {
        if (!$this->db->tableExists('shipments')) {
            return;
        }

        $this->addColumnsIfMissing(
            'shipments',
            [
                'planned_handover_method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'acceptance_method',
                ],
                'planned_dropoff_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'planned_handover_method',
                ],
                'acceptance_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'planned_dropoff_branch_id',
                ],
                'current_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'acceptance_branch_id',
                ],
                'last_scan_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'current_branch_id',
                ],
                'last_scan_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'last_scan_branch_id',
                ],
                'last_scan_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'last_scan_type',
                ],
                'last_scan_by' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'last_scan_at',
                ],
            ]
        );
    }

    private function addTrackingBranchFields(): void
    {
        if (
            !$this->db->tableExists(
                'shipment_tracking'
            )
        ) {
            return;
        }

        $this->addColumnsIfMissing(
            'shipment_tracking',
            [
                'branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'shipment_id',
                ],
                'event_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'public_status',
                ],
                'scan_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'event_type',
                ],
                'source_reference' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                    'after' => 'scan_type',
                ],
            ]
        );
    }

    private function addManifestBranchFields(): void
    {
        if (!$this->db->tableExists('manifests')) {
            return;
        }

        $this->addColumnsIfMissing(
            'manifests',
            [
                'origin_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'manifest_no',
                ],
                'destination_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'origin_branch_id',
                ],
                'dispatched_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'dispatched_by',
                ],
                'received_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'received_by',
                ],
                'closed_branch_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'closed_by',
                ],
            ]
        );
    }

    private function addPickupAuditFields(): void
    {
        if (
            !$this->db->tableExists(
                'pickup_requests'
            )
        ) {
            return;
        }

        $this->addColumnsIfMissing(
            'pickup_requests',
            [
                'assigned_user_id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
                'picked_up_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'picked_up_by' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'null' => true,
                ],
            ]
        );
    }

    private function addIndexes(): void
    {
        $this->addIndexIfMissing(
            'shipments',
            'idx_shipments_current_branch_status',
            ['current_branch_id', 'shipment_status']
        );

        $this->addIndexIfMissing(
            'shipments',
            'idx_shipments_planned_dropoff',
            ['planned_dropoff_branch_id']
        );

        $this->addIndexIfMissing(
            'shipment_tracking',
            'idx_shipment_tracking_branch',
            ['branch_id', 'event_time']
        );

        $this->addIndexIfMissing(
            'manifests',
            'idx_manifests_origin_status',
            ['origin_branch_id', 'status']
        );

        $this->addIndexIfMissing(
            'manifests',
            'idx_manifests_destination_status',
            ['destination_branch_id', 'status']
        );

        $this->addIndexIfMissing(
            'pickup_requests',
            'idx_pickup_requests_assigned_user',
            ['assigned_user_id', 'pickup_status']
        );
    }

    private function seedTrackingStatus(): void
    {
        if (
            !$this->db->tableExists(
                'tracking_statuses'
            )
        ) {
            return;
        }

        $exists = $this->db
            ->table('tracking_statuses')
            ->where(
                'status_code',
                'RECEIVED_AT_ORIGIN_BRANCH'
            )
            ->get()
            ->getRowArray();

        if ($exists) {
            return;
        }

        $this->db
            ->table('tracking_statuses')
            ->insert([
                'status_code' =>
                    'RECEIVED_AT_ORIGIN_BRANCH',
                'status_name' =>
                    'Received At Origin Branch',
                'status_color' => 'info',
                'sort_order' => 3,
                'is_active' => 1,
            ]);
    }

    private function seedCartenzAdminProfile(): void
    {
        if (
            !$this->db->tableExists(
                'user_operational_profiles'
            )
            || !$this->db->tableExists('users')
            || !$this->db->tableExists('branches')
        ) {
            return;
        }

        $user = $this->db
            ->table('users')
            ->where('username', 'cartenzadmin')
            ->get()
            ->getRowArray();

        $branch = $this->db
            ->table('branches')
            ->where('branch_code', 'JKT-HO')
            ->get()
            ->getRowArray();

        if (!$user || !$branch) {
            return;
        }

        $exists = $this->db
            ->table('user_operational_profiles')
            ->where('user_id', (int) $user['id'])
            ->get()
            ->getRowArray();

        if ($exists) {
            return;
        }

        $agent = null;

        if ($this->db->tableExists('agents')) {
            $agent = $this->db
                ->table('agents')
                ->where('agent_code', 'AGT001')
                ->get()
                ->getRowArray();
        }

        $now = date('Y-m-d H:i:s');

        $this->db
            ->table('user_operational_profiles')
            ->insert([
                'user_id' => (int) $user['id'],
                'branch_id' => (int) $branch['id'],
                'agent_id' => $agent
                    ? (int) $agent['id']
                    : null,
                'staff_type' => 'HEAD_OFFICE',
                'can_scan_pickup' => 1,
                'can_scan_dropoff' => 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
    }

    private function backfillCurrentBranch(): void
    {
        if (!$this->db->tableExists('shipments')) {
            return;
        }

        $this->db->query(
            "UPDATE shipments
             SET acceptance_branch_id =
                    COALESCE(
                        acceptance_branch_id,
                        receiving_branch_id
                    ),
                 current_branch_id =
                    COALESCE(
                        current_branch_id,
                        receiving_branch_id
                    )
             WHERE receiving_branch_id IS NOT NULL
               AND shipment_status IN (
                    'RECEIVED_AT_ORIGIN_BRANCH',
                    'ARRIVED_HUB',
                    'RECEIVED_AT_MAIN_WAREHOUSE'
               )"
        );
    }

    private function addColumnsIfMissing(
        string $table,
        array $columns
    ): void {
        foreach ($columns as $name => $definition) {
            if ($this->db->fieldExists($name, $table)) {
                continue;
            }

            $this->forge->addColumn(
                $table,
                [$name => $definition]
            );
        }
    }

    private function dropColumnsIfPresent(
        string $table,
        array $columns
    ): void {
        if (!$this->db->tableExists($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!$this->db->fieldExists($column, $table)) {
                continue;
            }

            $this->forge->dropColumn(
                $table,
                $column
            );
        }
    }

    private function addIndexIfMissing(
        string $table,
        string $name,
        array $columns
    ): void {
        if (
            !$this->db->tableExists($table)
            || $this->indexExists($table, $name)
        ) {
            return;
        }

        foreach ($columns as $column) {
            if (!$this->db->fieldExists($column, $table)) {
                return;
            }
        }

        $quoted = array_map(
            fn (string $column): string =>
                $this->quoteIdentifier($column),
            $columns
        );

        $this->db->query(
            sprintf(
                'ALTER TABLE %s ADD INDEX %s (%s)',
                $this->quoteIdentifier($table),
                $this->quoteIdentifier($name),
                implode(', ', $quoted)
            )
        );
    }

    private function indexExists(
        string $table,
        string $name
    ): bool {
        return isset(
            $this->db->getIndexData($table)[$name]
        );
    }

    private function dropIndexIfExists(
        string $table,
        string $name
    ): void {
        if (
            !$this->db->tableExists($table)
            || !$this->indexExists($table, $name)
        ) {
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
        return '`' . str_replace(
            '`',
            '``',
            $value
        ) . '`';
    }
}
