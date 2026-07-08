<div class="row">

    <div class="col-md-3 mb-3">

        <div class="card shadow border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Today's Shipment
                        </small>

                        <h2 class="fw-bold text-primary">
                            <?= $todayShipments ?>
                        </h2>

                    </div>

                    <div class="display-5">
                        📦
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-3 mb-3">

        <div class="card shadow border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Revenue
                        </small>

                        <h4 class="fw-bold text-success">
                            Rp <?= number_format($todayRevenue) ?>
                        </h4>

                    </div>

                    <div class="display-5">
                        💰
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-3 mb-3">

        <div class="card shadow border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Manifest
                        </small>

                        <h2 class="fw-bold text-warning">
                            <?= $todayManifest ?>
                        </h2>

                    </div>

                    <div class="display-5">
                        🚚
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="col-md-3 mb-3">

        <div class="card shadow border-0">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small class="text-muted">
                            Pending Pickup
                        </small>

                        <h2 class="fw-bold text-danger">
                            <?= $pendingPickup ?>
                        </h2>

                    </div>

                    <div class="display-5">
                        📍
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>