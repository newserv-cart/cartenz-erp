<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixVendorsColumns extends Migration
{
    public function up()
    {
        $fields = [

            'vendor_category' => [
                'name'       => 'vendor_category',
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'PRIMARY',
            ],

            'vendor_priority' => [
                'name'    => 'vendor_priority',
                'type'    => 'INT',
                'default' => 999,
            ],

            'notes' => [
                'name' => 'notes',
                'type' => 'TEXT',
                'null' => true,
            ],

        ];

        $this->forge->modifyColumn('vendors', $fields);
    }

    public function down()
    {
        //
    }
}