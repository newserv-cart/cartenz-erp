<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class BranchOperationalContextService
{
    public const STAFF_HEAD_OFFICE = 'HEAD_OFFICE';
    public const STAFF_BRANCH      = 'BRANCH';
    public const STAFF_AGENT       = 'AGENT';
    public const STAFF_COURIER     = 'COURIER';

    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    public function profileForUser(
        int $userId,
        bool $activeOnly = true
    ): ?array {
        if (
            $userId <= 0
            || !$this->db->tableExists(
                'user_operational_profiles'
            )
        ) {
            return null;
        }

        $builder = $this->db
            ->table('user_operational_profiles uop')
            ->select([
                'uop.*',
                'u.username',
                'b.branch_code',
                'b.branch_name',
                'b.city AS branch_city',
                'b.province AS branch_province',
                'b.address AS branch_address',
                'b.status AS branch_status',
                'a.agent_code',
                'a.agent_name',
                'a.handover_type',
                'a.status AS agent_status',
            ])
            ->join('users u', 'u.id = uop.user_id', 'inner')
            ->join(
                'branches b',
                'b.id = uop.branch_id',
                'inner'
            )
            ->join(
                'agents a',
                'a.id = uop.agent_id',
                'left'
            )
            ->where('uop.user_id', $userId);

        if ($activeOnly) {
            $builder
                ->where('uop.status', 1)
                ->where('b.status', 1);
        }

        $row = $builder
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function requireProfile(int $userId): array
    {
        $profile = $this->profileForUser($userId);

        if (!$profile) {
            throw new \RuntimeException(
                'User belum mempunyai Operational Profile. ' .
                'Hubungkan user ke branch terlebih dahulu.'
            );
        }

        return $profile;
    }

    public function branchById(int $branchId): ?array
    {
        if (
            $branchId <= 0
            || !$this->db->tableExists('branches')
        ) {
            return null;
        }

        $row = $this->db
            ->table('branches')
            ->where('id', $branchId)
            ->where('status', 1)
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function branchOptions(): array
    {
        if (!$this->db->tableExists('branches')) {
            return [];
        }

        $rows = $this->db
            ->table('branches')
            ->where('status', 1)
            ->orderBy('city', 'ASC')
            ->orderBy('branch_name', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as &$row) {
            $row['_label'] = $this->branchLabel($row);
        }
        unset($row);

        return $rows;
    }

    public function destinationOptions(
        int $originBranchId
    ): array {
        return array_values(
            array_filter(
                $this->branchOptions(),
                static fn (array $row): bool =>
                    (int) ($row['id'] ?? 0)
                    !== $originBranchId
            )
        );
    }

    public function mainBranch(): ?array
    {
        if (!$this->db->tableExists('branches')) {
            return null;
        }

        $row = $this->db
            ->table('branches')
            ->groupStart()
                ->where('branch_code', 'JKT-HO')
                ->orLike('branch_name', 'HEAD OFFICE')
            ->groupEnd()
            ->where('status', 1)
            ->orderBy('id', 'ASC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function isMainBranch(array $branch): bool
    {
        $code = strtoupper(
            trim((string) ($branch['branch_code'] ?? ''))
        );

        $name = strtoupper(
            trim((string) ($branch['branch_name'] ?? ''))
        );

        return $code === 'JKT-HO'
            || str_contains($code, 'HEAD')
            || str_contains($name, 'HEAD OFFICE')
            || str_contains($name, 'MAIN WAREHOUSE');
    }

    public function profileIsMain(array $profile): bool
    {
        return strtoupper(
            (string) ($profile['staff_type'] ?? '')
        ) === self::STAFF_HEAD_OFFICE
            || strtoupper(
                (string) ($profile['handover_type'] ?? '')
            ) === 'HEAD_OFFICE'
            || $this->isMainBranch($profile);
    }

    public function branchLabel(array $branch): string
    {
        $name = trim(
            (string) ($branch['branch_name'] ?? '')
        );

        $code = trim(
            (string) ($branch['branch_code'] ?? '')
        );

        $city = trim(
            (string) (
                $branch['city']
                ?? $branch['branch_city']
                ?? ''
            )
        );

        $label = $name !== ''
            ? $name
            : ('Branch #' . (int) ($branch['id'] ?? 0));

        if ($city !== '' && stripos($label, $city) === false) {
            $label .= ' — ' . $city;
        }

        if ($code !== '') {
            $label = $code . ' · ' . $label;
        }

        return $label;
    }

    public function locationLabel(array $branch): string
    {
        if ($this->isMainBranch($branch)) {
            return 'Cartenz Main Warehouse Jakarta';
        }

        $name = trim(
            (string) ($branch['branch_name'] ?? '')
        );

        $city = trim(
            (string) (
                $branch['city']
                ?? $branch['branch_city']
                ?? ''
            )
        );

        if ($name !== '') {
            return $city !== ''
                && stripos($name, $city) === false
                    ? $name . ', ' . $city
                    : $name;
        }

        return $city !== ''
            ? 'Cartenz ' . $city
            : 'Cartenz Branch';
    }

    public function automaticShipmentContext(
        int $actorId,
        ?array $inboundIntake = null
    ): ?array {
        $profile = $this->profileForUser($actorId);

        $branchId = (int) (
            $inboundIntake['receiving_branch_id']
            ?? $profile['branch_id']
            ?? 0
        );

        $branch = $this->branchById($branchId);

        if (!$branch) {
            return null;
        }

        $isMain = $this->isMainBranch($branch);

        if ($inboundIntake) {
            $method = ShipmentAcceptanceService::AGENT_HANDOVER_NO_AWB;
            $source = 'INBOUND_INTAKE';
        } else {
            $method = $isMain
                ? ShipmentAcceptanceService::AGENT_HANDOVER_MAIN
                : ShipmentAcceptanceService::AGENT_HANDOVER_BRANCH;

            $source = 'USER_OPERATIONAL_PROFILE';
        }

        $state = $isMain
            ? [
                'internal_status' =>
                    'RECEIVED_AT_MAIN_WAREHOUSE',
                'public_status' => 'IN_TRANSIT',
                'notes' =>
                    'Shipment diterima di main warehouse.',
            ]
            : [
                'internal_status' =>
                    'RECEIVED_AT_ORIGIN_BRANCH',
                'public_status' => 'PICKED_UP',
                'notes' =>
                    'Shipment diterima di origin branch.',
            ];

        return [
            'automatic'            => true,
            'source'               => $source,
            'profile'              => $profile,
            'branch'               => $branch,
            'branch_id'            => $branchId,
            'acceptance_method'    => $method,
            'receiving_branch_id'  => $branchId,
            'receiving_location'   =>
                $this->locationLabel($branch),
            'initial_state'        => $state,
            'is_main_branch'       => $isMain,
            'branch_label'         =>
                $this->branchLabel($branch),
        ];
    }

    public function assertActorAtBranch(
        int $actorId,
        int $expectedBranchId,
        string $action
    ): array {
        $profile = $this->requireProfile($actorId);

        if (
            $expectedBranchId > 0
            && (int) $profile['branch_id']
                !== $expectedBranchId
        ) {
            throw new \RuntimeException(
                $action .
                ' hanya boleh dilakukan oleh user pada branch ' .
                $expectedBranchId .
                '. Branch user saat ini: ' .
                $this->branchLabel($profile) .
                '.'
            );
        }

        return $profile;
    }
}
