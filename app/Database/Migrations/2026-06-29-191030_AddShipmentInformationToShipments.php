<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShipmentInformationToShipments extends Migration
{
    public function up()
{
    $this->forge->addColumn('shipments', [

        'payment_type' => [
            'type'       => 'VARCHAR',
            'constraint' => 20,
            'null'       => true,
            'after'      => 'package_type_id'
        ],

        'commodity_type_id' => [
            'type'       => 'INT',
            'constraint' => 11,
            'null'       => true,
            'after'      => 'payment_type'
        ],

        'shipment_date' => [
            'type' => 'DATE',
            'null' => true,
            'after' => 'customer_reference_no'
        ],

    ]);
}

public function down()
{
    $this->forge->dropColumn('shipments', [
        'payment_type',
        'commodity_type_id',
        'shipment_date'
    ]);
}
}
