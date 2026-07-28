<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class VendorManifestController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $manifests = $db->table('manifests m')
            ->select([
                'm.*',
                'v.vendor_name',
                'u.username AS created_by_username',
            ])
            ->join(
                'vendors v',
                'v.id = m.vendor_id',
                'left'
            )
            ->join(
                'users u',
                'u.id = m.created_by',
                'left'
            )
            ->where('m.manifest_type', 'VENDOR')
            ->orderBy('m.id', 'DESC')
            ->get()
            ->getResultArray();

        $vendors = $db->table('vendors')
            ->select([
                'vendors.id',
                'vendors.vendor_name',
            ])
            ->where('vendors.status', 1)
            ->where(
                'EXISTS (
                    SELECT 1
                    FROM shipments s
                    WHERE s.vendor_id = vendors.id
                      AND s.shipment_status = "ASSIGNED_TO_VENDOR"
                      AND s.vendor_manifest_id IS NULL
                )',
                null,
                false
            )
            ->orderBy('vendors.vendor_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('warehouse/vendor_manifests/index', [
            'title'     => 'Vendor Manifests',
            'manifests' => $manifests,
            'vendors'   => $vendors,
        ]);
    }

    public function store()
    {
        $vendorId = (int) $this->request->getPost('vendor_id');

        if ($vendorId <= 0) {
            return redirect()->back()
                ->with('error', 'Vendor wajib dipilih.');
        }

        $db = db_connect();

        $vendor = $db->table('vendors')
            ->where('id', $vendorId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (!$vendor) {
            return redirect()->back()
                ->with('error', 'Vendor tidak ditemukan atau tidak aktif.');
        }

        $openManifest = $db->table('manifests')
            ->where('manifest_type', 'VENDOR')
            ->where('vendor_id', $vendorId)
            ->where('status', 'OPEN')
            ->get()
            ->getRowArray();

        if ($openManifest) {
            return redirect()->to(
                site_url(
                    'warehouse/vendor-manifests/' . $openManifest['id']
                )
            )->with(
                'error',
                'Vendor ini masih mempunyai manifest OPEN.'
            );
        }

        $manifestNo = 'VMF' . date('ymdHis');

        $inserted = $db->table('manifests')->insert([
            'manifest_no'       => $manifestNo,
            'manifest_type'     => 'VENDOR',
            'vendor_id'         => $vendorId,
            'status'            => 'OPEN',
            'total_shipment'    => 0,
            'total_colly'       => 0,
            'total_weight'      => 0,
            'total_vendor_cost' => 0,
            'created_by'        => (int) (auth()->id() ?? 0),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Gagal membuat vendor manifest: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $manifestId = (int) $db->insertID();

        return redirect()->to(
            site_url('warehouse/vendor-manifests/' . $manifestId)
        )->with(
            'success',
            'Vendor Manifest ' . $manifestNo . ' berhasil dibuat.'
        );
    }

    public function show(int $manifestId)
    {
        $db = db_connect();

        $manifest = $db->table('manifests m')
            ->select([
                'm.*',
                'v.vendor_name',
            ])
            ->join(
                'vendors v',
                'v.id = m.vendor_id',
                'left'
            )
            ->where('m.id', $manifestId)
            ->where('m.manifest_type', 'VENDOR')
            ->get()
            ->getRowArray();

        if (!$manifest) {
            throw PageNotFoundException::forPageNotFound(
                'Vendor manifest tidak ditemukan.'
            );
        }

        $details = $db->table('manifest_details md')
            ->select([
                'md.id AS detail_id',
                'md.shipment_id',
                's.awb_no',
                's.total_colly',
                's.chargeable_weight',
                's.total_charge',
                's.vendor_cost',
                's.profit_amount',
                's.shipment_status',
                'c.country_name',
                'sv.service_name',
            ])
            ->join(
                'shipments s',
                's.id = md.shipment_id'
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
            ->where('md.manifest_id', $manifestId)
            ->orderBy('md.id', 'ASC')
            ->get()
            ->getResultArray();

        $eligibleShipments = [];

        if (($manifest['status'] ?? '') === 'OPEN') {
            $eligibleShipments = $db->table('shipments s')
                ->select([
                    's.id',
                    's.awb_no',
                    's.total_colly',
                    's.chargeable_weight',
                    's.total_charge',
                    's.vendor_cost',
                    's.profit_amount',
                    'c.country_name',
                    'sv.service_name',
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
                ->where(
                    's.vendor_id',
                    (int) $manifest['vendor_id']
                )
                ->where(
                    's.shipment_status',
                    'ASSIGNED_TO_VENDOR'
                )
                ->where(
                    's.vendor_manifest_id IS NULL',
                    null,
                    false
                )
                ->orderBy('s.id', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('warehouse/vendor_manifests/show', [
            'title'             => 'Vendor Manifest',
            'manifest'          => $manifest,
            'details'           => $details,
            'eligibleShipments' => $eligibleShipments,
        ]);
    }

    public function addShipment(int $manifestId)
    {
        $shipmentIds = $this->request->getPost('shipment_ids');

        if (!is_array($shipmentIds)) {
            $shipmentIds = [];
        }

        $shipmentIds = array_values(array_unique(array_filter(
            array_map('intval', $shipmentIds),
            static fn (int $id): bool => $id > 0
        )));

        if (empty($shipmentIds)) {
            return redirect()->back()
                ->with('error', 'Pilih minimal satu shipment.');
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $manifest = $db->table('manifests')
                ->where('id', $manifestId)
                ->where('manifest_type', 'VENDOR')
                ->where('status', 'OPEN')
                ->get()
                ->getRowArray();

            if (!$manifest) {
                throw new \RuntimeException(
                    'Manifest tidak ditemukan atau sudah tidak OPEN.'
                );
            }

            $shipments = $db->table('shipments')
                ->whereIn('id', $shipmentIds)
                ->where(
                    'vendor_id',
                    (int) $manifest['vendor_id']
                )
                ->where(
                    'shipment_status',
                    'ASSIGNED_TO_VENDOR'
                )
                ->where(
                    'vendor_manifest_id IS NULL',
                    null,
                    false
                )
                ->get()
                ->getResultArray();

            if (count($shipments) !== count($shipmentIds)) {
                throw new \RuntimeException(
                    'Sebagian shipment tidak valid, berbeda vendor, '
                    . 'atau sudah masuk manifest.'
                );
            }

            foreach ($shipments as $shipment) {
                $duplicate = $db->table('manifest_details')
                    ->where('manifest_id', $manifestId)
                    ->where('shipment_id', $shipment['id'])
                    ->countAllResults();

                if ($duplicate > 0) {
                    throw new \RuntimeException(
                        'AWB ' . $shipment['awb_no']
                        . ' sudah ada di manifest.'
                    );
                }

                $inserted = $db->table('manifest_details')->insert([
                    'manifest_id' => $manifestId,
                    'shipment_id' => (int) $shipment['id'],
                    'awb_no'      => $shipment['awb_no'] ?? '',
                    'actual_weight' => (float) (
                        $shipment['chargeable_weight'] ?? 0
                    ),
                    'previous_shipment_status' =>
                        $shipment['shipment_status'] ?? null,
                    'added_at'    => date('Y-m-d H:i:s'),
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);

                if (!$inserted) {
                    throw new \RuntimeException(
                        'Gagal menambahkan AWB '
                        . $shipment['awb_no']
                        . ' ke manifest.'
                    );
                }

                $db->table('shipments')
                    ->where('id', $shipment['id'])
                    ->update([
                        'vendor_manifest_id' => $manifestId,
                        'updated_at'         => date('Y-m-d H:i:s'),
                    ]);
            }

            $this->recalculateManifest($manifestId);

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penambahan shipment gagal.'
                );
            }

            $db->transCommit();

            return redirect()->back()->with(
                'success',
                count($shipmentIds)
                . ' shipment berhasil ditambahkan ke manifest.'
            );
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                'Gagal menambahkan shipment: ' . $e->getMessage()
            );
        }
    }

    public function removeShipment(
        int $manifestId,
        int $detailId
    ) {
        $db = db_connect();
        $db->transBegin();

        try {
            $detail = $db->table('manifest_details')
                ->where('id', $detailId)
                ->where('manifest_id', $manifestId)
                ->get()
                ->getRowArray();

            if (!$detail) {
                throw new \RuntimeException(
                    'Detail manifest tidak ditemukan.'
                );
            }

            $db->table('manifest_details')
                ->where('id', $detailId)
                ->delete();

            $db->table('shipments')
                ->where('id', (int) $detail['shipment_id'])
                ->update([
                    'vendor_manifest_id' => null,
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);

            $this->recalculateManifest($manifestId);

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi penghapusan shipment gagal.'
                );
            }

            $db->transCommit();

            return redirect()->back()->with(
                'success',
                'Shipment berhasil dihapus dari manifest.'
            );
        } catch (\Throwable $e) {
            $db->transRollback();

            return redirect()->back()->with(
                'error',
                'Gagal menghapus shipment: ' . $e->getMessage()
            );
        }
    }

    public function dispatch(int $manifestId)
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $manifest = $db->table('manifests')
                ->where('id', $manifestId)
                ->where('manifest_type', 'VENDOR')
                ->where('status', 'OPEN')
                ->get()
                ->getRowArray();

            if (!$manifest) {
                throw new \RuntimeException(
                    'Manifest tidak ditemukan atau sudah diberangkatkan.'
                );
            }

            $shipments = $db->table('manifest_details md')
                ->select([
                    's.id',
                    's.awb_no',
                    's.shipment_status',
                    's.vendor_id',
                ])
                ->join(
                    'shipments s',
                    's.id = md.shipment_id'
                )
                ->where('md.manifest_id', $manifestId)
                ->get()
                ->getResultArray();

            if (empty($shipments)) {
                throw new \RuntimeException(
                    'Manifest masih kosong.'
                );
            }

            $shipmentIds = array_column($shipments, 'id');

            $now    = date('Y-m-d H:i:s');
            $userId = (int) (auth()->id() ?? 0);

            $updated = $db->table('shipments')
                ->whereIn('id', $shipmentIds)
                ->where('shipment_status', 'ASSIGNED_TO_VENDOR')
                ->where(
                    'vendor_id',
                    (int) $manifest['vendor_id']
                )
                ->where(
                    'vendor_manifest_id',
                    $manifestId
                )
                ->update([
                    'shipment_status'   => 'HANDED_OVER_TO_VENDOR',
                    'vendor_handover_at'=> $now,
                    'vendor_handover_by'=> $userId,
                    'updated_at'        => $now,
                ]);

            if (!$updated) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal memperbarui shipment: '
                    . ($error['message'] ?? 'Unknown error')
                );
            }

            if ($db->affectedRows() !== count($shipmentIds)) {
                throw new \RuntimeException(
                    'Sebagian shipment sudah berubah status. '
                    . 'Dispatch dibatalkan.'
                );
            }

            $db->table('manifests')
                ->where('id', $manifestId)
                ->where('status', 'OPEN')
                ->update([
                    'status'        => 'DISPATCHED',
                    'dispatched_at' => $now,
                    'dispatched_by' => $userId,
                    'updated_at'    => $now,
                ]);

            $vendorName = trim(
                (string) (
                    $manifest['vendor_name']
                    ?? 'Vendor #' . ($manifest['vendor_id'] ?? '-')
                )
            );

            foreach ($shipments as $shipment) {
                $trackingModel = new ShipmentTrackingModel();

                $trackingModel->insert([
                    'shipment_id'      => (int) $shipment['id'],
                    'tracking_code'    => $shipment['awb_no'] ?? '',
                    'tracking_status'  => 'HANDED_OVER_TO_VENDOR',
                    'public_status'    => 'IN_TRANSIT',
                    'tracking_location'=> 'Main Warehouse',
                    'tracking_notes'   =>
                        'Shipment diserahkan kepada vendor '
                        . $vendorName
                        . ' untuk proses pengiriman berikutnya.',
                    'event_time'       => $now,
                    'created_by'       => $userId,
                    'created_at'       => $now,
                    'event_type'       => 'HANDED_OVER_TO_VENDOR',
                    'source_reference' => $shipment['awb_no'] ?? '',
                    'source_system'    => 'VENDOR_MANIFEST',
                    'is_public'        => 1,
                ], false);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi handover vendor gagal.'
                );
            }

            $db->transCommit();

            return redirect()->back()->with(
                'success',
                'Manifest '
                . $manifest['manifest_no']
                . ' berhasil diserahkan kepada vendor.'
            );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[VENDOR MANIFEST DISPATCH FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()->with(
                'error',
                'Gagal dispatch vendor manifest: '
                . $e->getMessage()
            );
        }
    }

    private function recalculateManifest(int $manifestId): void
    {
        $db = db_connect();

        $totals = $db->table('manifest_details md')
            ->select([
                'COUNT(md.id) AS total_shipment',
                'COALESCE(SUM(s.total_colly), 0) AS total_colly',
                'COALESCE(SUM(s.chargeable_weight), 0) AS total_weight',
                'COALESCE(SUM(s.vendor_cost), 0) AS total_vendor_cost',
            ])
            ->join(
                'shipments s',
                's.id = md.shipment_id'
            )
            ->where('md.manifest_id', $manifestId)
            ->get()
            ->getRowArray();

        $db->table('manifests')
            ->where('id', $manifestId)
            ->update([
                'total_shipment'    => (int) (
                    $totals['total_shipment'] ?? 0
                ),
                'total_colly'       => (int) (
                    $totals['total_colly'] ?? 0
                ),
                'total_weight'      => (float) (
                    $totals['total_weight'] ?? 0
                ),
                'total_vendor_cost' => (float) (
                    $totals['total_vendor_cost'] ?? 0
                ),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
    }
}
