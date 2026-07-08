<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommodityChargeRulesTable extends Migration
{
   public function up()
{
    $this->forge->addField([

        'id'=>[
            'type'=>'INT',
            'constraint'=>11,
            'unsigned'=>true,
            'auto_increment'=>true
        ],

        'commodity_type_id'=>[
            'type'=>'INT',
            'constraint'=>11,
            'unsigned'=>true
        ],

        // CUSTOMER
        'customer_insurance'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        'customer_packing'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        'customer_additional'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        // VENDOR
        'vendor_insurance'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        'vendor_packing'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        'vendor_additional'=>[
            'type'=>'DECIMAL',
            'constraint'=>'18,2',
            'default'=>0
        ],

        'status'=>[
            'type'=>'TINYINT',
            'default'=>1
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

    $this->forge->createTable('commodity_charge_rules');
}

    public function down()
    {
        //
    }
}
