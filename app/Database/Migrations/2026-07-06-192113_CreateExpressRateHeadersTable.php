<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateExpressRateHeadersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ],
            'vendor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0
            ],
            'service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true
            ],
            'effective_date' => [
                'type' => 'DATE'
            ],
            'remarks' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('vendor_id');
        $this->forge->addKey('service_id');
        $this->forge->addKey('effective_date');

        $this->forge->createTable('express_rate_headers', true);
    }

    public function down()
    {
        $this->forge->dropTable('express_rate_headers', true);
    }
}