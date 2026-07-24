<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;
use RuntimeException;

class ShipmentOwnerContextService
{
    private BaseConnection $db;

    private const OWNER_TYPES = [
        'CUSTOMER',
        'AGENT',
        'WHITE_LABEL',
    ];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Menentukan pemilik shipment/address book.
     *
     * Operational/admin:
     * - owner dipilih dari form.
     *
     * Customer/Agent/White Label:
     * - owner ditentukan dari profil akun login.
     * - owner_id dari browser tidak dipercaya.
     */
    public function resolve(
        int $userId,
        bool $isOperationalUser,
        ?string $postedOwnerType = null,
        int $postedOwnerId = 0
    ): array {
        if ($userId <= 0) {
            throw new RuntimeException(
                'User login tidak ditemukan.'
            );
        }

        if ($isOperationalUser) {
            $ownerType = $this->normalizeOwnerType(
                $postedOwnerType
            );

            if ($postedOwnerId <= 0) {
                throw new RuntimeException(
                    'Account owner belum dipilih.'
                );
            }

            $owner = $this->findActiveOwner(
                $ownerType,
                $postedOwnerId
            );

            if (!$owner) {
                throw new RuntimeException(
                    'Account owner tidak ditemukan atau tidak aktif.'
                );
            }

            return $this->buildContext(
                $ownerType,
                $postedOwnerId,
                $userId,
                'ADMIN_ASSISTED',
                $owner
            );
        }

        /*
         * Self-service: cari profil yang terhubung
         * dengan user login.
         */
        $matches = [];

        foreach (self::OWNER_TYPES as $ownerType) {
            $ownerId = $this->findOwnerIdByUser(
                $ownerType,
                $userId
            );

            if ($ownerId <= 0) {
                continue;
            }

            $owner = $this->findActiveOwner(
                $ownerType,
                $ownerId
            );

            if (!$owner) {
                continue;
            }

            $matches[] = [
                'owner_type' => $ownerType,
                'owner_id'   => $ownerId,
                'owner'      => $owner,
            ];
        }

        if (count($matches) === 0) {
            throw new RuntimeException(
                'Akun belum terhubung ke profil customer, agent, atau white label.'
            );
        }

        if (count($matches) > 1) {
            throw new RuntimeException(
                'Akun terhubung ke lebih dari satu profil owner. Hubungi administrator.'
            );
        }

        $match = $matches[0];

        return $this->buildContext(
            $match['owner_type'],
            $match['owner_id'],
            $userId,
            'SELF_SERVICE',
            $match['owner']
        );
    }

    private function normalizeOwnerType(
        ?string $ownerType
    ): string {
        $ownerType = strtoupper(
            trim((string) $ownerType)
        );

        if (!in_array(
            $ownerType,
            self::OWNER_TYPES,
            true
        )) {
            throw new RuntimeException(
                'Owner type tidak valid.'
            );
        }

        return $ownerType;
    }

    private function ownerConfiguration(
        string $ownerType
    ): array {
        return match ($ownerType) {
            'CUSTOMER' => [
                'table'        => 'customers',
                'displayFields'=> [
                    'customer_name',
                    'company_name',
                    'name',
                ],
                'userLinkField'=> 'customer_id',
            ],

            'AGENT' => [
                'table'        => 'agents',
                'displayFields'=> [
                    'agent_name',
                    'company_name',
                    'name',
                ],
                'userLinkField'=> 'agent_id',
            ],

            'WHITE_LABEL' => [
                'table'        => 'white_labels',
                'displayFields'=> [
                    'white_label_name',
                    'company_name',
                    'name',
                ],
                'userLinkField'=> 'white_label_id',
            ],

            default => throw new RuntimeException(
                'Owner type tidak dikenal.'
            ),
        };
    }

    private function findOwnerIdByUser(
        string $ownerType,
        int $userId
    ): int {
        $config = $this->ownerConfiguration(
            $ownerType
        );

        $table = $config['table'];

        if (!$this->db->tableExists($table)) {
            return 0;
        }

        /*
         * Pola pertama:
         * profile table mempunyai kolom user_id.
         */
        if ($this->db->fieldExists('user_id', $table)) {
            $query = $this->db
                ->table($table)
                ->select('id')
                ->where('user_id', $userId);

            if ($this->db->fieldExists('status', $table)) {
                $query->where('status', 1);
            }

            $record = $query
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($record) {
                return (int) ($record['id'] ?? 0);
            }
        }

        /*
         * Pola kedua:
         * tabel users mempunyai customer_id/agent_id/
         * white_label_id.
         */
        $userLinkField = $config['userLinkField'];

        if (
            $this->db->tableExists('users')
            && $this->db->fieldExists(
                $userLinkField,
                'users'
            )
        ) {
            $user = $this->db
                ->table('users')
                ->select($userLinkField)
                ->where('id', $userId)
                ->get()
                ->getRowArray();

            return (int) (
                $user[$userLinkField] ?? 0
            );
        }

        return 0;
    }

    private function findActiveOwner(
        string $ownerType,
        int $ownerId
    ): ?array {
        $config = $this->ownerConfiguration(
            $ownerType
        );

        $table = $config['table'];

        if (!$this->db->tableExists($table)) {
            return null;
        }

        $query = $this->db
            ->table($table)
            ->where('id', $ownerId);

        if ($this->db->fieldExists('status', $table)) {
            $query->where('status', 1);
        }

        $record = $query
            ->limit(1)
            ->get()
            ->getRowArray();

        return $record ?: null;
    }

    private function buildContext(
        string $ownerType,
        int $ownerId,
        int $userId,
        string $entryChannel,
        array $owner
    ): array {
        $config = $this->ownerConfiguration(
            $ownerType
        );

        $ownerName = '';

        foreach ($config['displayFields'] as $field) {
            if (!empty($owner[$field])) {
                $ownerName = trim(
                    (string) $owner[$field]
                );

                break;
            }
        }

        return [
            'owner_type'        => $ownerType,
            'owner_id'          => $ownerId,
            'owner_name'        => $ownerName,
            'created_by_user_id'=> $userId,
            'entry_channel'     => $entryChannel,

            /*
             * Legacy compatibility.
             */
            'customer_id' => $ownerType === 'CUSTOMER'
                ? $ownerId
                : null,

            'agent_id' => $ownerType === 'AGENT'
                ? $ownerId
                : null,

            'source_agent_id' => $ownerType === 'AGENT'
                ? $ownerId
                : null,
        ];
    }
}
