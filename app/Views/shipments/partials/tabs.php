<ul class="nav nav-tabs" role="tablist">

<li class="nav-item">
<a class="nav-link active"
data-bs-toggle="tab"
href="#shipment">

📦 Shipment

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#pricing">

💰 Pricing

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#vendor">

🏢 Vendor

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#tracking">

📍 Tracking

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#billing">

💳 Billing

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#history">

📝 History

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#attachment">

📎 Attachment

</a>
</li>

<li class="nav-item">
<a class="nav-link"
data-bs-toggle="tab"
href="#api">

🔗 API Log

</a>
</li>

</ul>

<div class="tab-content border border-top-0 p-4 bg-white">

<div class="tab-pane fade show active"
id="shipment">

<?= $this->include('shipments/partials/shipment') ?>

</div>

<div class="tab-pane fade"
id="pricing">

<?= $this->include('shipments/partials/pricing') ?>

</div>

<div class="tab-pane fade"
id="vendor">

<?= $this->include('shipments/partials/vendor') ?>

</div>

<div class="tab-pane fade"
id="tracking">

<?= $this->include('shipments/partials/tracking') ?>

</div>

<div class="tab-pane fade"
id="billing">

<?= $this->include('shipments/partials/billing') ?>

</div>

<div class="tab-pane fade"
id="history">

<?= $this->include('shipments/partials/history') ?>

</div>

<div class="tab-pane fade"
id="attachment">

<?= $this->include('shipments/partials/attachment') ?>

</div>

<div class="tab-pane fade"
id="api">

<?= $this->include('shipments/partials/api_log') ?>

</div>

</div>