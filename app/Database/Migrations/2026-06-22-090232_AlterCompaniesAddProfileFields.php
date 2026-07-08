<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterCompaniesAddProfileFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('companies', [

            'address' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'legal_name'
            ],

            'city' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'address'
            ],

            'country' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'after' => 'city'
            ],

            'tax_name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'country'
            ],

            'tax_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'tax_name'
            ],

            'vat_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'tax_number'
            ],

            'invoice_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'vat_number'
            ]

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('companies', [
            'address',
            'city',
            'country',
            'tax_name',
            'tax_number',
            'vat_number',
            'invoice_notes'
        ]);
    }
}