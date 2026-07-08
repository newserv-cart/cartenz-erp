<div class="card shadow-sm mb-4">

    <div class="card-body">

        <div class="row text-center">

            <div class="col">
                <a href="<?= site_url('shipments/create') ?>"
                   class="btn btn-primary w-100">
                    📦<br>Create Shipment
                </a>
            </div>

            <div class="col">
                <a href="<?= site_url('manifests/create') ?>"
                   class="btn btn-success w-100">
                    🚚<br>Create Manifest
                </a>
            </div>

            <div class="col">
                <a href="<?= site_url('pickups/create') ?>"
                   class="btn btn-warning w-100">
                    🚛<br>Pickup
                </a>
            </div>

            <div class="col">
                <a href="<?= site_url('invoices/create') ?>"
                   class="btn btn-info w-100">
                    💰<br>Invoice
                </a>
            </div>

            <div class="col">
                <a href="<?= site_url('customers/create') ?>"
                   class="btn btn-secondary w-100">
                    👤<br>Customer
                </a>
            </div>

        </div>

    </div>

</div>