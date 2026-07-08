<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGoGreenRulesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id'=>[
                'type'=>'BIGINT',
                'constraint'=>20,
                'unsigned'=>true,
                'auto_increment'=>true
            ],

            'service_id'=>[
                'type'=>'BIGINT',
                'constraint'=>20,
                'unsigned'=>true
            ],

            'gogreen_zone_id'=>[
                'type'=>'BIGINT',
                'constraint'=>20,
                'unsigned'=>true
            ],

            'customer_type'=>[
                'type'=>'ENUM',
                'constraint'=>['FIXED','PER_KG'],
                'default'=>'PER_KG'
            ],

            'customer_value'=>[
                'type'=>'DECIMAL',
                'constraint'=>'15,2',
                'default'=>0
            ],

            'vendor_type'=>[
                'type'=>'ENUM',
                'constraint'=>['FIXED','PER_KG'],
                'default'=>'PER_KG'
            ],

            'vendor_value'=>[
                'type'=>'DECIMAL',
                'constraint'=>'15,2',
                'default'=>0
            ],

            'effective_from'=>[
                'type'=>'DATE',
                'null'=>true
            ],

            'effective_to'=>[
                'type'=>'DATE',
                'null'=>true
            ],

            'status'=>[
                'type'=>'TINYINT',
                'constraint'=>1,
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

        $this->forge->addPrimaryKey('id');

        $this->forge->addForeignKey(
            'service_id',
            'services',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'gogreen_zone_id',
            'gogreen_zones',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('gogreen_rules');
    }

    public function down()
    {
        $this->forge->dropTable('gogreen_rules',true);
    }
}