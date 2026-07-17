<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
        <div>
            <h3 class="mb-1">👥 Operational Profiles</h3>
            <div class="text-muted">
                Hubungkan user ke branch agar status, scan, dan manifest otomatis.
            </div>
        </div>

        <a
            href="<?= site_url('operations/scan') ?>"
            class="btn btn-outline-primary">
            📱 Branch Scan
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-primary text-white fw-bold">
            Add / Update User Mapping
        </div>

        <div class="card-body">
            <form
                method="post"
                action="<?= site_url(
                    'admin/operational-profiles/save'
                ) ?>">

                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">User *</label>
                        <select
                            name="user_id"
                            class="form-select"
                            required>

                            <option value="">-- Select User --</option>

                            <?php foreach ($users as $user): ?>
                                <option
                                    value="<?= (int) $user['id'] ?>"
                                    <?= (string) old('user_id')
                                        === (string) $user['id']
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($user['username']) ?>
                                    (#<?= (int) $user['id'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Branch *</label>
                        <select
                            name="branch_id"
                            class="form-select"
                            required>

                            <option value="">-- Select Branch --</option>

                            <?php foreach ($branches as $branch): ?>
                                <option
                                    value="<?= (int) $branch['id'] ?>"
                                    <?= (string) old('branch_id')
                                        === (string) $branch['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($branch['branch_code']) ?>
                                    ·
                                    <?= esc($branch['branch_name']) ?>
                                    <?= !empty($branch['city'])
                                        ? ' — ' . esc($branch['city'])
                                        : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Agent</label>
                        <select
                            name="agent_id"
                            class="form-select">

                            <option value="">-- Optional --</option>

                            <?php foreach ($agents as $agent): ?>
                                <option
                                    value="<?= (int) $agent['id'] ?>"
                                    <?= (string) old('agent_id')
                                        === (string) $agent['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($agent['agent_code']) ?>
                                    ·
                                    <?= esc($agent['agent_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Staff Type *</label>
                        <select
                            name="staff_type"
                            class="form-select"
                            required>

                            <?php foreach (
                                [
                                    'HEAD_OFFICE',
                                    'BRANCH',
                                    'AGENT',
                                    'COURIER',
                                ] as $type
                            ): ?>
                                <option
                                    value="<?= esc($type) ?>"
                                    <?= old('staff_type', 'BRANCH')
                                        === $type
                                        ? 'selected'
                                        : '' ?>>
                                    <?= esc($type) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-8 d-flex flex-wrap align-items-end gap-4">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="can_scan_pickup"
                                name="can_scan_pickup"
                                value="1"
                                <?= old('can_scan_pickup')
                                    ? 'checked'
                                    : '' ?>>

                            <label
                                for="can_scan_pickup"
                                class="form-check-label">
                                Can Scan Pickup
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="can_scan_dropoff"
                                name="can_scan_dropoff"
                                value="1"
                                <?= old('can_scan_dropoff', '1')
                                    ? 'checked'
                                    : '' ?>>

                            <label
                                for="can_scan_dropoff"
                                class="form-check-label">
                                Can Scan Drop-off
                            </label>
                        </div>

                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="status"
                                name="status"
                                value="1"
                                <?= old('status', '1')
                                    ? 'checked'
                                    : '' ?>>

                            <label
                                for="status"
                                class="form-check-label">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <button
                    type="submit"
                    class="btn btn-success mt-3">
                    Save Operational Profile
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Branch</th>
                            <th>Agent</th>
                            <th>Staff Type</th>
                            <th>Pickup</th>
                            <th>Drop-off</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (empty($profiles)): ?>
                            <tr>
                                <td
                                    colspan="8"
                                    class="text-center text-muted py-4">
                                    Belum ada Operational Profile.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($profiles as $profile): ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <?= esc($profile['username']) ?>
                                    </td>
                                    <td>
                                        <?= esc($profile['branch_code']) ?>
                                        ·
                                        <?= esc($profile['branch_name']) ?>
                                        <?= !empty($profile['city'])
                                            ? ' — ' . esc($profile['city'])
                                            : '' ?>
                                    </td>
                                    <td>
                                        <?= esc(
                                            $profile['agent_name']
                                            ?? '-'
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= esc($profile['staff_type']) ?>
                                    </td>
                                    <td>
                                        <?= (int) $profile['can_scan_pickup']
                                            === 1
                                            ? 'Yes'
                                            : 'No' ?>
                                    </td>
                                    <td>
                                        <?= (int) $profile['can_scan_dropoff']
                                            === 1
                                            ? 'Yes'
                                            : 'No' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= (int) $profile['status'] === 1
                                            ? 'success'
                                            : 'secondary' ?>">
                                            <?= (int) $profile['status'] === 1
                                                ? 'ACTIVE'
                                                : 'INACTIVE' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'admin/operational-profiles/toggle/' .
                                                $profile['id']
                                            ) ?>">

                                            <?= csrf_field() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-secondary">
                                                Toggle
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
