<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentUsersTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('agent_users')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],

            /*
             * agents.id = BIGINT UNSIGNED
             */
            'agent_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],

            /*
             * users.id = INT UNSIGNED
             */
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],

            'role_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'AGENT_USER',
            ],

            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],

            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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
        $this->forge->addKey('agent_id');

        /*
         * Satu akun login hanya terhubung ke satu agent.
         * Satu agent boleh mempunyai banyak akun staf.
         */
        $this->forge->addUniqueKey('user_id');

        $this->forge->addForeignKey(
            'agent_id',
            'agents',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->createTable('agent_users');
    }

    public function down()
    {
        if ($this->db->tableExists('agent_users')) {
            $this->forge->dropTable('agent_users');
        }
    }
}
