<div class="card border-0 shadow-sm mt-3">

    <div class="card-header bg-white">
        <strong>📸 Proof of Delivery</strong>
    </div>

    <div class="card-body">

        <?php if (empty($pod)): ?>

            <div class="alert alert-light border mb-0">
                Proof of Delivery belum tersedia untuk shipment ini.
            </div>

        <?php else: ?>

            <div class="row g-4">

                <div class="col-md-5">

                    <div class="mb-3">
                        <div class="text-muted small">
                            Delivered To
                        </div>

                        <div class="fw-semibold">
                            <?= esc($pod['delivered_to'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">
                            Delivery Relation
                        </div>

                        <div class="fw-semibold">
                            <?= esc(
                                $pod['delivery_relation'] ?? '-'
                            ) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">
                            Delivered At
                        </div>

                        <div class="fw-semibold">
                            <?= esc($pod['delivered_at'] ?? '-') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">
                            Recorded By
                        </div>

                        <div class="fw-semibold">
                            <?= esc(
                                $pod['recorded_by_username']
                                ?? 'User #' . ($pod['recorded_by'] ?? '-')
                            ) ?>
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small">
                            Delivery Notes
                        </div>

                        <div>
                            <?= nl2br(
                                esc($pod['delivery_notes'] ?? '-')
                            ) ?>
                        </div>
                    </div>

                </div>

                <div class="col-md-7">

                    <div class="mb-4">
                        <div class="text-muted small mb-2">
                            POD Photo
                        </div>

                        <a
                            href="<?= site_url(
                                'warehouse/pod/file/'
                                . (int) $pod['id']
                                . '/photo'
                            ) ?>"
                            target="_blank"
                        >
                            <img
                                src="<?= site_url(
                                    'warehouse/pod/file/'
                                    . (int) $pod['id']
                                    . '/photo'
                                ) ?>"
                                alt="Proof of Delivery"
                                class="img-fluid rounded border"
                                style="max-height: 500px;"
                            >
                        </a>
                    </div>

                    <?php if (
                        !empty($pod['pod_signature_path'])
                    ): ?>

                        <div>
                            <div class="text-muted small mb-2">
                                Receiver Signature
                            </div>

                            <a
                                href="<?= site_url(
                                    'warehouse/pod/file/'
                                    . (int) $pod['id']
                                    . '/signature'
                                ) ?>"
                                target="_blank"
                            >
                                <img
                                    src="<?= site_url(
                                        'warehouse/pod/file/'
                                        . (int) $pod['id']
                                        . '/signature'
                                    ) ?>"
                                    alt="Receiver Signature"
                                    class="img-fluid rounded border"
                                    style="max-height: 220px;"
                                >
                            </a>
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>

    </div>
</div>
