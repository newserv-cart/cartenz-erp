<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VendorBookingController extends BaseController
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
                's.vendor_booking_status',
                's.total_colly',
                's.chargeable_weight',
                's.vendor_cost',

                'v.vendor_name',
                'c.country_name',
                'sv.service_name',
                'm.manifest_no',
            ])
            ->join(
                'vendors v',
                'v.id = s.vendor_id',
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
            ->join(
                'manifests m',
                'm.id = s.vendor_manifest_id',
                'left'
            )
            ->where(
                's.shipment_status',
                'HANDED_OVER_TO_VENDOR'
            )
            ->where('s.vendor_id IS NOT NULL', null, false)
            ->where('s.vendor_manifest_id IS NOT NULL', null, false)
            ->groupStart()
                ->where('s.vendor_awb IS NULL', null, false)
                ->orWhere('s.vendor_awb', '')
            ->groupEnd()
            ->orderBy('s.vendor_handover_at', 'ASC')
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query vendor booking gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        return view('warehouse/vendor_bookings/index', [
            'title'     => 'Vendor Booking',
            'shipments' => $query->getResultArray(),
        ]);
    }

    public function book(int $shipmentId)
    {
        $vendorAwb = strtoupper(
            trim((string) $this->request->getPost('vendor_awb'))
        );

        $bookingReference = strtoupper(
            trim(
                (string) $this->request->getPost(
                    'vendor_booking_reference'
                )
            )
        );

        $vendorServiceCode = strtoupper(
            trim(
                (string) $this->request->getPost(
                    'vendor_service_code'
                )
            )
        );

        if ($vendorAwb === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vendor AWB wajib diisi.');
        }

        if (strlen($vendorAwb) > 100) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Vendor AWB maksimal 100 karakter.'
                );
        }

        $db = db_connect();

        $duplicateQuery = $db->table('shipments')
            ->select('id, awb_no')
            ->where('vendor_awb', $vendorAwb)
            ->where('id !=', $shipmentId)
            ->get();

        if ($duplicateQuery === false) {
            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Gagal memeriksa Vendor AWB: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $duplicate = $duplicateQuery->getRowArray();

        if ($duplicate) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Vendor AWB ' . $vendorAwb
                    . ' sudah dipakai oleh shipment '
                    . ($duplicate['awb_no'] ?? '') . '.'
                );
        }

        $shipmentQuery = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_id',
                's.vendor_manifest_id',
                's.vendor_awb',
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

        if (
            strtoupper(
                (string) ($shipment['shipment_status'] ?? '')
            ) !== 'HANDED_OVER_TO_VENDOR'
        ) {
            return redirect()->back()->with(
                'error',
                'Shipment tidak dapat dibooking. Status saat ini: '
                . ($shipment['shipment_status'] ?? '-')
            );
        }

        if (empty($shipment['vendor_id'])) {
            return redirect()->back()->with(
                'error',
                'Shipment belum memiliki vendor.'
            );
        }

        if (empty($shipment['vendor_manifest_id'])) {
            return redirect()->back()->with(
                'error',
                'Shipment belum terhubung ke Vendor Manifest.'
            );
        }

        if (
            strtoupper(
                (string) ($shipment['manifest_status'] ?? '')
            ) !== 'DISPATCHED'
        ) {
            return redirect()->back()->with(
                'error',
                'Vendor Manifest belum berstatus DISPATCHED.'
            );
        }

        if (!empty($shipment['vendor_awb'])) {
            return redirect()->back()->with(
                'error',
                'Shipment sudah memiliki Vendor AWB.'
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
                    'HANDED_OVER_TO_VENDOR'
                )
                ->groupStart()
                    ->where('vendor_awb IS NULL', null, false)
                    ->orWhere('vendor_awb', '')
                ->groupEnd()
                ->update([
                    'vendor_awb'               => $vendorAwb,
                    'vendor_booking_reference' => $bookingReference !== ''
                        ? $bookingReference
                        : null,
                    'vendor_service_code'      => $vendorServiceCode !== ''
                        ? $vendorServiceCode
                        : null,
                    'vendor_booking_status'    => 'CONFIRMED',
                    'vendor_booking_mode'      => 'MANUAL',
                    'vendor_booking_at'        => $now,
                    'vendor_booking_by'        => $userId,
                    'shipment_status'          => 'BOOKED_WITH_VENDOR',
                    'updated_at'               => $now,
                ]);

            if ($updated === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal menyimpan vendor booking: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            if ($db->affectedRows() !== 1) {
                throw new \RuntimeException(
                    'Shipment sudah dibooking atau statusnya berubah.'
                );
            }

            $vendorName = trim(
                (string) (
                    $shipment['vendor_name']
                    ?? 'Vendor #' . $shipment['vendor_id']
                )
            );

            $description =
                'Vendor booking dikonfirmasi untuk '
                . $vendorName
                . '. Vendor AWB: '
                . $vendorAwb
                . '.';

            if ($bookingReference !== '') {
                $description .=
                    ' Booking reference: '
                    . $bookingReference
                    . '.';
            }

            $trackingModel = new ShipmentTrackingModel();

            $trackingInserted = $trackingModel->insert([
                'shipment_id'      => $shipmentId,
                'tracking_code'    => $shipment['awb_no'] ?? '',
                'tracking_status'  => 'BOOKED_WITH_VENDOR',
                'public_status'    => 'IN_TRANSIT',
                'tracking_location'=> $vendorName,
                'tracking_notes'   => $description,
                'event_time'       => $now,
                'created_by'       => $userId,
                'created_at'       => $now,
                'event_type'       => 'BOOKED_WITH_VENDOR',
                'source_reference' => $vendorAwb,
                'source_system'    => 'VENDOR_BOOKING',
                'is_public'        => 1,
            ], false);

            if ($trackingInserted === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking vendor booking.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi vendor booking gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('warehouse/vendor-bookings'))
                ->with(
                    'success',
                    'Shipment '
                    . ($shipment['awb_no'] ?? '')
                    . ' berhasil dibooking. Vendor AWB: '
                    . $vendorAwb
                    . '.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[VENDOR BOOKING FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal melakukan vendor booking: '
                    . $e->getMessage()
                );
        }
    }
}
