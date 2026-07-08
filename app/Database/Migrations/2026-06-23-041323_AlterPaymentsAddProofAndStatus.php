<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterPaymentsAddProofAndStatus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('payments', [

            'proof_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'remarks'
            ],

            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'PENDING',
                'after'      => 'proof_file'
            ],

        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('payments', 'proof_file');
        $this->forge->dropColumn('payments', 'status');
    }
}