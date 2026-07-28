<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePodAccessTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            'shipment_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],

            'token_hash' => [
                'type'       => 'CHAR',
                'constraint' => 64,
                'null'       => false,
            ],

            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'created_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],

            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],

            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'revoked_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],

            'last_accessed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            'access_count' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash', false, true, 'uq_pod_access_token_hash');
        $this->forge->addKey('shipment_id', false, false, 'idx_pod_access_shipment');
        $this->forge->addKey('expires_at', false, false, 'idx_pod_access_expiry');
        $this->forge->addKey('revoked_at', false, false, 'idx_pod_access_revoked');

        $this->forge->createTable('pod_access_tokens');
    }

    public function down()
    {
        //
    }
}
