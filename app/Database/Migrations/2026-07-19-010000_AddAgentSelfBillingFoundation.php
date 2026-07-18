<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgentSelfBillingFoundation extends Migration
{
    public function up()
    {
        $this->addAgentSettings();
        $this->extendAgentInvoices();
        $this->extendShipments();
        $this->createAgentInvoiceDetails();
    }

    public function down()
    {
        if ($this->db->tableExists('agent_invoice_details')) {
            $this->forge->dropTable('agent_invoice_details', true);
        }

        $this->dropColumnsIfPresent('shipments', [
            'agent_invoice_id',
            'is_agent_invoiced',
            'agent_invoiced_at',
        ]);

        $this->dropColumnsIfPresent('agent_invoices', [
            'source',
            'approval_status',
            'requested_by',
            'requested_at',
            'approved_by',
            'approved_at',
            'issued_by',
            'issued_at',
            'rejected_by',
            'rejected_at',
            'rejection_reason',
        ]);

        $this->dropColumnsIfPresent('agents', [
            'self_billing_enabled',
            'self_billing_mode',
            'payment_term_days',
        ]);
    }

    private function addAgentSettings(): void
    {
        $fields = [];

        if (!$this->db->fieldExists('self_billing_enabled', 'agents')) {
            $fields['self_billing_enabled'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ];
        }

        if (!$this->db->fieldExists('self_billing_mode', 'agents')) {
            $fields['self_billing_mode'] = [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'REQUIRE_APPROVAL',
            ];
        }

        if (!$this->db->fieldExists('payment_term_days', 'agents')) {
            $fields['payment_term_days'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 7,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('agents', $fields);
        }
    }

    private function extendAgentInvoices(): void
    {
        $definitions = [
            'source' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'FINANCE_MANUAL',
            ],
            'approval_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'APPROVED',
            ],
            'requested_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'requested_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
            ],
            'approved_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'approved_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
            ],
            'issued_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'issued_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
            ],
            'rejected_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'rejected_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
            ],
            'rejection_reason' => [
                'type'  => 'TEXT',
                'null'  => true,
            ],
        ];

        $fields = [];

        foreach ($definitions as $name => $definition) {
            if (!$this->db->fieldExists($name, 'agent_invoices')) {
                $fields[$name] = $definition;
            }
        }

        if ($fields !== []) {
            $this->forge->addColumn('agent_invoices', $fields);
        }
    }

    private function extendShipments(): void
    {
        $definitions = [
            'agent_invoice_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'is_agent_invoiced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'agent_invoiced_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
            ],
        ];

        $fields = [];

        foreach ($definitions as $name => $definition) {
            if (!$this->db->fieldExists($name, 'shipments')) {
                $fields[$name] = $definition;
            }
        }

        if ($fields !== []) {
            $this->forge->addColumn('shipments', $fields);
        }
    }

    private function createAgentInvoiceDetails(): void
    {
        if ($this->db->tableExists('agent_invoice_details')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'agent_invoice_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'shipment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'shipment_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'chargeable_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'freight_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],
            'insurance_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],
            'packing_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],
            'additional_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],
            'discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
            ],
            'line_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
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
        $this->forge->addKey('agent_invoice_id');
        $this->forge->addKey('shipment_id');
        $this->forge->createTable('agent_invoice_details');
    }

    private function dropColumnsIfPresent(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, $table)) {
                $this->forge->dropColumn($table, $column);
            }
        }
    }
}
