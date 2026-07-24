<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AllowGenericOwnerOnCustomerAddresses extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn(
            'customer_addresses',
            [
                'customer_id' => [
                    'name'       => 'customer_id',
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
            ]
        );
    }

    public function down()
    {
        /*
         * Jangan rollback menjadi NOT NULL apabila sudah ada
         * address milik AGENT atau WHITE_LABEL.
         */
    }
}
