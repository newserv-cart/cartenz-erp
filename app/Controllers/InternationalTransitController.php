<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class InternationalTransitController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $query = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_id',
                's.vendor_manifest_id',
                's.vendor_awb',
                's.vendor_booking_reference',
                's.vendor_service_code',
                's.vendor_booking_at',
                's.total_colly',
                's.chargeable_weight',

                'v.vendor_name',
                'm.manifest_no',
                'c.country_name',
                'sv.service_name',
            ])
            ->join(
                'vendors v',
                'v.id = s.vendor_id',
                'left'
            )
            ->join(
                'manifests m',
                'm.id = s.vendor_manifest_id',
                'left'
            )
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
            ->where(
                's.shipment_status',
                'BOOKED_WITH_VENDOR'
            )
            ->where('s.vendor_awb IS NOT NULL', null, false)
            ->where('s.vendor_awb !=', '')
            ->orderBy('s.vendor_booking_at', 'ASC')
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query international transit gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        return view(
            'warehouse/international_transit/index',
            [
                'title'     => 'International Transit',
                'shipments' => $query->getResultArray(),
            ]
        );
    }

    public function depart(int $shipmentId)
    {
        $departureDate = trim(
            (string) $this->request->getPost('departure_date')
        );

        $departureTime = trim(
            (string) $this->request->getPost('departure_time')
        );

        $originLocation = trim(
            (string) $this->request->getPost(
                'vendor_origin_location'
            )
        );

        $transportReference = strtoupper(
            trim(
                (string) $this->request->getPost(
                    'vendor_transport_reference'
                )
            )
        );

        if ($departureDate === '') {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal keberangkatan wajib diisi.'
                );
        }

        if ($departureTime === '') {
            $departureTime = date('H:i');
        }

        $departureTimestamp = strtotime(
            $departureDate . ' ' . $departureTime
        );

        if ($departureTimestamp === false) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal atau waktu keberangkatan tidak valid.'
                );
        }

        $departureAt = date(
            'Y-m-d H:i:s',
            $departureTimestamp
        );

        if ($originLocation === '') {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi awal transit wajib diisi.'
                );
        }

        if (mb_strlen($originLocation) > 150) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi awal maksimal 150 karakter.'
                );
        }

        if (mb_strlen($transportReference) > 100) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Referensi transport maksimal 100 karakter.'
                );
        }

        $db = db_connect();

        $shipmentQuery = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_id',
                's.vendor_manifest_id',
                's.vendor_awb',
                's.vendor_booking_status',
                's.vendor_booking_at',
                's.vendor_departure_at',

                'v.vendor_name',
                'm.manifest_no',
                'm.status AS manifest_status',
            ])
            ->join(
                'vendors v',
                'v.id = s.vendor_id',
                'left'
            )
            ->join(
                'manifests m',
                'm.id = s.vendor_manifest_id',
                'left'
            )
            ->where('s.id', $shipmentId)
            ->get();

        if ($shipmentQuery === false) {
            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Query shipment gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $shipment = $shipmentQuery->getRowArray();

        if (!$shipment) {
            throw PageNotFoundException::forPageNotFound(
                'Shipment tidak ditemukan.'
            );
        }

        $currentStatus = strtoupper(
            trim(
                (string) (
                    $shipment['shipment_status'] ?? ''
                )
            )
        );

        if ($currentStatus !== 'BOOKED_WITH_VENDOR') {
            return redirect()->back()->with(
                'error',
                'Shipment tidak dapat diberangkatkan. '
                . 'Status saat ini: '
                . ($currentStatus ?: '-')
            );
        }

        if (empty($shipment['vendor_id'])) {
            return redirect()->back()->with(
                'error',
                'Shipment belum memiliki vendor.'
            );
        }

        if (empty($shipment['vendor_awb'])) {
            return redirect()->back()->with(
                'error',
                'Shipment belum memiliki Vendor AWB.'
            );
        }

        if (
            strtoupper(
                (string) (
                    $shipment['vendor_booking_status'] ?? ''
                )
            ) !== 'CONFIRMED'
        ) {
            return redirect()->back()->with(
                'error',
                'Vendor booking belum berstatus CONFIRMED.'
            );
        }

        if (
            strtoupper(
                (string) (
                    $shipment['manifest_status'] ?? ''
                )
            ) !== 'DISPATCHED'
        ) {
            return redirect()->back()->with(
                'error',
                'Vendor Manifest belum berstatus DISPATCHED.'
            );
        }

        if (!empty($shipment['vendor_departure_at'])) {
            return redirect()->back()->with(
                'error',
                'Shipment sudah memiliki waktu keberangkatan.'
            );
        }

        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $updated = $db->table('shipments')
                ->where('id', $shipmentId)
                ->where(
                    'shipment_status',
                    'BOOKED_WITH_VENDOR'
                )
                ->where('vendor_awb IS NOT NULL', null, false)
                ->where('vendor_awb !=', '')
                ->where('vendor_departure_at IS NULL', null, false)
                ->update([
                    'shipment_status'            =>
                        'IN_TRANSIT_INTERNATIONAL',

                    'vendor_departure_at'        =>
                        $departureAt,

                    'vendor_departure_by'        =>
                        $userId,

                    'vendor_origin_location'     =>
                        $originLocation,

                    'vendor_transport_reference' =>
                        $transportReference !== ''
                            ? $transportReference
                            : null,

                    'updated_at'                 =>
                        $now,
                ]);

            if ($updated === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal memperbarui shipment: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            if ($db->affectedRows() !== 1) {
                throw new \RuntimeException(
                    'Shipment sudah diberangkatkan '
                    . 'atau statusnya telah berubah.'
                );
            }

            $vendorName = trim(
                (string) (
                    $shipment['vendor_name']
                    ?? 'Vendor #' . $shipment['vendor_id']
                )
            );

            $description =
                'Shipment diberangkatkan oleh '
                . $vendorName
                . ' dari '
                . $originLocation
                . '. Vendor AWB: '
                . $shipment['vendor_awb']
                . '.';

            if ($transportReference !== '') {
                $description .=
                    ' Transport reference: '
                    . $transportReference
                    . '.';
            }

            $trackingModel = new ShipmentTrackingModel();

            $trackingInserted = $trackingModel->insert([
                'shipment_id'      => $shipmentId,
                'tracking_code'    => $shipment['awb_no'] ?? '',
                'tracking_status'  => 'IN_TRANSIT_INTERNATIONAL',
                'public_status'    => 'IN_TRANSIT',
                'tracking_location'=> $originLocation,
                'tracking_notes'   => $description,
                'event_time'       => $departureAt,
                'created_by'       => $userId,
                'created_at'       => $departureAt,
                'event_type'       => 'IN_TRANSIT_INTERNATIONAL',
                'source_reference' => $shipment['vendor_awb'] ?? '',
                'source_system'    => 'INTERNATIONAL_TRANSIT',
                'is_public'        => 1,
            ], false);

            if ($trackingInserted === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking transit.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi international transit gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(
                    site_url(
                        'warehouse/international-transit'
                    )
                )
                ->with(
                    'success',
                    'Shipment '
                    . ($shipment['awb_no'] ?? '')
                    . ' berhasil ditandai '
                    . 'IN_TRANSIT_INTERNATIONAL.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[INTERNATIONAL TRANSIT FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memproses keberangkatan: '
                    . $e->getMessage()
                );
        }
    }
}
