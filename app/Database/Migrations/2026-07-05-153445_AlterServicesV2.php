<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterServicesV2 extends Migration
{
    public function up()
    {
        $this->forge->addColumn('services', [

            'vendor_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => true,
                'after' => 'id'
            ],

            'pricing_engine' => [
                'type' => 'ENUM',
                'constraint' => [
                    'REGULAR',
                    'EXPRESS',
                    'SEA',
                    'CHARTER'
                ],
                'default' => 'REGULAR',
                'after' => 'service_name'
            ],

            'transit_day_from' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
                'after' => 'pricing_engine'
            ],

            'transit_day_to' => [
                'type' => 'INT',
                'constraint' => 3,
                'null' => true,
                'after' => 'transit_day_from'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('services', [
            'vendor_id',
            'pricing_engine',
            'transit_day_from',
            'transit_day_to'
        ]);
    }
}