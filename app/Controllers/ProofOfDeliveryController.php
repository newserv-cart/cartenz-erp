<?php

namespace App\Controllers;

use App\Models\ShipmentTrackingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ProofOfDeliveryController extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $query = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_awb',
                's.chargeable_weight',
                's.updated_at',

                'v.vendor_name',
                'c.country_name',

                'pod.id AS pod_id',
                'pod.delivered_at',
                'pod.delivered_to',
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
                'shipment_proof_of_delivery pod',
                'pod.shipment_id = s.id',
                'left'
            )
            ->where('s.shipment_status', 'DELIVERED')
            ->orderBy('s.updated_at', 'DESC')
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query Proof of Delivery gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        return view('warehouse/pod/index', [
            'title'     => 'Proof of Delivery',
            'shipments' => $query->getResultArray(),
        ]);
    }

    public function create(int $shipmentId)
    {
        $db = db_connect();

        $query = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                's.vendor_awb',
                's.receiver_name',
                's.receiver_company',
                's.receiver_phone',
                's.receiver_address',
                's.receiver_city',
                's.receiver_state',
                's.receiver_postal_code',

                'v.vendor_name',
                'c.country_name',

                'pod.id AS pod_id',
                'pod.delivered_at',
                'pod.delivered_to',
                'pod.delivery_relation',
                'pod.delivery_notes',
                'pod.pod_photo_path',
                'pod.pod_signature_path',
                'pod.recorded_by',

                'pod.delivery_latitude',
                'pod.delivery_longitude',
                'pod.location_accuracy',
                'pod.location_captured_at',
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
                'shipment_proof_of_delivery pod',
                'pod.shipment_id = s.id',
                'left'
            )
            ->where('s.id', $shipmentId)
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query shipment gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $shipment = $query->getRowArray();

        if (!$shipment) {
            throw PageNotFoundException::forPageNotFound(
                'Shipment tidak ditemukan.'
            );
        }

        if (
            strtoupper(
                trim((string) ($shipment['shipment_status'] ?? ''))
            ) !== 'DELIVERED'
        ) {
            return redirect()
                ->to(site_url('warehouse/pod'))
                ->with(
                    'error',
                    'POD hanya dapat dibuat untuk shipment DELIVERED.'
                );
        }

        return view('warehouse/pod/form', [
            'title'    => 'Record Proof of Delivery',
            'shipment' => $shipment,
        ]);
    }

    public function store(int $shipmentId)
    {
        $deliveredTo = trim(
            (string) $this->request->getPost('delivered_to')
        );

        $deliveryRelation = strtoupper(
            trim(
                (string) $this->request->getPost(
                    'delivery_relation'
                )
            )
        );

        $deliveredAtInput = trim(
            (string) $this->request->getPost('delivered_at')
        );

        $deliveryNotes = trim(
            (string) $this->request->getPost('delivery_notes')
        );

        if ($deliveredTo === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Nama penerima wajib diisi.');
        }

        if (mb_strlen($deliveredTo) > 150) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Nama penerima maksimal 150 karakter.'
                );
        }

        $allowedRelations = [
            'SELF',
            'FAMILY',
            'EMPLOYEE',
            'SECURITY',
            'RECEPTIONIST',
            'NEIGHBOR',
            'OTHER',
        ];

        if (
            $deliveryRelation !== ''
            && !in_array(
                $deliveryRelation,
                $allowedRelations,
                true
            )
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Hubungan penerima tidak valid.'
                );
        }

        if ($deliveredAtInput === '') {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal dan waktu penerimaan wajib diisi.'
                );
        }

        $deliveredTimestamp = strtotime($deliveredAtInput);

        if ($deliveredTimestamp === false) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal dan waktu penerimaan tidak valid.'
                );
        }

        $deliveredAt = date(
            'Y-m-d H:i:s',
            $deliveredTimestamp
        );

        if (mb_strlen($deliveryNotes) > 1000) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Catatan maksimal 1.000 karakter.'
                );
        }

        $db = db_connect();

        $shipmentQuery = $db->table('shipments')
            ->select([
                'id',
                'awb_no',
                'shipment_status',
            ])
            ->where('id', $shipmentId)
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
            ) !== 'DELIVERED'
        ) {
            return redirect()->back()->with(
                'error',
                'POD hanya dapat dibuat untuk shipment DELIVERED.'
            );
        }

        $existingPod = $db
            ->table('shipment_proof_of_delivery')
            ->where('shipment_id', $shipmentId)
            ->get()
            ->getRowArray();

        if ($existingPod) {
            return redirect()->back()->with(
                'error',
                'Shipment ini sudah memiliki Proof of Delivery.'
            );
        }

        $photo = $this->request->getFile('pod_photo');

        if (
            !$photo
            || !$photo->isValid()
            || $photo->hasMoved()
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Foto Proof of Delivery wajib diunggah.'
                );
        }

        $allowedPhotoMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array(
            $photo->getMimeType(),
            $allowedPhotoMimeTypes,
            true
        )) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Format foto harus JPG, PNG, atau WEBP.'
                );
        }

        if ($photo->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Ukuran foto maksimal 5 MB.'
                );
        }

        $signature = $this->request->getFile(
            'pod_signature'
        );

        $hasSignature = (
            $signature
            && $signature->isValid()
            && !$signature->hasMoved()
            && $signature->getError()
                !== UPLOAD_ERR_NO_FILE
        );

        if ($hasSignature) {
            if (!in_array(
                $signature->getMimeType(),
                $allowedPhotoMimeTypes,
                true
            )) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Format tanda tangan harus JPG, PNG, atau WEBP.'
                    );
            }

            if ($signature->getSize() > 3 * 1024 * 1024) {
                return redirect()->back()
                    ->withInput()
                    ->with(
                        'error',
                        'Ukuran tanda tangan maksimal 3 MB.'
                    );
            }
        }

        $uploadDirectory = WRITEPATH . 'uploads/pod/';

        if (
            !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0775, true)
            && !is_dir($uploadDirectory)
        ) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Folder Proof of Delivery tidak dapat dibuat.'
                );
        }

        $photoFilename = $photo->getRandomName();

        $signatureFilename = null;

        try {
            $photo->move(
                $uploadDirectory,
                $photoFilename
            );

            if ($hasSignature) {
                $signatureFilename =
                    $signature->getRandomName();

                $signature->move(
                    $uploadDirectory,
                    $signatureFilename
                );
            }
        } catch (\Throwable $e) {
            log_message(
                'error',
                '[POD FILE MOVE FAILED] ' . $e->getMessage()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan file Proof of Delivery.'
                );
        }

        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $inserted = $db
                ->table('shipment_proof_of_delivery')
                ->insert([
                    'shipment_id'       => $shipmentId,
                    'delivered_at'      => $deliveredAt,
                    'delivered_to'      => $deliveredTo,
                    'delivery_relation' =>
                        $deliveryRelation !== ''
                            ? $deliveryRelation
                            : null,
                    'delivery_notes'    =>
                        $deliveryNotes !== ''
                            ? $deliveryNotes
                            : null,
                    'pod_photo_path'    => $photoFilename,
                    'pod_signature_path'=> $signatureFilename,
                    'recorded_by'       => $userId,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

            if ($inserted === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal menyimpan POD: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            $podId = (int) $db->insertID();

            $description =
                'Proof of Delivery dicatat. Barang diterima oleh '
                . $deliveredTo
                . '.';

            if ($deliveryRelation !== '') {
                $description .=
                    ' Hubungan penerima: '
                    . $deliveryRelation
                    . '.';
            }

            $trackingModel = new ShipmentTrackingModel();

            $trackingInserted = $trackingModel->insert([
                'shipment_id'      => $shipmentId,
                'tracking_code'    => $shipment['awb_no'] ?? '',
                'tracking_status'  => 'POD_RECORDED',
                'public_status'    => 'DELIVERED',
                'tracking_location'=> 'Delivery Location',
                'tracking_notes'   => $description,
                'event_time'       => $deliveredAt,
                'created_by'       => $userId,
                'created_at'       => $now,
                'event_type'       => 'POD_RECORDED',
                'source_reference' => $shipment['awb_no'] ?? '',
                'source_system'    => 'POD',
                'is_public'        => 1,
            ], false);

            if ($trackingInserted === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking POD.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi Proof of Delivery gagal.'
                );
            }

            $db->transCommit();

            return redirect()->to(
                site_url('warehouse/pod/' . $shipmentId)
            )->with(
                'success',
                'Proof of Delivery untuk shipment '
                . ($shipment['awb_no'] ?? '')
                . ' berhasil disimpan.'
            );
        } catch (\Throwable $e) {
            $db->transRollback();

            $photoPath = $uploadDirectory . $photoFilename;

            if (is_file($photoPath)) {
                @unlink($photoPath);
            }

            if ($signatureFilename !== null) {
                $signaturePath =
                    $uploadDirectory . $signatureFilename;

                if (is_file($signaturePath)) {
                    @unlink($signaturePath);
                }
            }

            log_message(
                'error',
                '[POD STORE FAILED] '
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
            );

            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan Proof of Delivery: '
                    . $e->getMessage()
                );
        }
    }

    public function file(int $podId, string $type)
    {
        $db = db_connect();

        $pod = $db
            ->table('shipment_proof_of_delivery')
            ->where('id', $podId)
            ->get()
            ->getRowArray();

        if (!$pod) {
            throw PageNotFoundException::forPageNotFound(
                'File POD tidak ditemukan.'
            );
        }

        $filename = match ($type) {
            'photo' =>
                $pod['pod_photo_path'] ?? null,

            'signature' =>
                $pod['pod_signature_path'] ?? null,

            default => null,
        };

        if (!$filename) {
            throw PageNotFoundException::forPageNotFound(
                'File POD tidak tersedia.'
            );
        }

        $fullPath =
            WRITEPATH . 'uploads/pod/' . basename($filename);

        if (!is_file($fullPath)) {
            throw PageNotFoundException::forPageNotFound(
                'File POD tidak ditemukan di penyimpanan.'
            );
        }

        $mimeType = mime_content_type($fullPath)
            ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader(
                'Content-Disposition',
                'inline; filename="' . basename($filename) . '"'
            )
            ->setBody(file_get_contents($fullPath));
    }

    public function pdf(int $shipmentId)
    {
        $service = new \App\Services\PodPdfService();

        $document = $service->generate($shipmentId);

        $filename = 'POD-'
            . preg_replace(
                '/[^A-Z0-9\-]/i',
                '',
                $document['awb_no']
            )
            . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader(
                'Content-Disposition',
                'inline; filename="' . $filename . '"'
            )
            ->setBody($document['pdf']);
    }

    public function createShareLink(int $shipmentId)
    {
        $days = (int) $this->request->getPost('expiry_days');

        if (!in_array($days, [1, 7, 30, 90], true)) {
            $days = 30;
        }

        $db = db_connect();

        $shipmentQuery = $db
            ->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',
                'pod.id AS pod_id',
            ])
            ->join(
                'shipment_proof_of_delivery pod',
                'pod.shipment_id = s.id'
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
            return redirect()->back()->with(
                'error',
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if (
            strtoupper(
                (string) ($shipment['shipment_status'] ?? '')
            ) !== 'DELIVERED'
        ) {
            return redirect()->back()->with(
                'error',
                'Secure link hanya dapat dibuat untuk shipment DELIVERED.'
            );
        }

        $plainToken = bin2hex(random_bytes(32));
        $tokenHash  = hash('sha256', $plainToken);

        $now       = date('Y-m-d H:i:s');
        $expiresAt = date(
            'Y-m-d H:i:s',
            strtotime('+' . $days . ' days')
        );

        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $revoked = $db
                ->table('pod_access_tokens')
                ->where('shipment_id', $shipmentId)
                ->where('revoked_at IS NULL', null, false)
                ->update([
                    'revoked_at' => $now,
                    'revoked_by' => $userId,
                ]);

            if ($revoked === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal mencabut link lama: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            $inserted = $db
                ->table('pod_access_tokens')
                ->insert([
                    'shipment_id' => $shipmentId,
                    'token_hash'  => $tokenHash,
                    'expires_at'  => $expiresAt,
                    'created_by'  => $userId,
                    'created_at'  => $now,
                ]);

            if ($inserted === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal membuat secure link: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi pembuatan secure link gagal.'
                );
            }

            $db->transCommit();

            $shareUrl = site_url(
                'pod/public/' . $plainToken
            );

            return redirect()
                ->to(site_url('warehouse/pod/' . $shipmentId))
                ->with(
                    'success',
                    'Secure POD link berhasil dibuat dan berlaku '
                    . $days
                    . ' hari.'
                )
                ->with('pod_share_url', $shareUrl)
                ->with('pod_share_expires_at', $expiresAt);

        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[CREATE POD SHARE LINK FAILED] '
                . $e->getMessage()
            );

            return redirect()->back()->with(
                'error',
                'Gagal membuat secure POD link: '
                . $e->getMessage()
            );
        }
    }

    public function revokeShareLink(int $tokenId)
    {
        $db = db_connect();

        $token = $db
            ->table('pod_access_tokens')
            ->where('id', $tokenId)
            ->where('revoked_at IS NULL', null, false)
            ->get()
            ->getRowArray();

        if (!$token) {
            return redirect()->back()->with(
                'error',
                'Secure link tidak ditemukan atau sudah dicabut.'
            );
        }

        $updated = $db
            ->table('pod_access_tokens')
            ->where('id', $tokenId)
            ->where('revoked_at IS NULL', null, false)
            ->update([
                'revoked_at' => date('Y-m-d H:i:s'),
                'revoked_by' => (int) (auth()->id() ?? 0),
            ]);

        if ($updated === false) {
            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Gagal mencabut secure link: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        return redirect()->back()->with(
            'success',
            'Secure POD link berhasil dicabut.'
        );
    }

    public function supplement(int $shipmentId)
    {
        $signatureData = trim(
            (string) $this->request->getPost(
                'pod_signature_data'
            )
        );

        $latitudeInput = trim(
            (string) $this->request->getPost(
                'delivery_latitude'
            )
        );

        $longitudeInput = trim(
            (string) $this->request->getPost(
                'delivery_longitude'
            )
        );

        $accuracyInput = trim(
            (string) $this->request->getPost(
                'location_accuracy'
            )
        );

        $db = db_connect();

        $podQuery = $db
            ->table('shipment_proof_of_delivery pod')
            ->select([
                'pod.*',
                's.awb_no',
                's.shipment_status',
            ])
            ->join(
                'shipments s',
                's.id = pod.shipment_id'
            )
            ->where('pod.shipment_id', $shipmentId)
            ->get();

        if ($podQuery === false) {
            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Query POD gagal: '
                . ($error['message']
                    ?? 'Unknown database error')
            );
        }

        $pod = $podQuery->getRowArray();

        if (!$pod) {
            return redirect()->back()->with(
                'error',
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if (
            strtoupper(
                (string) ($pod['shipment_status'] ?? '')
            ) !== 'DELIVERED'
        ) {
            return redirect()->back()->with(
                'error',
                'Shipment belum berstatus DELIVERED.'
            );
        }

        $updateData = [
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $signatureFilename = null;

        if (
            empty($pod['pod_signature_path'])
            && $signatureData !== ''
        ) {
            try {
                $signatureFilename = $this->saveCanvasSignature(
                    $signatureData
                );

                $updateData['pod_signature_path'] =
                    $signatureFilename;
            } catch (\Throwable $e) {
                return redirect()->back()->with(
                    'error',
                    $e->getMessage()
                );
            }
        }

        if (
            empty($pod['delivery_latitude'])
            && $latitudeInput !== ''
            && $longitudeInput !== ''
        ) {
            if (
                !is_numeric($latitudeInput)
                || !is_numeric($longitudeInput)
            ) {
                return redirect()->back()->with(
                    'error',
                    'Koordinat lokasi tidak valid.'
                );
            }

            $latitude  = (float) $latitudeInput;
            $longitude = (float) $longitudeInput;

            if ($latitude < -90 || $latitude > 90) {
                return redirect()->back()->with(
                    'error',
                    'Latitude tidak valid.'
                );
            }

            if ($longitude < -180 || $longitude > 180) {
                return redirect()->back()->with(
                    'error',
                    'Longitude tidak valid.'
                );
            }

            $accuracy = is_numeric($accuracyInput)
                ? max(0, (float) $accuracyInput)
                : null;

            $updateData['delivery_latitude'] =
                number_format($latitude, 7, '.', '');

            $updateData['delivery_longitude'] =
                number_format($longitude, 7, '.', '');

            $updateData['location_accuracy'] =
                $accuracy;

            $updateData['location_captured_at'] =
                date('Y-m-d H:i:s');
        }

        if (count($updateData) === 1) {
            return redirect()->back()->with(
                'error',
                'Tidak ada data signature atau lokasi yang disimpan.'
            );
        }

        $updated = $db
            ->table('shipment_proof_of_delivery')
            ->where('id', (int) $pod['id'])
            ->update($updateData);

        if ($updated === false) {
            if ($signatureFilename !== null) {
                $signaturePath =
                    WRITEPATH
                    . 'uploads/pod/'
                    . $signatureFilename;

                if (is_file($signaturePath)) {
                    @unlink($signaturePath);
                }
            }

            $error = $db->error();

            return redirect()->back()->with(
                'error',
                'Gagal melengkapi POD: '
                . ($error['message']
                    ?? 'Unknown database error')
            );
        }

        return redirect()
            ->to(site_url('warehouse/pod/' . $shipmentId))
            ->with(
                'success',
                'Signature dan lokasi POD berhasil disimpan.'
            );
    }

    private function saveCanvasSignature(
        string $dataUrl
    ): string {
        if (
            !str_starts_with(
                $dataUrl,
                'data:image/png;base64,'
            )
        ) {
            throw new \RuntimeException(
                'Format signature canvas tidak valid.'
            );
        }

        $base64Data = substr(
            $dataUrl,
            strlen('data:image/png;base64,')
        );

        $binaryData = base64_decode(
            $base64Data,
            true
        );

        if ($binaryData === false) {
            throw new \RuntimeException(
                'Data signature tidak dapat dibaca.'
            );
        }

        if (strlen($binaryData) > 2 * 1024 * 1024) {
            throw new \RuntimeException(
                'Ukuran signature terlalu besar.'
            );
        }

        if (substr($binaryData, 0, 8) !== "\x89PNG\r\n\x1a\n") {
            throw new \RuntimeException(
                'File signature bukan PNG yang valid.'
            );
        }

        $uploadDirectory =
            WRITEPATH . 'uploads/pod/';

        if (
            !is_dir($uploadDirectory)
            && !mkdir($uploadDirectory, 0775, true)
            && !is_dir($uploadDirectory)
        ) {
            throw new \RuntimeException(
                'Folder signature POD tidak dapat dibuat.'
            );
        }

        $filename =
            'signature_'
            . bin2hex(random_bytes(16))
            . '.png';

        $fullPath = $uploadDirectory . $filename;

        if (
            file_put_contents(
                $fullPath,
                $binaryData,
                LOCK_EX
            ) === false
        ) {
            throw new \RuntimeException(
                'Signature gagal disimpan.'
            );
        }

        return $filename;
    }
}
