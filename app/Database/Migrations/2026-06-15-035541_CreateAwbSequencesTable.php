<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAwbSequencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([

            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'awb_prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],

            'awb_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 4,
            ],

            'awb_month' => [
                'type'       => 'VARCHAR',
                'constraint' => 2,
            ],

            'last_number' => [
                'type'    => 'INT',
                'default' => 0,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['awb_prefix','awb_year','awb_month']);

        $this->forge->createTable('awb_sequences');
    }

    public function down()
    {
        $this->forge->dropTable('awb_sequences');
    }
}