<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ShipmentModel;
use App\Models\ShipmentTrackingModel;


class AgentShipmentController extends BaseController
{
    public function detail($id)
    {
        $shipmentModel = new ShipmentModel();

        $trackingModel = new ShipmentTrackingModel();

        $shipment = $shipmentModel->find($id);

        $tracking = (new \App\Services\ExternalTrackingPresenter())
            ->presentMany(
                $trackingModel->getTrackingHistory($id)
            );

        $latestTracking = $trackingModel
            ->getLatestTracking($id);

        if (is_array($latestTracking)) {
            $latestTracking = (new \App\Services\ExternalTrackingPresenter())
                ->present($latestTracking);
        }

        return view(
            'agent/shipment_detail',
            [
                'shipment'       => $this->externalizeShipment($shipment),
                'tracking'       => $tracking,
                'latestTracking' => $latestTracking
            ]
        );
    }

    public function podFile(int $shipmentId, string $type)
    {
        $agent = $this->requireAgent();

        $agentId = (int) ($agent['id'] ?? 0);

        $db = db_connect();

        $query = $db
            ->table('shipment_proof_of_delivery pod')
            ->select([
                'pod.pod_photo_path',
                'pod.pod_signature_path',
            ])
            ->join('shipments s', 's.id = pod.shipment_id')
            ->where('s.id', $shipmentId)
            ->where('s.owner_type', 'AGENT')
            ->where('s.agent_id', $agentId)
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query file POD gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $pod = $query->getRowArray();

        if (!$pod) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $filename = match ($type) {
            'photo' =>
                $pod['pod_photo_path'] ?? null,

            'signature' =>
                $pod['pod_signature_path'] ?? null,

            default => null,
        };

        if (empty($filename)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'File Proof of Delivery tidak tersedia.'
            );
        }

        $filename = basename((string) $filename);

        $fullPath =
            WRITEPATH
            . 'uploads'
            . DIRECTORY_SEPARATOR
            . 'pod'
            . DIRECTORY_SEPARATOR
            . $filename;

        if (!is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
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
            ->setBody(file_get_contents($fullPath));
    }

    public function podPdf(int $shipmentId)
    {
        try {
            $agent = $this->requireAgent();
        } catch (\RuntimeException $e) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $agentId = (int) ($agent['id'] ?? 0);

        $db = db_connect();

        $shipment = $db->table('shipments')
            ->select([
                'id',
                'awb_no',
                'agent_id',
                'owner_type',
            ])
            ->where('id', $shipmentId)
            ->where('agent_id', $agentId)
            ->where('owner_type', 'AGENT')
            ->get()
            ->getRowArray();

        if (!$shipment) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

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

    private function requireAgent(): array
    {
        $user = auth()->user();
        $userId = (int) (auth()->id() ?? 0);

        if (
            $user === null
            || $userId <= 0
        ) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $db = db_connect();

        if (!$db->tableExists('agent_users')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $agent = $db
            ->table('agent_users AS au')
            ->select('a.id, a.agent_name')
            ->join('agents AS a', 'a.id = au.agent_id', 'inner')
            ->where('au.user_id', $userId)
            ->where('au.status', 1)
            ->where('a.status', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$agent) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        return $agent;
    }

    private function externalizeShipment($shipment)
    {
        if (!is_array($shipment)) {
            return $shipment;
        }

        return [
            'id'                => $shipment['id'] ?? 0,
            'awb_no'            => $shipment['awb_no'] ?? '',
            'shipment_status'   => $shipment['shipment_status'] ?? '',
            'shipment_date'     => $shipment['shipment_date'] ?? null,
            'service_name'      => $shipment['service_name'] ?? '',
            'country_name'      => $shipment['country_name'] ?? '',
            'total_colly'       => $shipment['total_colly'] ?? 0,
            'actual_weight'     => $shipment['actual_weight'] ?? 0,
            'volume_weight'     => $shipment['volume_weight'] ?? 0,
            'chargeable_weight' => $shipment['chargeable_weight'] ?? 0,
            'sender_name'       => $shipment['sender_name'] ?? '',
            'sender_company'    => $shipment['sender_company'] ?? '',
            'sender_phone'      => $shipment['sender_phone'] ?? '',
            'sender_address'    => $shipment['sender_address'] ?? '',
            'sender_city'       => $shipment['sender_city'] ?? '',
            'sender_state'      => $shipment['sender_state'] ?? '',
            'sender_postal_code'=> $shipment['sender_postal_code'] ?? '',
            'receiver_name'     => $shipment['receiver_name'] ?? '',
            'receiver_company'  => $shipment['receiver_company'] ?? '',
            'receiver_phone'    => $shipment['receiver_phone'] ?? '',
            'receiver_address'  => $shipment['receiver_address'] ?? '',
            'receiver_city'     => $shipment['receiver_city'] ?? '',
            'receiver_state'    => $shipment['receiver_state'] ?? '',
            'receiver_postal_code' => $shipment['receiver_postal_code'] ?? '',
            'planned_handover_method' => $shipment['planned_handover_method'] ?? '',
        ];
    }
}