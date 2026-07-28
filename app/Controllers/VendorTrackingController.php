<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VendorTrackingController extends BaseController
{
    /**
     * Status yang boleh dilanjutkan dari status sekarang.
     */
    private array $statusTransitions = [
        'IN_TRANSIT_INTERNATIONAL' => [
            'ARRIVED_HUB',
            'CUSTOMS_EXCEPTION',
        ],

        'ARRIVED_HUB' => [
            'IN_TRANSIT_HUB',
            'HANDED_TO_LAST_MILE',
            'CUSTOMS_EXCEPTION',
        ],

        'IN_TRANSIT_HUB' => [
            'HANDED_TO_LAST_MILE',
            'CUSTOMS_EXCEPTION',
        ],

        'CUSTOMS_EXCEPTION' => [
            'ARRIVED_HUB',
            'IN_TRANSIT_HUB',
            'HANDED_TO_LAST_MILE',
            'RETURN_TO_SENDER',
        ],

        'HANDED_TO_LAST_MILE' => [
            'DELIVERED',
            'RETURN_TO_SENDER',
        ],
    ];

    public function index()
    {
        $search = strtoupper(
            trim((string) $this->request->getGet('q'))
        );

        $shipment = null;
        $tracking = [];
        $allowedNextStatuses = [];

        if ($search !== '') {
            $db = db_connect();

            $query = $db->table('shipments s')
                ->select([
                    's.id',
                    's.awb_no',
                    's.shipment_status',
                    's.vendor_id',
                    's.vendor_awb',
                    's.vendor_booking_reference',
                    's.vendor_origin_location',
                    's.vendor_transport_reference',
                    's.total_colly',
                    's.actual_weight',
                    's.volume_weight',
                    's.chargeable_weight',
                    's.country_id',
                    's.service_id',

                    'v.vendor_name',
                    'c.country_name',
                    'sv.service_name',
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
                ->groupStart()
                    ->where('UPPER(s.awb_no)', $search)
                    ->orWhere('UPPER(s.vendor_awb)', $search)
                ->groupEnd()
                ->get();

            if ($query === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Query vendor tracking gagal: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            $shipment = $query->getRowArray();

            if ($shipment) {
                $currentStatus = strtoupper(
                    trim(
                        (string) (
                            $shipment['shipment_status'] ?? ''
                        )
                    )
                );

                $allowedNextStatuses =
                    $this->statusTransitions[$currentStatus] ?? [];

                $trackingQuery = $db
                    ->table('shipment_tracking')
                    ->select([
                        'tracking_status AS status',
                        'tracking_location AS location',
                        'tracking_notes AS description',
                        'created_at',
                    ])
                    ->where(
                        'shipment_id',
                        (int) $shipment['id']
                    )
                    ->orderBy('created_at', 'DESC')
                    ->get();

                if ($trackingQuery !== false) {
                    $tracking = $trackingQuery->getResultArray();
                }
            }
        }

        return view(
            'warehouse/vendor_tracking/index',
            [
                'title'               => 'Vendor Tracking Update',
                'search'              => $search,
                'shipment'            => $shipment,
                'tracking'            => $tracking,
                'allowedNextStatuses' => $allowedNextStatuses,
            ]
        );
    }

    public function update(int $shipmentId)
    {
        $nextStatus = strtoupper(
            trim(
                (string) $this->request->getPost(
                    'shipment_status'
                )
            )
        );

        $location = trim(
            (string) $this->request->getPost('location')
        );

        $eventAtInput = trim(
            (string) $this->request->getPost('event_at')
        );

        $description = trim(
            (string) $this->request->getPost('description')
        );

        if ($nextStatus === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Status berikutnya wajib dipilih.');
        }

        if ($location === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Lokasi tracking wajib diisi.');
        }

        if (mb_strlen($location) > 150) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Lokasi tracking maksimal 150 karakter.'
                );
        }

        if ($eventAtInput === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Waktu kejadian wajib diisi.');
        }

        $eventTimestamp = strtotime($eventAtInput);

        if ($eventTimestamp === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Waktu kejadian tidak valid.');
        }

        $eventAt = date('Y-m-d H:i:s', $eventTimestamp);

        if (mb_strlen($description) > 1000) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Keterangan maksimal 1.000 karakter.'
                );
        }

        $db = db_connect();

        $shipmentQuery = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_id',
                's.vendor_awb',
                'v.vendor_name',
            ])
            ->join(
                'vendors v',
                'v.id = s.vendor_id',
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

        $allowedStatuses =
            $this->statusTransitions[$currentStatus] ?? [];

        if (!in_array($nextStatus, $allowedStatuses, true)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Perubahan status dari '
                    . $currentStatus
                    . ' ke '
                    . $nextStatus
                    . ' tidak diizinkan.'
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

        $vendorName = trim(
            (string) (
                $shipment['vendor_name']
                ?? 'Vendor #' . $shipment['vendor_id']
            )
        );

        if ($description === '') {
            $description = $this->defaultDescription(
                $nextStatus,
                $vendorName,
                (string) $shipment['vendor_awb']
            );
        }

        $userId = (int) (auth()->id() ?? 0);
        $now    = date('Y-m-d H:i:s');

        $db->transBegin();

        try {
            $updated = $db->table('shipments')
                ->where('id', $shipmentId)
                ->where('shipment_status', $currentStatus)
                ->update([
                    'shipment_status' => $nextStatus,
                    'updated_at'      => $now,
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
                    'Status shipment sudah berubah. '
                    . 'Silakan muat ulang halaman.'
                );
            }

            $publicStatus = match ($nextStatus) {
                'DELIVERED' => 'DELIVERED',
                default     => 'IN_TRANSIT',
            };

            $trackingModel = new ShipmentTrackingModel();

            $trackingInserted = $trackingModel->insert([
                'shipment_id'      => $shipmentId,
                'tracking_code'    => $shipment['awb_no'] ?? '',
                'tracking_status'  => $nextStatus,
                'public_status'    => $publicStatus,
                'tracking_location'=> $location,
                'tracking_notes'   => $description,
                'event_time'       => $eventAt,
                'created_by'       => $userId,
                'created_at'       => $now,
                'event_type'       => $nextStatus,
                'source_reference' => $shipment['vendor_awb'] ?? '',
                'source_system'    => 'VENDOR_TRACKING',
                'is_public'        => 1,
            ], false);

            if ($trackingInserted === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi vendor tracking gagal.'
                );
            }

            $db->transCommit();

            return redirect()->to(
                site_url(
                    'warehouse/vendor-tracking?q='
                    . urlencode(
                        (string) $shipment['awb_no']
                    )
                )
            )->with(
                'success',
                'Shipment '
                . ($shipment['awb_no'] ?? '')
                . ' berhasil diperbarui menjadi '
                . $nextStatus
                . '.'
            );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[VENDOR TRACKING UPDATE FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui tracking: '
                    . $e->getMessage()
                );
        }
    }

    private function defaultDescription(
        string $status,
        string $vendorName,
        string $vendorAwb
    ): string {
        return match ($status) {
            'ARRIVED_HUB' =>
                'Shipment tiba di hub tujuan melalui '
                . $vendorName
                . '. Vendor AWB: '
                . $vendorAwb
                . '.',

            'IN_TRANSIT_HUB' =>
                'Shipment sedang diproses dan diteruskan '
                . 'melalui jaringan hub vendor.',

            'CUSTOMS_EXCEPTION' =>
                'Shipment mengalami kendala pada proses customs. '
                . 'Pemeriksaan atau dokumen tambahan diperlukan.',

            'HANDED_TO_LAST_MILE' =>
                'Shipment telah diserahkan kepada jaringan '
                . 'last-mile untuk proses pengantaran.',

            'DELIVERED' =>
                'Shipment berhasil diserahkan kepada penerima.',

            'RETURN_TO_SENDER' =>
                'Shipment diproses untuk dikembalikan '
                . 'kepada pengirim.',

            default =>
                'Status shipment diperbarui oleh petugas.',
        };
    }
}
