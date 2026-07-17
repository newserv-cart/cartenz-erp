<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserOperationalProfileModel;

class UserOperationalProfileController
    extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $profiles = [];

        if (
            $db->tableExists(
                'user_operational_profiles'
            )
        ) {
            $profiles = $db
                ->table('user_operational_profiles uop')
                ->select([
                    'uop.*',
                    'u.username',
                    'b.branch_code',
                    'b.branch_name',
                    'b.city',
                    'a.agent_code',
                    'a.agent_name',
                ])
                ->join(
                    'users u',
                    'u.id = uop.user_id',
                    'inner'
                )
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
                ->orderBy('u.username', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view(
            'admin/operational_profiles/index',
            [
                'profiles' => $profiles,
                'users' => $db
                    ->table('users')
                    ->orderBy('username', 'ASC')
                    ->get()
                    ->getResultArray(),
                'branches' => $db
                    ->table('branches')
                    ->where('status', 1)
                    ->orderBy('city', 'ASC')
                    ->orderBy('branch_name', 'ASC')
                    ->get()
                    ->getResultArray(),
                'agents' => $db
                    ->table('agents')
                    ->where('status', 1)
                    ->orderBy('agent_name', 'ASC')
                    ->get()
                    ->getResultArray(),
            ]
        );
    }

    public function save()
    {
        try {
            $userId = (int) $this->request
                ->getPost('user_id');

            $branchId = (int) $this->request
                ->getPost('branch_id');

            $agentId = (int) $this->request
                ->getPost('agent_id');

            $staffType = strtoupper(
                trim(
                    (string) $this->request
                        ->getPost('staff_type')
                )
            );

            if ($userId <= 0 || $branchId <= 0) {
                throw new \RuntimeException(
                    'User dan Branch wajib dipilih.'
                );
            }

            if (
                !in_array(
                    $staffType,
                    [
                        'HEAD_OFFICE',
                        'BRANCH',
                        'AGENT',
                        'COURIER',
                    ],
                    true
                )
            ) {
                throw new \RuntimeException(
                    'Staff Type tidak valid.'
                );
            }

            $db = db_connect();

            if (
                !$db->table('users')
                    ->where('id', $userId)
                    ->get()
                    ->getRowArray()
            ) {
                throw new \RuntimeException(
                    'User tidak ditemukan.'
                );
            }

            if (
                !$db->table('branches')
                    ->where('id', $branchId)
                    ->where('status', 1)
                    ->get()
                    ->getRowArray()
            ) {
                throw new \RuntimeException(
                    'Branch tidak ditemukan atau tidak aktif.'
                );
            }

            if (
                $agentId > 0
                && !$db->table('agents')
                    ->where('id', $agentId)
                    ->where('status', 1)
                    ->get()
                    ->getRowArray()
            ) {
                throw new \RuntimeException(
                    'Agent tidak ditemukan atau tidak aktif.'
                );
            }

            $data = [
                'user_id' => $userId,
                'branch_id' => $branchId,
                'agent_id' =>
                    $agentId > 0 ? $agentId : null,
                'staff_type' => $staffType,
                'can_scan_pickup' =>
                    $this->request
                        ->getPost('can_scan_pickup')
                        ? 1
                        : 0,
                'can_scan_dropoff' =>
                    $this->request
                        ->getPost('can_scan_dropoff')
                        ? 1
                        : 0,
                'status' =>
                    $this->request->getPost('status')
                        ? 1
                        : 0,
            ];

            $model = new UserOperationalProfileModel();

            $existing = $model
                ->where('user_id', $userId)
                ->first();

            $saved = $existing
                ? $model->update(
                    (int) $existing['id'],
                    $data
                )
                : $model->insert($data);

            if (!$saved) {
                throw new \RuntimeException(
                    'Operational Profile gagal disimpan.'
                );
            }

            return redirect()
                ->back()
                ->with(
                    'success',
                    'Operational Profile berhasil disimpan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function toggle($id)
    {
        $model = new UserOperationalProfileModel();
        $row   = $model->find((int) $id);

        if (!$row) {
            return redirect()
                ->back()
                ->with(
                    'error',
                    'Operational Profile tidak ditemukan.'
                );
        }

        $model->update(
            (int) $row['id'],
            [
                'status' =>
                    (int) $row['status'] === 1
                        ? 0
                        : 1,
            ]
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Status Operational Profile diperbarui.'
            );
    }
}
