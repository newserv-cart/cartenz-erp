<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterShipmentsAddReferences extends Migration
{
    public function up()
    {
        $fields = [

            'customer_reference_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'awb_no',
            ],

            'vendor_awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'customer_reference_no',
            ],

            'master_awb_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'vendor_awb_no',
            ],

        ];

        $this->forge->addColumn('shipments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('shipments', 'customer_reference_no');
        $this->forge->dropColumn('shipments', 'vendor_awb_no');
        $this->forge->dropColumn('shipments', 'master_awb_no');
    }
}