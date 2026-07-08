<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShipmentsAddFinancialFields extends Migration
{
    public function up()
    {
        $fields = [

            'declared_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
                'after'      => 'chargeable_weight',
            ],

            'currency_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'declared_value',
            ],

            'insurance_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '18,2',
                'default'    => 0,
                'after'      => 'currency_id',
            ],

        ];

        $this->forge->addColumn('shipments', $fields);

        $this->forge->addKey('currency_id');
    }

    public function down()
    {
        $this->forge->dropColumn('shipments', 'declared_value');
        $this->forge->dropColumn('shipments', 'currency_id');
        $this->forge->dropColumn('shipments', 'insurance_amount');
    }
}