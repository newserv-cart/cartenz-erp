<div class="card shadow-sm border-0">
    <div class="card-header bg-info text-dark">
        <h5 class="mb-0">📎 Shipment Attachments</h5>
    </div>

    <div class="card-body">
        <div class="alert alert-info mb-0">
            Belum ada attachment untuk shipment
            <strong><?= esc($shipment['awb_no'] ?? '-') ?></strong>.
            Modul upload dokumen akan dihubungkan pada sprint berikutnya.
        </div>
    </div>
</div>
