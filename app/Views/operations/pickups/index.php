<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">
                🚚 Pickup Operations
            </h3>

            <div class="text-muted">
                Kelola pickup request customer corporate.
            </div>
        </div>

        <form method="get">
            <select
                name="status"
                class="form-select"
                onchange="this.form.submit()"
            >
                <?php foreach ([
                    'ACTIVE',
                    'REQUESTED',
                    'SCHEDULED',
                    'COURIER_ASSIGNED',
                    'PICKED_UP',
                    'CANCELLED',
                ] as $status): ?>

                    <option
                        value="<?= esc($status) ?>"
                        <?= $statusFilter === $status
                            ? 'selected'
                            : '' ?>
                    >
                        <?= esc($status) ?>
                    </option>

                <?php endforeach; ?>
            </select>
        </form>
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

    <div class="card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>AWB / Customer</th>
                            <th>Pickup Address</th>
                            <th>PIC</th>
                            <th>Requested Window</th>
                            <th>Status</th>
                            <th>Courier</th>
                            <th style="min-width: 320px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (empty($pickups)): ?>

                        <tr>
                            <td
                                colspan="7"
                                class="text-center py-5 text-muted"
                            >
                                Tidak ada pickup request.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($pickups as $pickup): ?>

                            <?php
                            $status = strtoupper(
                                (string) (
                                    $pickup['pickup_status']
                                    ?? '-'
                                )
                            );
                            ?>

                            <tr>
                                <td>
                                    <strong>
                                        <?= esc(
                                            $pickup['awb_no'] ?? '-'
                                        ) ?>
                                    </strong>

                                    <div class="small text-muted">
                                        <?= esc(
                                            $pickup['customer_name']
                                            ?? 'Customer #'
                                            . (
                                                $pickup['customer_id']
                                                ?? '-'
                                            )
                                        ) ?>
                                    </div>

                                    <div class="small">
                                        <?= (int) (
                                            $pickup['total_colly']
                                            ?? 0
                                        ) ?> Colly ·

                                        <?= number_format(
                                            (float) (
                                                $pickup[
                                                    'chargeable_weight'
                                                ] ?? 0
                                            ),
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </div>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc(
                                            $pickup['sender_name']
                                            ?? '-'
                                        ) ?>
                                    </strong>

                                    <div>
                                        <?= esc(
                                            $pickup['sender_address']
                                            ?? '-'
                                        ) ?>
                                    </div>

                                    <div class="small text-muted">
                                        <?= esc(
                                            implode(', ', array_filter([
                                                $pickup[
                                                    'sender_city'
                                                ] ?? null,
                                                $pickup[
                                                    'sender_state'
                                                ] ?? null,
                                                $pickup[
                                                    'sender_postal_code'
                                                ] ?? null,
                                            ]))
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc(
                                            $pickup[
                                                'pickup_contact_name'
                                            ] ?? '-'
                                        ) ?>
                                    </strong>

                                    <div>
                                        <?= esc(
                                            $pickup[
                                                'pickup_contact_phone'
                                            ] ?? '-'
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <strong>
                                        <?= esc(
                                            $pickup[
                                                'requested_pickup_date'
                                            ] ?? '-'
                                        ) ?>
                                    </strong>

                                    <div class="small text-muted">
                                        <?= esc(
                                            $pickup[
                                                'requested_time_start'
                                            ] ?? '-'
                                        ) ?>
                                        –
                                        <?= esc(
                                            $pickup[
                                                'requested_time_end'
                                            ] ?? '-'
                                        ) ?>
                                    </div>

                                    <?php if (
                                        !empty(
                                            $pickup[
                                                'scheduled_pickup_at'
                                            ]
                                        )
                                    ): ?>
                                        <div class="small text-primary">
                                            Scheduled:
                                            <?= esc(
                                                $pickup[
                                                    'scheduled_pickup_at'
                                                ]
                                            ) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        <?= esc($status) ?>
                                    </span>

                                    <div class="small text-muted mt-1">
                                        Shipment:
                                        <?= esc(
                                            $pickup[
                                                'shipment_status'
                                            ] ?? '-'
                                        ) ?>
                                    </div>
                                </td>

                                <td>
                                    <?= esc(
                                        $pickup['courier_username']
                                        ?? '-'
                                    ) ?>
                                </td>

                                <td>

                                    <?php if (
                                        $status === 'REQUESTED'
                                    ): ?>

                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'operations/pickups/'
                                                . $pickup[
                                                    'pickup_request_id'
                                                ]
                                                . '/schedule'
                                            ) ?>"
                                            class="d-flex gap-2"
                                        >
                                            <?= csrf_field() ?>

                                            <input
                                                type="datetime-local"
                                                name="scheduled_pickup_at"
                                                class="form-control form-control-sm"
                                                value="<?= date(
                                                    'Y-m-d\TH:i',
                                                    strtotime(
                                                        $pickup[
                                                            'requested_pickup_date'
                                                        ]
                                                        . ' '
                                                        . (
                                                            $pickup[
                                                                'requested_time_start'
                                                            ]
                                                            ?: '09:00'
                                                        )
                                                    )
                                                ) ?>"
                                                required
                                            >

                                            <button
                                                class="btn btn-sm btn-primary"
                                            >
                                                Schedule
                                            </button>
                                        </form>

                                    <?php elseif (
                                        $status === 'SCHEDULED'
                                    ): ?>

                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'operations/pickups/'
                                                . $pickup[
                                                    'pickup_request_id'
                                                ]
                                                . '/assign'
                                            ) ?>"
                                            class="d-flex gap-2"
                                        >
                                            <?= csrf_field() ?>

                                            <select
                                                name="courier_user_id"
                                                class="form-select form-select-sm"
                                                required
                                            >
                                                <option value="">
                                                    -- Courier --
                                                </option>

                                                <?php if (empty($couriers)): ?>
                                                    <option value="" disabled>
                                                        Belum ada user dengan grup courier
                                                    </option>
                                                <?php else: ?>
                                                    <?php foreach (
                                                        $couriers as $courier
                                                    ): ?>
                                                        <option
                                                            value="<?= (int) $courier['id'] ?>"
                                                        >
                                                            <?= esc(
                                                                $courier[
                                                                    'username'
                                                                ]
                                                                ?? 'User #'
                                                                . $courier['id']
                                                            ) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>

                                            <button
                                                class="btn btn-sm btn-warning"
                                            >
                                                Assign
                                            </button>
                                        </form>

                                    <?php elseif (
                                        $status === 'COURIER_ASSIGNED'
                                    ): ?>

                                        <form
                                            method="post"
                                            action="<?= site_url(
                                                'operations/pickups/'
                                                . $pickup[
                                                    'pickup_request_id'
                                                ]
                                                . '/picked-up'
                                            ) ?>"
                                            onsubmit="return confirm(
                                                'Konfirmasi barang sudah dijemput?'
                                            );"
                                        >
                                            <?= csrf_field() ?>

                                            <button
                                                class="btn btn-sm btn-success"
                                            >
                                                ✅ Mark Picked Up
                                            </button>
                                        </form>

                                    <?php elseif (
                                        $status === 'PICKED_UP'
                                    ): ?>

                                        <span class="text-success">
                                            Menunggu penerimaan origin branch
                                        </span>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Tidak ada aksi
                                        </span>

                                    <?php endif; ?>

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
