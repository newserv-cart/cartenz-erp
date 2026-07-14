<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDualTrackingWorkflow extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists(
            'public_tracking_status',
            'shipments'
        )) {
            $this->forge->addColumn('shipments', [
                'public_tracking_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'default'    => 'CREATED',
                    'after'      => 'shipment_status',
                ],
            ]);
        }

        if (!$this->db->fieldExists(
            'public_status',
            'shipment_tracking'
        )) {
            $this->forge->addColumn('shipment_tracking', [
                'public_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'tracking_status',
                ],
            ]);
        }

        $statusColumns = [
            'public_status_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'status_name',
            ],
            'is_exception' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_active',
            ],
            'is_terminal' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_exception',
            ],
            'requires_location' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_terminal',
            ],
            'requires_notes' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'requires_location',
            ],
        ];

        foreach ($statusColumns as $name => $definition) {
            if (!$this->db->fieldExists($name, 'tracking_statuses')) {
                $this->forge->addColumn(
                    'tracking_statuses',
                    [$name => $definition]
                );
            }
        }

        $statuses = [
            [
                'status_code'       => 'DRAFT',
                'status_name'       => 'Draft',
                'status_color'      => 'secondary',
                'public_status_code'=> 'CREATED',
                'sort_order'        => 10,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 0,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'CREATED',
                'status_name'       => 'Shipment Created',
                'status_color'      => 'secondary',
                'public_status_code'=> 'CREATED',
                'sort_order'        => 20,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 0,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'MANIFESTED',
                'status_name'       => 'Manifested',
                'status_color'      => 'info',
                'public_status_code'=> 'CREATED',
                'sort_order'        => 30,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'PICKED_UP',
                'status_name'       => 'Picked Up',
                'status_color'      => 'primary',
                'public_status_code'=> 'PICKED_UP',
                'sort_order'        => 40,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'SENT_TO_MAIN_WAREHOUSE',
                'status_name'       => 'Sent To Main Warehouse',
                'status_color'      => 'info',
                'public_status_code'=> 'IN_TRANSIT',
                'sort_order'        => 50,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'RECEIVED_AT_MAIN_WAREHOUSE',
                'status_name'       => 'Received At Main Warehouse',
                'status_color'      => 'success',
                'public_status_code'=> 'IN_TRANSIT',
                'sort_order'        => 60,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'ARRIVED_HUB',
                'status_name'       => 'Arrived Hub',
                'status_color'      => 'info',
                'public_status_code'=> 'IN_TRANSIT',
                'sort_order'        => 70,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'IN_TRANSIT_HUB',
                'status_name'       => 'In Transit Hub',
                'status_color'      => 'warning',
                'public_status_code'=> 'IN_TRANSIT',
                'sort_order'        => 80,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'IN_TRANSIT',
                'status_name'       => 'In Transit',
                'status_color'      => 'warning',
                'public_status_code'=> 'IN_TRANSIT',
                'sort_order'        => 90,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'OUT_FOR_DELIVERY',
                'status_name'       => 'Out For Delivery',
                'status_color'      => 'dark',
                'public_status_code'=> 'OUT_FOR_DELIVERY',
                'sort_order'        => 100,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'DELIVERED',
                'status_name'       => 'Delivered',
                'status_color'      => 'success',
                'public_status_code'=> 'DELIVERED',
                'sort_order'        => 110,
                'is_active'         => 1,
                'is_exception'      => 0,
                'is_terminal'       => 1,
                'requires_location' => 1,
                'requires_notes'    => 0,
            ],
            [
                'status_code'       => 'ON_HOLD',
                'status_name'       => 'On Hold',
                'status_color'      => 'danger',
                'public_status_code'=> 'ON_HOLD',
                'sort_order'        => 900,
                'is_active'         => 1,
                'is_exception'      => 1,
                'is_terminal'       => 0,
                'requires_location' => 1,
                'requires_notes'    => 1,
            ],
            [
                'status_code'       => 'RETURN_TO_SENDER',
                'status_name'       => 'Return To Sender',
                'status_color'      => 'danger',
                'public_status_code'=> 'RETURN_TO_SENDER',
                'sort_order'        => 910,
                'is_active'         => 1,
                'is_exception'      => 1,
                'is_terminal'       => 1,
                'requires_location' => 1,
                'requires_notes'    => 1,
            ],
        ];

        $table = $this->db->table('tracking_statuses');

        foreach ($statuses as $status) {
            $existing = $table
                ->where('status_code', $status['status_code'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table
                    ->where('id', $existing['id'])
                    ->update($status);
            } else {
                $table->insert($status);
            }
        }

        $shipmentPublicCase = $this->publicStatusCase(
            'shipment_status'
        );

        $trackingPublicCase = $this->publicStatusCase(
            'tracking_status'
        );

        $this->db->query(
            'UPDATE shipments
             SET public_tracking_status = ' .
             $shipmentPublicCase
        );

        $this->db->query(
            'UPDATE shipment_tracking
             SET public_status = ' .
             $trackingPublicCase
        );
    }

    public function down()
    {
        foreach ([
            'requires_notes',
            'requires_location',
            'is_terminal',
            'is_exception',
            'public_status_code',
        ] as $column) {
            if ($this->db->fieldExists(
                $column,
                'tracking_statuses'
            )) {
                $this->forge->dropColumn(
                    'tracking_statuses',
                    $column
                );
            }
        }

        if ($this->db->fieldExists(
            'public_status',
            'shipment_tracking'
        )) {
            $this->forge->dropColumn(
                'shipment_tracking',
                'public_status'
            );
        }

        if ($this->db->fieldExists(
            'public_tracking_status',
            'shipments'
        )) {
            $this->forge->dropColumn(
                'shipments',
                'public_tracking_status'
            );
        }
    }

    private function publicStatusCase(string $column): string
    {
        return "CASE UPPER({$column})
            WHEN 'DRAFT' THEN 'CREATED'
            WHEN 'CREATED' THEN 'CREATED'
            WHEN 'MANIFESTED' THEN 'CREATED'
            WHEN 'PICKED_UP' THEN 'PICKED_UP'
            WHEN 'SENT_TO_MAIN_WAREHOUSE' THEN 'IN_TRANSIT'
            WHEN 'RECEIVED_AT_MAIN_WAREHOUSE' THEN 'IN_TRANSIT'
            WHEN 'ARRIVED_HUB' THEN 'IN_TRANSIT'
            WHEN 'IN_TRANSIT_HUB' THEN 'IN_TRANSIT'
            WHEN 'IN_TRANSIT' THEN 'IN_TRANSIT'
            WHEN 'OUT_FOR_DELIVERY' THEN 'OUT_FOR_DELIVERY'
            WHEN 'DELIVERED' THEN 'DELIVERED'
            WHEN 'ON_HOLD' THEN 'ON_HOLD'
            WHEN 'RETURN_TO_SENDER' THEN 'RETURN_TO_SENDER'
            ELSE 'CREATED'
        END";
    }
}
