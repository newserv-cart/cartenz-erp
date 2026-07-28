<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3 class="mb-1">
            📸 Proof of Delivery
        </h3>

        <div class="text-muted">
            <?= esc($shipment['awb_no'] ?? '-') ?>
            · Proof of Delivery
        </div>
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

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-3">
                    <div class="text-muted">AWB CARTENZ</div>
                    <strong>
                        <?= esc($shipment['awb_no'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">Shipment Status</div>
                    <strong>
                        <?= esc($shipment['shipment_status'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">Destination</div>
                    <strong>
                        <?= esc($shipment['country_name'] ?? '-') ?>
                    </strong>
                </div>

                <div class="col-md-3">
                    <div class="text-muted">Receiver</div>
                    <strong>
                        <?= esc($shipment['receiver_name'] ?? '-') ?>
                    </strong>
                </div>

            </div>

        </div>
    </div>

    <?php if (!empty($shipment['pod_id'])): ?>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Proof of Delivery Recorded</strong>

                <a
                    href="<?= site_url(
                        'warehouse/pod/'
                        . (int) $shipment['id']
                        . '/pdf'
                    ) ?>"
                    target="_blank"
                    class="btn btn-sm btn-danger"
                >
                    📄 Open POD PDF
                </a>
            </div>

            <div class="card-body">

                <div class="row g-4">

                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="text-muted">
                                Delivered To
                            </div>

                            <strong>
                                <?= esc(
                                    $shipment['delivered_to'] ?? '-'
                                ) ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted">
                                Delivery Relation
                            </div>

                            <strong>
                                <?= esc(
                                    $shipment['delivery_relation']
                                    ?? '-'
                                ) ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted">
                                Delivered At
                            </div>

                            <strong>
                                <?= esc(
                                    $shipment['delivered_at'] ?? '-'
                                ) ?>
                            </strong>
                        </div>

                        <div>
                            <div class="text-muted">
                                Notes
                            </div>

                            <div>
                                <?= nl2br(
                                    esc(
                                        $shipment['delivery_notes']
                                        ?? '-'
                                    )
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">

                        <div class="mb-4">
                            <div class="text-muted mb-2">
                                POD Photo
                            </div>

                            <a
                                href="<?= site_url(
                                    'warehouse/pod/file/'
                                    . (int) $shipment['pod_id']
                                    . '/photo'
                                ) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <img
                                    src="<?= site_url(
                                        'warehouse/pod/file/'
                                        . (int) $shipment['pod_id']
                                        . '/photo'
                                    ) ?>"
                                    alt=""
                                    class="img-fluid rounded border"
                                    style="max-height: 450px;"
                                >
                            </a>
                        </div>

                        <?php if (!empty($shipment['pod_signature_path'])): ?>
                            <div class="mb-4">
                                <div class="text-muted mb-2">
                                    Receiver Signature
                                </div>

                                <a
                                    href="<?= site_url(
                                        'warehouse/pod/file/'
                                        . (int) $shipment['pod_id']
                                        . '/signature'
                                    ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <img
                                        src="<?= site_url(
                                            'warehouse/pod/file/'
                                            . (int) $shipment['pod_id']
                                            . '/signature'
                                        ) ?>"
                                        alt=""
                                        class="img-fluid rounded border bg-white"
                                        style="max-height: 220px;"
                                    >
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (
                            !empty($shipment['delivery_latitude'])
                            && !empty($shipment['delivery_longitude'])
                        ): ?>
                            <div class="mt-4">
                                <div class="text-muted">
                                    Delivery Location
                                </div>

                                <strong>
                                    <?= number_format(
                                        (float) (
                                            $shipment['delivery_latitude'] ?? 0
                                        ),
                                        7,
                                        '.',
                                        ''
                                    ) ?>,
                                    <?= number_format(
                                        (float) (
                                            $shipment['delivery_longitude'] ?? 0
                                        ),
                                        7,
                                        '.',
                                        ''
                                    ) ?>
                                </strong>

                                <div class="small text-muted">
                                    Accuracy:
                                    <?= number_format(
                                        (float) $shipment['location_accuracy'],
                                        2,
                                        ',',
                                        '.'
                                    ) ?> meter
                                </div>

                                <a
                                    href="<?= 'https://www.google.com/maps?q='
                                        . rawurlencode(
                                            $shipment['delivery_latitude']
                                            . ','
                                            . $shipment['delivery_longitude']
                                        ) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary mt-2"
                                >
                                    📍 Open Location
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>

            </div>
        </div>

        <?php
        $needsSignature = empty($shipment['pod_signature_path']);

        $needsLocation =
            empty($shipment['delivery_latitude'])
            || empty($shipment['delivery_longitude']);
        ?>

        <?php if (
            !empty($shipment['pod_id'])
            && ($needsSignature || $needsLocation)
        ): ?>

            <div class="card shadow-sm mt-4">

                <div class="card-header">
                    <strong>✍️ Complete POD Evidence</strong>
                </div>

                <div class="card-body">

                    <form
                        method="post"
                        action="<?= site_url(
                            'warehouse/pod/'
                            . (int) $shipment['id']
                            . '/supplement'
                        ) ?>"
                        id="podSupplementForm"
                    >
                        <?= csrf_field() ?>

                        <?php if ($needsSignature): ?>

                            <div class="mb-4">

                                <label class="form-label">
                                    Receiver Signature
                                </label>

                                <div
                                    class="border rounded bg-white"
                                    style="max-width: 720px;"
                                >
                                    <canvas
                                        id="signatureCanvas"
                                        width="720"
                                        height="260"
                                        style="
                                            width: 100%;
                                            height: 260px;
                                            touch-action: none;
                                            cursor: crosshair;
                                        "
                                    ></canvas>
                                </div>

                                <input
                                    type="hidden"
                                    name="pod_signature_data"
                                    id="pod_signature_data"
                                >

                                <div class="mt-2">

                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary btn-sm"
                                        id="clearSignature"
                                    >
                                        Clear Signature
                                    </button>

                                </div>

                                <div class="form-text">
                                    Minta penerima menandatangani area di atas.
                                </div>

                            </div>

                        <?php endif; ?>

                        <?php if ($needsLocation): ?>

                            <div class="mb-4">

                                <label class="form-label">
                                    Delivery GPS Location
                                </label>

                                <div class="row g-3">

                                    <div class="col-md-3">
                                        <input
                                            type="text"
                                            id="delivery_latitude_display"
                                            class="form-control"
                                            placeholder="Latitude"
                                            readonly
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <input
                                            type="text"
                                            id="delivery_longitude_display"
                                            class="form-control"
                                            placeholder="Longitude"
                                            readonly
                                        >
                                    </div>

                                    <div class="col-md-3">
                                        <input
                                            type="text"
                                            id="location_accuracy_display"
                                            class="form-control"
                                            placeholder="Accuracy"
                                            readonly
                                        >
                                    </div>

                                    <div class="col-md-3 d-grid">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary"
                                            id="captureLocation"
                                        >
                                            📍 Capture Location
                                        </button>
                                    </div>

                                </div>

                                <input
                                    type="hidden"
                                    name="delivery_latitude"
                                    id="delivery_latitude"
                                >

                                <input
                                    type="hidden"
                                    name="delivery_longitude"
                                    id="delivery_longitude"
                                >

                                <input
                                    type="hidden"
                                    name="location_accuracy"
                                    id="location_accuracy"
                                >

                                <div
                                    id="locationMessage"
                                    class="form-text"
                                >
                                    Klik Capture Location dan izinkan akses lokasi.
                                </div>

                            </div>

                        <?php endif; ?>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            ✅ Save Signature & Location
                        </button>

                    </form>

                </div>
            </div>

        <?php endif; ?>

        <div class="card shadow-sm mt-4">

            <div class="card-header">
                <strong>🔗 Secure Public POD Link</strong>
            </div>

            <div class="card-body">

                <p class="text-muted">
                    Buat tautan untuk customer walk-in atau pengirim
                    yang tidak memiliki akun.
                </p>

                <?php if (
                    session()->getFlashdata('pod_share_url')
                ): ?>

                    <div class="alert alert-success">

                        <div class="fw-bold mb-2">
                            Secure link berhasil dibuat
                        </div>

                        <div class="input-group">
                            <input
                                type="text"
                                id="podShareUrl"
                                class="form-control"
                                value="<?= esc(
                                    session()->getFlashdata(
                                        'pod_share_url'
                                    )
                                ) ?>"
                                readonly
                            >

                            <button
                                type="button"
                                class="btn btn-outline-success"
                                onclick="copyPodShareUrl()"
                            >
                                Copy Link
                            </button>
                        </div>

                        <div class="small mt-2">
                            Berlaku sampai:
                            <?= esc(
                                session()->getFlashdata(
                                    'pod_share_expires_at'
                                )
                            ) ?>
                        </div>

                    </div>

                <?php endif; ?>

                <form
                    method="post"
                    action="<?= site_url(
                        'warehouse/pod/'
                        . (int) $shipment['id']
                        . '/share-link'
                    ) ?>"
                >
                    <?= csrf_field() ?>

                    <div class="row g-3 align-items-end">

                        <div class="col-md-4">
                            <label class="form-label">
                                Link Validity
                            </label>

                            <select
                                name="expiry_days"
                                class="form-select"
                            >
                                <option value="1">1 Day</option>
                                <option value="7">7 Days</option>
                                <option value="30" selected>
                                    30 Days
                                </option>
                                <option value="90">90 Days</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                onclick="return confirm(
                                    'Buat secure POD link baru? '
                                    + 'Link aktif lama akan dicabut.'
                                );"
                            >
                                🔗 Generate Secure Link
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </div>

        <script>
        function copyPodShareUrl() {
            const input = document.getElementById('podShareUrl');

            if (!input) {
                return;
            }

            navigator.clipboard.writeText(input.value)
                .then(function () {
                    alert('Secure POD link berhasil disalin.');
                })
                .catch(function () {
                    input.select();
                    document.execCommand('copy');
                    alert('Secure POD link berhasil disalin.');
                });
        }
        </script>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('signatureCanvas');
            const signatureInput = document.getElementById(
                'pod_signature_data'
            );

            let signatureTouched = false;

            if (canvas && signatureInput) {
                const context = canvas.getContext('2d');

                context.lineWidth = 2;
                context.lineCap = 'round';
                context.lineJoin = 'round';

                let drawing = false;

                function getPoint(event) {
                    const rect = canvas.getBoundingClientRect();

                    const clientX = event.touches
                        ? event.touches[0].clientX
                        : event.clientX;

                    const clientY = event.touches
                        ? event.touches[0].clientY
                        : event.clientY;

                    return {
                        x: (clientX - rect.left)
                            * (canvas.width / rect.width),

                        y: (clientY - rect.top)
                            * (canvas.height / rect.height)
                    };
                }

                function startDrawing(event) {
                    event.preventDefault();

                    drawing = true;
                    signatureTouched = true;

                    const point = getPoint(event);

                    context.beginPath();
                    context.moveTo(point.x, point.y);
                }

                function draw(event) {
                    if (!drawing) {
                        return;
                    }

                    event.preventDefault();

                    const point = getPoint(event);

                    context.lineTo(point.x, point.y);
                    context.stroke();
                }

                function stopDrawing(event) {
                    if (!drawing) {
                        return;
                    }

                    event.preventDefault();
                    drawing = false;
                    context.closePath();
                }

                canvas.addEventListener('mousedown', startDrawing);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('mouseup', stopDrawing);
                canvas.addEventListener('mouseleave', stopDrawing);

                canvas.addEventListener('touchstart', startDrawing, {
                    passive: false
                });

                canvas.addEventListener('touchmove', draw, {
                    passive: false
                });

                canvas.addEventListener('touchend', stopDrawing, {
                    passive: false
                });

                const clearButton = document.getElementById(
                    'clearSignature'
                );

                if (clearButton) {
                    clearButton.addEventListener('click', function () {
                        context.clearRect(
                            0,
                            0,
                            canvas.width,
                            canvas.height
                        );

                        signatureTouched = false;
                        signatureInput.value = '';
                    });
                }
            }

            const captureLocation = document.getElementById(
                'captureLocation'
            );

            if (captureLocation) {
                captureLocation.addEventListener('click', function () {
                    const message = document.getElementById(
                        'locationMessage'
                    );

                    if (!navigator.geolocation) {
                        message.textContent =
                            'Browser tidak mendukung GPS location.';

                        return;
                    }

                    captureLocation.disabled = true;
                    message.textContent = 'Mengambil lokasi...';

                    navigator.geolocation.getCurrentPosition(
                        function (position) {
                            const latitude =
                                position.coords.latitude;

                            const longitude =
                                position.coords.longitude;

                            const accuracy =
                                position.coords.accuracy;

                            document.getElementById(
                                'delivery_latitude'
                            ).value = latitude;

                            document.getElementById(
                                'delivery_longitude'
                            ).value = longitude;

                            document.getElementById(
                                'location_accuracy'
                            ).value = accuracy;

                            document.getElementById(
                                'delivery_latitude_display'
                            ).value = latitude.toFixed(7);

                            document.getElementById(
                                'delivery_longitude_display'
                            ).value = longitude.toFixed(7);

                            document.getElementById(
                                'location_accuracy_display'
                            ).value = accuracy.toFixed(2) + ' meter';

                            message.textContent =
                                'Lokasi berhasil direkam.';

                            captureLocation.disabled = false;
                        },

                        function (error) {
                            message.textContent =
                                'Lokasi gagal diambil: '
                                + error.message;

                            captureLocation.disabled = false;
                        },

                        {
                            enableHighAccuracy: true,
                            timeout: 15000,
                            maximumAge: 0
                        }
                    );
                });
            }

            const form = document.getElementById(
                'podSupplementForm'
            );

            if (form) {
                form.addEventListener('submit', function (event) {
                    if (
                        canvas
                        && signatureInput
                        && signatureTouched
                    ) {
                        signatureInput.value =
                            canvas.toDataURL('image/png');
                    }

                    const hasSignature =
                        !signatureInput
                        || signatureInput.value !== '';

                    const latitudeInput = document.getElementById(
                        'delivery_latitude'
                    );

                    const hasLocation =
                        !latitudeInput
                        || latitudeInput.value !== '';

                    if (!hasSignature && !hasLocation) {
                        event.preventDefault();

                        alert(
                            'Tambahkan tanda tangan atau ambil lokasi terlebih dahulu.'
                        );
                    }
                });
            }
        });
        </script>

    <?php else: ?>

        <form
            method="post"
            action="<?= site_url(
                'warehouse/pod/' . $shipment['id']
            ) ?>"
            enctype="multipart/form-data"
        >
            <?= csrf_field() ?>

            <div class="card shadow-sm">
                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Delivered To *
                            </label>

                            <input
                                type="text"
                                name="delivered_to"
                                class="form-control"
                                value="<?= esc(old('delivered_to')) ?>"
                                maxlength="150"
                                placeholder="Nama orang yang menerima"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Relation
                            </label>

                            <select
                                name="delivery_relation"
                                class="form-select"
                            >
                                <option value="">
                                    -- Select Relation --
                                </option>
                                <option value="SELF">Receiver</option>
                                <option value="FAMILY">Family</option>
                                <option value="EMPLOYEE">Employee</option>
                                <option value="SECURITY">Security</option>
                                <option value="RECEPTIONIST">
                                    Receptionist
                                </option>
                                <option value="NEIGHBOR">Neighbor</option>
                                <option value="OTHER">Other</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Delivered At *
                            </label>

                            <input
                                type="datetime-local"
                                name="delivered_at"
                                class="form-control"
                                value="<?= date('Y-m-d\TH:i') ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                POD Photo *
                            </label>

                            <input
                                type="file"
                                name="pod_photo"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                                required
                            >

                            <small class="text-muted">
                                JPG, PNG, atau WEBP. Maksimal 5 MB.
                            </small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Receiver Signature
                            </label>

                            <input
                                type="file"
                                name="pod_signature"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <small class="text-muted">
                                Opsional. Maksimal 3 MB.
                            </small>
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Delivery Notes
                            </label>

                            <textarea
                                name="delivery_notes"
                                class="form-control"
                                rows="3"
                                maxlength="1000"
                                placeholder="Catatan kurir atau kondisi penerimaan"
                            ><?= esc(old('delivery_notes')) ?></textarea>
                        </div>

                        <div class="col-12">
                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="return confirm(
                                    'Simpan Proof of Delivery ini?'
                                );"
                            >
                                ✅ Save Proof of Delivery
                            </button>
                        </div>

                    </div>

                </div>
            </div>

        </form>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>
