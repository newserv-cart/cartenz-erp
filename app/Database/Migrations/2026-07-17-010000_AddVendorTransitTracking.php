<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendorTransitTracking extends Migration
{
    private const NEW_STATUS_CODES = [
        'SUBMITTED_TO_VENDOR',
        'VENDOR_PICKED_UP',
        'DEPARTED_ORIGIN',
        'ARRIVED_AT_TRANSIT_HUB',
        'DEPARTED_TRANSIT_HUB',
        'ARRIVED_DESTINATION_COUNTRY',
        'CUSTOMS_CLEARANCE_STARTED',
        'CUSTOMS_CLEARANCE_COMPLETED',
        'CUSTOMS_EXCEPTION',
        'HANDED_TO_LAST_MILE',
    ];

    public function up()
    {
        $this->createVendorTrackingEvents();
        $this->createVendorStatusMappings();
        $this->extendShipmentTracking();
        $this->seedTrackingStatuses();
    }

    public function down()
    {
        if ($this->db->tableExists('tracking_statuses')) {
            $this->db
                ->table('tracking_statuses')
                ->whereIn('status_code', self::NEW_STATUS_CODES)
                ->delete();
        }

        if ($this->db->tableExists('shipment_tracking')) {
            foreach ([
                'is_public',
                'event_sequence',
                'timezone_name',
                'hub_code',
                'city',
                'country_name',
                'country_code',
                'vendor_status_code',
                'source_event_id',
                'source_system',
                'vendor_event_id',
            ] as $column) {
                if ($this->db->fieldExists($column, 'shipment_tracking')) {
                    $this->forge->dropColumn('shipment_tracking', $column);
                }
            }
        }

        $this->forge->dropTable('vendor_status_mappings', true);
        $this->forge->dropTable('shipment_vendor_tracking_events', true);
    }

    private function createVendorTrackingEvents(): void
    {
        if ($this->db->tableExists('shipment_vendor_tracking_events')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shipment_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
            ],
            'vendor_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'shipment_package_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'vendor_awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'internal_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'public_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'IN_TRANSIT',
            ],
            'event_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'MANUAL',
            ],
            'source_event_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'vendor_status_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'country_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'null'       => true,
            ],
            'country_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'hub_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'location_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'timezone_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'event_time' => [
                'type' => 'DATETIME',
            ],
            'event_sequence' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'tracking_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'event_fingerprint' => [
                'type'       => 'CHAR',
                'constraint' => 64,
            ],
            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'raw_payload' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('shipment_id');
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('vendor_awb_no');
        $this->forge->addKey('internal_status');
        $this->forge->addKey('public_status');
        $this->forge->addKey('event_time');
        $this->forge->addUniqueKey('event_fingerprint');
        $this->forge->addUniqueKey(
            ['shipment_id', 'event_source', 'source_event_id'],
            'uq_vendor_tracking_source_event'
        );
        $this->forge->createTable('shipment_vendor_tracking_events');
    }

    private function createVendorStatusMappings(): void
    {
        if ($this->db->tableExists('vendor_status_mappings')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vendor_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'default'    => 0,
            ],
            'vendor_status_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'internal_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
            ],
            'public_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'IN_TRANSIT',
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('vendor_id');
        $this->forge->addUniqueKey(
            ['vendor_id', 'vendor_status_code'],
            'uq_vendor_status_mapping'
        );
        $this->forge->createTable('vendor_status_mappings');
    }

    private function extendShipmentTracking(): void
    {
        if (!$this->db->tableExists('shipment_tracking')) {
            return;
        }

        $columns = [
            'vendor_event_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'shipment_id',
            ],
            'source_system' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'source_reference',
            ],
            'source_event_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'source_system',
            ],
            'vendor_status_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'source_event_id',
            ],
            'country_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'null'       => true,
                'after'      => 'tracking_location',
            ],
            'country_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'country_code',
            ],
            'city' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'country_name',
            ],
            'hub_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'city',
            ],
            'timezone_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'hub_code',
            ],
            'event_sequence' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'event_time',
            ],
            'is_public' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'event_sequence',
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipment_tracking')) {
                $this->forge->addColumn(
                    'shipment_tracking',
                    [$name => $definition]
                );
            }
        }
    }

    private function seedTrackingStatuses(): void
    {
        if (!$this->db->tableExists('tracking_statuses')) {
            return;
        }

        $statuses = [
            ['SUBMITTED_TO_VENDOR', 'Submitted to Vendor', 'IN_TRANSIT', 120, 'info'],
            ['VENDOR_PICKED_UP', 'Vendor Picked Up', 'IN_TRANSIT', 130, 'primary'],
            ['DEPARTED_ORIGIN', 'Departed Origin Country', 'IN_TRANSIT', 140, 'info'],
            ['ARRIVED_AT_TRANSIT_HUB', 'Arrived at Transit Hub', 'IN_TRANSIT', 150, 'warning'],
            ['DEPARTED_TRANSIT_HUB', 'Departed Transit Hub', 'IN_TRANSIT', 160, 'warning'],
            ['ARRIVED_DESTINATION_COUNTRY', 'Arrived Destination Country', 'IN_TRANSIT', 170, 'info'],
            ['CUSTOMS_CLEARANCE_STARTED', 'Customs Clearance Started', 'IN_TRANSIT', 180, 'warning'],
            ['CUSTOMS_CLEARANCE_COMPLETED', 'Customs Clearance Completed', 'IN_TRANSIT', 190, 'success'],
            ['CUSTOMS_EXCEPTION', 'Customs Exception', 'ON_HOLD', 195, 'danger'],
            ['HANDED_TO_LAST_MILE', 'Handed to Last Mile', 'IN_TRANSIT', 200, 'dark'],
        ];

        $fieldNames = $this->db->getFieldNames('tracking_statuses');
        $fieldMap   = array_fill_keys($fieldNames, true);
        $table      = $this->db->table('tracking_statuses');

        foreach ($statuses as [$code, $name, $public, $sort, $color]) {
            $existing = $table
                ->where('status_code', $code)
                ->get()
                ->getRowArray();

            if ($existing) {
                continue;
            }

            $row = [
                'status_code'        => $code,
                'status_name'        => $name,
                'status_color'       => $color,
                'public_status_code' => $public,
                'sort_order'         => $sort,
                /*
                 * Vendor transit statuses memakai form khusus.
                 * Jangan tampilkan di dropdown status internal generik.
                 */
                'is_active'          => 0,
                'is_exception'       => $code === 'CUSTOMS_EXCEPTION' ? 1 : 0,
                'is_terminal'        => 0,
                'requires_location'  => 1,
                'requires_notes'     => 0,
            ];

            $table->insert(array_intersect_key($row, $fieldMap));
        }
    }
}
