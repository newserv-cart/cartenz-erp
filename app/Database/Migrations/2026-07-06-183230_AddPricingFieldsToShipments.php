<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPricingFieldsToShipments extends Migration
{
    public function up()
    {
       $fields = [

    'freight_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'remote_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'packing_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'additional_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'discount_amount' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'vendor_freight' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'fuel_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'vendor_remote_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'go_green_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'handling_charge' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'vat_amount' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'vendor_total' => [
        'type' => 'DECIMAL',
        'constraint' => '15,2',
        'default' => 0
    ],

    'margin_percent' => [
        'type' => 'DECIMAL',
        'constraint' => '8,2',
        'default' => 0
    ],

    'markup_percent' => [
        'type' => 'DECIMAL',
        'constraint' => '8,2',
        'default' => 0
    ]

];

        $this->forge->addColumn('shipments', $fields);
    }

    // Tambahkan fungsi down() untuk rollback jika dibutuhkan
    public function down()
    {
        $this->forge->dropColumn('shipments', [
            'freight_charge',
            'remote_charge',          
            'packing_charge',
            'additional_charge',
            'discount_amount',
            'vendor_freight',
            'fuel_charge',
            'vendor_remote_charge',
            'go_green_charge',
            'handling_charge',
            'vat_amount',
            'vendor_total',            
            'margin_percent',
            'markup_percent'
        ]);
    }
}