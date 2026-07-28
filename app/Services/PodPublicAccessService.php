<?php

namespace App\Services;

use CodeIgniter\Exceptions\PageNotFoundException;

class PodPublicAccessService
{
    public function resolve(
        string $plainToken,
        bool $recordAccess = false
    ): array {
        $plainToken = strtolower(trim($plainToken));

        if (
            strlen($plainToken) !== 64
            || !ctype_xdigit($plainToken)
        ) {
            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $tokenHash = hash('sha256', $plainToken);

        $db = db_connect();

        $query = $db
            ->table('pod_access_tokens pat')
            ->select([
                'pat.id AS access_token_id',
                'pat.shipment_id',
                'pat.expires_at',
                'pat.revoked_at',
                'pat.created_at AS link_created_at',

                's.id',
                's.awb_no',
                's.shipment_status',
                's.service_id',
                's.country_id',
                's.total_colly',
                's.actual_weight',
                's.volume_weight',
                's.chargeable_weight',

                's.sender_name',
                's.sender_company',
                's.sender_phone',
                's.sender_address',
                's.sender_city',
                's.sender_state',
                's.sender_postal_code',

                's.receiver_name',
                's.receiver_company',
                's.receiver_phone',
                's.receiver_address',
                's.receiver_city',
                's.receiver_state',
                's.receiver_postal_code',

                'sv.service_name',
                'c.country_name',

                'pod.id AS pod_id',
                'pod.delivered_at',
                'pod.delivered_to',
                'pod.delivery_relation',
                'pod.delivery_notes',
                'pod.pod_photo_path',
                'pod.pod_signature_path',
            ])
            ->join(
                'shipments s',
                's.id = pat.shipment_id'
            )
            ->join(
                'shipment_proof_of_delivery pod',
                'pod.shipment_id = s.id'
            )
            ->join(
                'services sv',
                'sv.id = s.service_id',
                'left'
            )
            ->join(
                'countries c',
                'c.id = s.country_id',
                'left'
            )
            ->where('pat.token_hash', $tokenHash)
            ->get();

        if ($query === false) {
            $error = $db->error();

            log_message(
                'error',
                '[PUBLIC POD QUERY FAILED] '
                . ($error['message'] ?? 'Unknown database error')
            );

            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        $access = $query->getRowArray();

        if (!$access) {
            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if (!empty($access['revoked_at'])) {
            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if (
            !empty($access['expires_at'])
            && strtotime($access['expires_at']) < time()
        ) {
            throw PageNotFoundException::forPageNotFound(
                'Tautan Proof of Delivery sudah kedaluwarsa.'
            );
        }

        if (
            strtoupper(
                trim((string) ($access['shipment_status'] ?? ''))
            ) !== 'DELIVERED'
        ) {
            throw PageNotFoundException::forPageNotFound(
                'Proof of Delivery tidak ditemukan.'
            );
        }

        if ($recordAccess) {
            $db->table('pod_access_tokens')
                ->where('id', (int) $access['access_token_id'])
                ->set(
                    'access_count',
                    'access_count + 1',
                    false
                )
                ->set(
                    'last_accessed_at',
                    date('Y-m-d H:i:s')
                )
                ->update();
        }

        return $access;
    }
}
