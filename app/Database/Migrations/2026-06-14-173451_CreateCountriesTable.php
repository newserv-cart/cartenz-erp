<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCountriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],

            'region_id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
            ],

            'country_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],

            'country_name' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
            ],

            'status' => [
                'type' => 'TINYINT',
                'default' => 1,
            ],

            'created_at DATETIME NULL',
            'updated_at DATETIME NULL',
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('region_id');

        $this->forge->addForeignKey(
            'region_id',
            'regions',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('countries');
    }

    public function down()
    {
        $this->forge->dropTable('countries');
    }
}