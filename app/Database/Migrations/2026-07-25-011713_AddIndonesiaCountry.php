<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndonesiaCountry extends Migration
{
    public function up()
    {
        $db = $this->db;

        $region = $db
            ->table('regions')
            ->where('region_code', 'ASIA')
            ->get()
            ->getRowArray();

        if (empty($region)) {
            $region = $db
                ->table('regions')
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        $regionId = !empty($region) ? (int) $region['id'] : 1;

        $expressZone = $db
            ->table('express_zones')
            ->where('zone_code', 'Z1')
            ->get()
            ->getRowArray();

        if (empty($expressZone)) {
            $expressZone = $db
                ->table('express_zones')
                ->orderBy('id', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();
        }

        $expressZoneId = !empty($expressZone) ? (int) $expressZone['id'] : null;

        $exists = $db
            ->table('countries')
            ->where('country_code', 'ID')
            ->countAllResults();

        if ($exists > 0) {
            return;
        }

        $db->table('countries')->insert([
            'region_id'       => $regionId,
            'express_zone_id' => $expressZoneId,
            'country_code'    => 'ID',
            'country_name'    => 'Indonesia',
            'status'          => 1,
        ]);
    }

    public function down()
    {
        $this->db
            ->table('countries')
            ->where('country_code', 'ID')
            ->delete();
    }
}
