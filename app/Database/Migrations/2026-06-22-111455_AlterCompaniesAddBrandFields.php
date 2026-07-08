<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCompaniesAddBrandFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('companies', [

            'invoice_prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'company_name'
            ],

            'tracking_domain' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'invoice_prefix'
            ],

            'tagline' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tracking_domain'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('companies', [
            'invoice_prefix',
            'tracking_domain',
            'tagline'
        ]);
    }
}