<?php

namespace App\Controllers;

use App\Services\PodPdfService;
use App\Services\PodPublicAccessService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PublicPodController extends BaseController
{
    public function show(string $token)
    {
        $service = new PodPublicAccessService();

        $access = $service->resolve(
            $token,
            true
        );

        return view('public/pod', [
            'title'  => 'Proof of Delivery',
            'pod'    => $access,
            'token'  => $token,
        ]);
    }

    public function pdf(string $token)
    {
        $accessService = new PodPublicAccessService();

        $access = $accessService->resolve(
            $token,
            true
        );

        $pdfService = new PodPdfService();

        $document = $pdfService->generate(
            (int) $access['shipment_id']
        );

        $filename = 'POD-'
            . preg_replace(
                '/[^A-Z0-9\-]/i',
                '',
                (string) $access['awb_no']
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

    public function file(string $token, string $type)
    {
        $service = new PodPublicAccessService();

        $access = $service->resolve(
            $token,
            false
        );

        $filename = match ($type) {
            'photo' =>
                $access['pod_photo_path'] ?? null,

            'signature' =>
                $access['pod_signature_path'] ?? null,

            default => null,
        };

        if (!$filename) {
            throw PageNotFoundException::forPageNotFound(
                'File Proof of Delivery tidak tersedia.'
            );
        }

        $filename = basename((string) $filename);

        $fullPath = WRITEPATH
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'pod'
            . DIRECTORY_SEPARATOR
            . $filename;

        if (!is_file($fullPath)) {
            throw PageNotFoundException::forPageNotFound(
                'File Proof of Delivery tidak ditemukan.'
            );
        }

        $mimeType = mime_content_type($fullPath)
            ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader(
                'Content-Disposition',
                'inline; filename="' . $filename . '"'
            )
            ->setHeader(
                'Content-Length',
                (string) filesize($fullPath)
            )
            ->setBody(file_get_contents($fullPath));
    }
}
