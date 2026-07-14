<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">🔗 API Log</h5>
    </div>

    <div class="card-body">
        <?php if (empty($shipment['vendor_id'])): ?>
            <div class="alert alert-info mb-0">
                Shipment belum memiliki vendor, sehingga belum ada proses API vendor.
            </div>
        <?php else: ?>
            <div class="alert alert-secondary mb-0">
                Belum ada API log yang tersedia untuk shipment ini.
            </div>
        <?php endif; ?>
    </div>
</div>
