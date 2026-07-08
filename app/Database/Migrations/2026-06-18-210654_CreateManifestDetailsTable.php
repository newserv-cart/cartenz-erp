<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManifestDetailsTable extends Migration
{
  public function up()
{
    $this->forge->addField([

        'id' => [
            'type'           => 'BIGINT',
            'constraint'     => 20,
            'unsigned'       => true,
            'auto_increment' => true,
        ],

        'manifest_id' => [
            'type'       => 'BIGINT',
            'constraint' => 20,
            'unsigned'   => true,
        ],

        'shipment_id' => [
            'type'       => 'BIGINT',
            'constraint' => 20,
            'unsigned'   => true,
        ],

        'awb_no' => [
            'type'       => 'VARCHAR',
            'constraint' => 50,
        ],

        'actual_weight' => [
            'type'       => 'DECIMAL',
            'constraint' => '12,2',
            'default'    => 0,
        ],

        'created_at' => [
            'type' => 'DATETIME',
            'null' => true,
        ],

    ]);

    $this->forge->addKey('id', true);

    $this->forge->addKey('manifest_id');

    $this->forge->addKey('shipment_id');

    $this->forge->addKey('awb_no');

    $this->forge->createTable('manifest_details');
}

    public function down()
{
    $this->forge->dropTable('manifest_details');
}
}
