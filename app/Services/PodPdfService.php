<?php

namespace App\Services;

use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class PodPdfService
{
    public function generate(int $shipmentId): array
    {
        $db = db_connect();

        $query = $db->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_status',

                's.actual_weight',
                's.volume_weight',
                's.chargeable_weight',
                's.total_colly',

                's.sender_name',
                's.sender_company',
                's.sender_phone',
                's.sender_email',
                's.sender_address',
                's.sender_city',
                's.sender_state',
                's.sender_postal_code',

                's.receiver_name',
                's.receiver_company',
                's.receiver_phone',
                's.receiver_email',
                's.receiver_address',
                's.receiver_city',
                's.receiver_state',
                's.receiver_postal_code',

                'c.country_name',
                'sv.service_name',

                'pod.id AS pod_id',
                'pod.delivered_at',
                'pod.delivered_to',
                'pod.delivery_relation',
                'pod.delivery_notes',
                'pod.pod_photo_path',
                'pod.pod_signature_path',
                'pod.recorded_by',
                'pod.created_at AS pod_created_at',

                'pod.delivery_latitude',
                'pod.delivery_longitude',
                'pod.location_accuracy',

                'u.username AS recorded_by_username',
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
                'shipment_proof_of_delivery pod',
                'pod.shipment_id = s.id',
                'inner'
            )
            ->join(
                'users u',
                'u.id = pod.recorded_by',
                'left'
            )
            ->where('s.id', $shipmentId)
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query POD Certificate gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $shipment = $query->getRowArray();

        if (!$shipment) {
            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if (
            strtoupper(
                trim((string) ($shipment['shipment_status'] ?? ''))
            ) !== 'DELIVERED'
        ) {
            throw new \RuntimeException(
                'POD Certificate hanya tersedia untuk shipment DELIVERED.'
            );
        }

        $photoDataUri = $this->fileToDataUri(
            $shipment['pod_photo_path'] ?? null
        );

        $signatureDataUri = $this->fileToDataUri(
            $shipment['pod_signature_path'] ?? null
        );

        $html = view('pdf/pod_certificate', [
            'shipment'         => $shipment,
            'photoDataUri'     => $photoDataUri,
            'signatureDataUri' => $signatureDataUri,
            'generatedAt'      => date('Y-m-d H:i:s'),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'awb_no' => (string) $shipment['awb_no'],
            'pdf'    => $dompdf->output(),
        ];
    }

    private function fileToDataUri(?string $filename): ?string
    {
        if (!$filename) {
            return null;
        }

        $filename = basename($filename);

        $fullPath = WRITEPATH
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'pod'
            . DIRECTORY_SEPARATOR
            . $filename;

        if (!is_file($fullPath)) {
            log_message(
                'error',
                '[POD PDF FILE NOT FOUND] ' . $fullPath
            );

            return null;
        }

        $mimeType = mime_content_type($fullPath)
            ?: 'application/octet-stream';

        $contents = file_get_contents($fullPath);

        if ($contents === false) {
            return null;
        }

        return 'data:'
            . $mimeType
            . ';base64,'
            . base64_encode($contents);
    }
}
