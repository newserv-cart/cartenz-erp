<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShipmentWeightVerificationsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('shipment_weight_verifications')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shipment_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'declared_actual_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'declared_volume_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'declared_chargeable_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'verified_actual_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'verified_volume_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'verified_chargeable_weight' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'weight_difference' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0,
            ],
            'declared_colly' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'verified_colly' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 1,
            ],
            'old_grand_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'new_grand_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'package_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'remarks' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'branch_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_by_user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => false,
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
        $this->forge->addKey(
            'shipment_id',
            false,
            false,
            'idx_weight_verification_shipment'
        );
        $this->forge->addKey(
            'verified_by_user_id',
            false,
            false,
            'idx_weight_verification_user'
        );

        $this->forge->createTable('shipment_weight_verifications');
    }

    public function down()
    {
        //
    }
}
