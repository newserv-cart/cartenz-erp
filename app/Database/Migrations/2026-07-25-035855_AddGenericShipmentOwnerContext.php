<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGenericShipmentOwnerContext extends Migration
{
    public function up()
    {
        $this->addAddressBookColumns();
        $this->addShipmentColumns();
        $this->backfillLegacyData();
    }

    private function addAddressBookColumns(): void
    {
        $fields = [];

        if (
            !$this->db->fieldExists(
                'owner_type',
                'customer_addresses'
            )
        ) {
            $fields['owner_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'CUSTOMER, AGENT, WHITE_LABEL',
            ];
        }

        if (
            !$this->db->fieldExists(
                'owner_id',
                'customer_addresses'
            )
        ) {
            $fields['owner_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (
            !$this->db->fieldExists(
                'is_primary',
                'customer_addresses'
            )
        ) {
            $fields['is_primary'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ];
        }

        if (
            !$this->db->fieldExists(
                'source',
                'customer_addresses'
            )
        ) {
            $fields['source'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'MANUAL',
                'null'       => false,
                'comment'    => implode(', ', [
                    'ACCOUNT_PROFILE',
                    'SHIPMENT_INPUT',
                    'MANUAL',
                    'LEGACY',
                ]),
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn(
                'customer_addresses',
                $fields
            );
        }
    }

    private function addShipmentColumns(): void
    {
        $fields = [];

        if (
            !$this->db->fieldExists(
                'owner_type',
                'shipments'
            )
        ) {
            $fields['owner_type'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment'    => 'CUSTOMER, AGENT, WHITE_LABEL',
            ];
        }

        if (
            !$this->db->fieldExists(
                'owner_id',
                'shipments'
            )
        ) {
            $fields['owner_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (
            !$this->db->fieldExists(
                'created_by_user_id',
                'shipments'
            )
        ) {
            $fields['created_by_user_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ];
        }

        if (
            !$this->db->fieldExists(
                'entry_channel',
                'shipments'
            )
        ) {
            $fields['entry_channel'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'LEGACY',
                'null'       => false,
                'comment'    => implode(', ', [
                    'SELF_SERVICE',
                    'ADMIN_ASSISTED',
                    'LEGACY',
                ]),
            ];
        }

        if (
            !$this->db->fieldExists(
                'sender_snapshot',
                'shipments'
            )
        ) {
            $fields['sender_snapshot'] = [
                'type' => 'LONGTEXT',
                'null' => true,
            ];
        }

        if (
            !$this->db->fieldExists(
                'receiver_snapshot',
                'shipments'
            )
        ) {
            $fields['receiver_snapshot'] = [
                'type' => 'LONGTEXT',
                'null' => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn(
                'shipments',
                $fields
            );
        }
    }

    private function backfillLegacyData(): void
    {
        $this->db->query("
            UPDATE customer_addresses
            SET
                owner_type = 'CUSTOMER',
                owner_id = customer_id,
                source = 'LEGACY'
            WHERE customer_id IS NOT NULL
              AND (
                    owner_type IS NULL
                    OR owner_type = ''
                    OR owner_id IS NULL
                  )
        ");

        $this->db->query("
            UPDATE shipments
            SET
                owner_type = 'CUSTOMER',
                owner_id = customer_id,
                entry_channel = 'LEGACY'
            WHERE customer_id IS NOT NULL
              AND (
                    owner_type IS NULL
                    OR owner_type = ''
                    OR owner_id IS NULL
                  )
        ");
    }

    public function down()
    {
        $shipmentColumns = [
            'owner_type',
            'owner_id',
            'created_by_user_id',
            'entry_channel',
            'sender_snapshot',
            'receiver_snapshot',
        ];

        foreach ($shipmentColumns as $column) {
            if (
                $this->db->fieldExists(
                    $column,
                    'shipments'
                )
            ) {
                $this->forge->dropColumn(
                    'shipments',
                    $column
                );
            }
        }

        $addressColumns = [
            'owner_type',
            'owner_id',
            'is_primary',
            'source',
        ];

        foreach ($addressColumns as $column) {
            if (
                $this->db->fieldExists(
                    $column,
                    'customer_addresses'
                )
            ) {
                $this->forge->dropColumn(
                    'customer_addresses',
                    $column
                );
            }
        }
    }
}
