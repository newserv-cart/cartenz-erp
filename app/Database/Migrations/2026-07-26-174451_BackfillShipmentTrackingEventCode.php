<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillShipmentTrackingEventCode extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('shipment_tracking')) {
            return;
        }

        $this->db->query(
            "UPDATE shipment_tracking
             SET event_code = public_status
             WHERE event_code IS NULL
               AND public_status IS NOT NULL"
        );

        $this->db->query(
            "UPDATE shipment_tracking
             SET event_code = 'PICKUP_REQUESTED'
             WHERE event_code = 'CREATED'
               AND (
                   tracking_location LIKE '%Pickup Address%'
                   OR tracking_notes LIKE '%Permintaan pickup%'
                   OR tracking_notes LIKE '%Customer meminta%'
               )"
        );

        $this->db->query(
            "UPDATE shipment_tracking
             SET public_status = 'PICKUP_REQUESTED'
             WHERE event_code = 'PICKUP_REQUESTED'
               AND public_status = 'CREATED'"
        );
    }

    public function down()
    {
        //
    }
}
