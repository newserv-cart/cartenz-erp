<ul class="nav nav-tabs flex-nowrap overflow-auto" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#shipment" type="button">
            📦 Shipment
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pricing" type="button">
            💰 Pricing
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vendor" type="button">
            🏢 Vendor
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tracking" type="button">
            📍 Tracking
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button">
            💳 Billing
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">
            📝 History
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attachment" type="button">
            📎 Attachment
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#api" type="button">
            🔗 API Log
        </button>
    </li>
</ul>

<div class="tab-content border border-top-0 bg-white p-3 p-lg-4">
    <div class="tab-pane fade show active" id="shipment" role="tabpanel">
        <?= $this->include('shipments/partials/shipment') ?>
    </div>

    <div class="tab-pane fade" id="pricing" role="tabpanel">
        <?= $this->include('shipments/partials/pricing') ?>
    </div>

    <div class="tab-pane fade" id="vendor" role="tabpanel">
        <?= $this->include('shipments/partials/vendor') ?>
    </div>

    <div class="tab-pane fade" id="tracking" role="tabpanel">
        <?= $this->include('shipments/partials/tracking') ?>
    </div>

    <div class="tab-pane fade" id="billing" role="tabpanel">
        <?= $this->include('shipments/partials/billing') ?>
    </div>

    <div class="tab-pane fade" id="history" role="tabpanel">
        <?= $this->include('shipments/partials/history') ?>
    </div>

    <div class="tab-pane fade" id="attachment" role="tabpanel">
        <?= $this->include('shipments/partials/attachment') ?>
    </div>

    <div class="tab-pane fade" id="api" role="tabpanel">
        <?= $this->include('shipments/partials/api_log') ?>
    </div>
</div>
