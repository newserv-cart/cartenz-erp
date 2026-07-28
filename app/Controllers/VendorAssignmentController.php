<?php

namespace App\Controllers;

use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;

class VendorAssignmentController extends BaseController
{
    protected ShipmentModel $shipmentModel;

    public function __construct()
    {
        $this->shipmentModel = new ShipmentModel();
    }

    public function index()
    {
        $db = db_connect();

        $shipments = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.owner_type',
                's.owner_id',
                's.customer_id',
                's.agent_id',
                's.country_id',
                's.service_id',
                's.package_type_id',
                's.total_colly',
                's.actual_weight',
                's.volume_weight',
                's.chargeable_weight',
                's.total_charge',
                's.shipment_status',

                'c.country_name',
                'sv.service_name',
                'pt.package_type_name AS package_name',
            ])
            ->join(
                'countries c',
                'c.id = s.country_id',
                'left'
            )
            ->join(
                'services sv',
                'sv.id = s.service_id',
                'left'
            )
            ->join(
                'package_types pt',
                'pt.id = s.package_type_id',
                'left'
            )
            ->where(
                's.shipment_status',
                'RECEIVED_AT_MAIN_WAREHOUSE'
            )
            ->where('s.vendor_id IS NULL', null, false)
            ->orderBy('s.id', 'DESC')
            ->get()
            ->getResultArray();

        $vendors = $db->table('vendors')
            ->where('status', 1)
            ->orderBy('vendor_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('warehouse/vendor_assignment/index', [
            'title'     => 'Vendor Assignment',
            'shipments' => $shipments,
            'vendors'   => $vendors,
        ]);
    }

    public function assign()
    {
        $shipmentIds = $this->request->getPost('shipment_ids');
        $vendorId    = (int) $this->request->getPost('vendor_id');

        if (!is_array($shipmentIds)) {
            $shipmentIds = [];
        }

        $shipmentIds = array_values(
            array_unique(
                array_filter(
                    array_map('intval', $shipmentIds),
                    static fn (int $id): bool => $id > 0
                )
            )
        );

        if (empty($shipmentIds)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Pilih minimal satu shipment.'
                );
        }

        if ($vendorId <= 0) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Vendor wajib dipilih.'
                );
        }

        $db = db_connect();

        $vendor = $db->table('vendors')
            ->where('id', $vendorId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (!$vendor) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Vendor tidak ditemukan atau tidak aktif.'
                );
        }

        $eligibleShipments = $db->table('shipments')
            ->select([
                'id',
                'awb_no',
                'shipment_status',
                'vendor_id',
            ])
            ->whereIn('id', $shipmentIds)
            ->where(
                'shipment_status',
                'RECEIVED_AT_MAIN_WAREHOUSE'
            )
            ->where('vendor_id IS NULL', null, false)
            ->get()
            ->getResultArray();

        if (count($eligibleShipments) !== count($shipmentIds)) {
            return redirect()->back()
                ->with(
                    'error',
                    'Sebagian shipment sudah berubah status, '
                    . 'sudah memiliki vendor, atau tidak valid.'
                );
        }

        $userId = (int) (auth()->id() ?? 0);
        $now    = date('Y-m-d H:i:s');

        $vendorName = trim(
            (string) (
                $vendor['vendor_name']
                ?? $vendor['name']
                ?? 'Vendor #' . $vendorId
            )
        );

        $db->transBegin();

        try {
            $updated = $db->table('shipments')
                ->whereIn('id', $shipmentIds)
                ->where(
                    'shipment_status',
                    'RECEIVED_AT_MAIN_WAREHOUSE'
                )
                ->where('vendor_id IS NULL', null, false)
                ->update([
                    'vendor_id'         => $vendorId,
                    'shipment_status'   => 'ASSIGNED_TO_VENDOR',
                    'vendor_assigned_at'=> $now,
                    'vendor_assigned_by'=> $userId,
                    'updated_at'        => $now,
                ]);

            if ($updated === false) {
                $dbError = $db->error();

                throw new \RuntimeException(
                    'Gagal memperbarui shipment: '
                    . ($dbError['message']
                        ?? 'Unknown database error')
                );
            }

            if ($db->affectedRows() !== count($shipmentIds)) {
                throw new \RuntimeException(
                    'Jumlah shipment yang berhasil diperbarui '
                    . 'tidak sesuai. Proses dibatalkan.'
                );
            }

            foreach ($eligibleShipments as $shipment) {
                $trackingModel = new ShipmentTrackingModel();

                $trackingInserted = $trackingModel->insert([
                    'shipment_id'      => (int) $shipment['id'],
                    'tracking_code'    => $shipment['awb_no'] ?? '',
                    'tracking_status'  => 'ASSIGNED_TO_VENDOR',
                    'public_status'    => 'IN_TRANSIT',
                    'tracking_location'=> 'Main Warehouse',
                    'tracking_notes'   =>
                        'Shipment dialokasikan kepada vendor '
                        . $vendorName
                        . ' untuk proses pengiriman berikutnya.',
                    'event_time'       => $now,
                    'created_by'       => $userId,
                    'created_at'       => $now,
                    'event_type'       => 'ASSIGNED_TO_VENDOR',
                    'source_reference' => $shipment['awb_no'] ?? '',
                    'source_system'    => 'VENDOR_ASSIGNMENT',
                    'is_public'        => 1,
                ], false);

                if ($trackingInserted === false) {
                    throw new \RuntimeException(
                        'Gagal menyimpan tracking shipment '
                        . ($shipment['awb_no'] ?? '')
                    );
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi vendor assignment gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('warehouse/vendor-assignment'))
                ->with(
                    'success',
                    count($shipmentIds)
                    . ' shipment berhasil dialokasikan ke '
                    . $vendorName
                    . '.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[VENDOR ASSIGNMENT FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal melakukan vendor assignment: '
                    . $e->getMessage()
                );
        }
    }
}
