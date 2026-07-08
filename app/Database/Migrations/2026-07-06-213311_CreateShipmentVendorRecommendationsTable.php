<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentVendorRecommendationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id'=>[
                'type'=>'BIGINT',
                'unsigned'=>true,
                'auto_increment'=>true
            ],

            'shipment_id'=>[
                'type'=>'BIGINT',
                'unsigned'=>true
            ],

            'vendor_id'=>[
                'type'=>'BIGINT',
                'unsigned'=>true
            ],

            'vendor_cost'=>[
                'type'=>'DECIMAL',
                'constraint'=>'15,2',
                'default'=>0
            ],

            'customer_charge'=>[
                'type'=>'DECIMAL',
                'constraint'=>'15,2',
                'default'=>0
            ],

            'profit'=>[
                'type'=>'DECIMAL',
                'constraint'=>'15,2',
                'default'=>0
            ],

            'score'=>[
                'type'=>'DECIMAL',
                'constraint'=>'6,2',
                'default'=>0
            ],

            'recommended'=>[
                'type'=>'TINYINT',
                'constraint'=>1,
                'default'=>0
            ],

            'created_at'=>[
                'type'=>'DATETIME',
                'null'=>true
            ],

            'updated_at'=>[
                'type'=>'DATETIME',
                'null'=>true
            ]

        ]);

        $this->forge->addKey('id',true);

        $this->forge->addKey('shipment_id');

        $this->forge->addKey('vendor_id');

        $this->forge->createTable('shipment_vendor_recommendations');
    }

    public function down()
    {
        $this->forge->dropTable('shipment_vendor_recommendations');
    }
}