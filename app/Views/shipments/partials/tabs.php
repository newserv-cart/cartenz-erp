<?php

$request = service('request');
$user    = auth()->user();
$actorId = (int) (auth()->id() ?? 0);

$isSuperAdmin = $user && $user->inGroup('superadmin');
$isCustomerUser = $user
    && $user->inGroup('user')
    && !$user->inGroup('admin')
    && !$user->inGroup('superadmin');

$operationalProfile = null;
$isHeadOffice        = $isSuperAdmin;
$isAgentBranch       = false;

try {
    if ($actorId > 0) {
        $context = new \App\Services\BranchOperationalContextService();
        $operationalProfile = $context->profileForUser($actorId);

        if (is_array($operationalProfile)) {
            $isHeadOffice = $isHeadOffice || $context->profileIsMain($operationalProfile);
            $isAgentBranch = !$isHeadOffice
                && (int) ($operationalProfile['agent_id'] ?? 0) > 0;
        }
    }
} catch (\Throwable $e) {
    $operationalProfile = null;
}

if ($isHeadOffice) {
    $tabs = [
        'shipment'   => ['📦 Shipment', 'shipments/partials/shipment'],
        'pricing'    => ['💰 Pricing', 'shipments/partials/pricing'],
        'vendor'     => ['🏢 Vendor', 'shipments/partials/vendor'],
        'tracking'   => ['📍 Tracking', 'shipments/partials/tracking'],
        'billing'    => ['💳 Billing', 'shipments/partials/billing'],
        'history'    => ['📝 History', 'shipments/partials/history'],
        'attachment' => ['📎 Attachment', 'shipments/partials/attachment'],
        'api'        => ['🔗 API Log', 'shipments/partials/api_log'],
    ];
} else {
    $tabs = [
        'shipment' => ['📦 Shipment', 'shipments/partials/shipment'],
        'tracking' => ['📍 Tracking', 'shipments/partials/tracking'],
        'charges'  => [
            $isAgentBranch ? '💳 Tagihan Agen' : '💳 Biaya Pengiriman',
            'shipments/partials/party_charges',
        ],
    ];
}

$allowedTabs = array_keys($tabs);
$requestedTab = strtolower(trim((string) ($request->getGet('tab') ?? '')));
$activeTab = in_array($requestedTab, $allowedTabs, true)
    ? $requestedTab
    : 'shipment';
?>

<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($tabs as $tabId => [$label, $partial]): ?>
        <li class="nav-item" role="presentation">
            <a
                class="nav-link <?= $activeTab === $tabId ? 'active' : '' ?>"
                data-bs-toggle="tab"
                href="#<?= esc($tabId) ?>"
                role="tab"
                aria-selected="<?= $activeTab === $tabId ? 'true' : 'false' ?>"
            >
                <?= esc($label) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="tab-content border border-top-0 p-4 bg-white">
    <?php foreach ($tabs as $tabId => [$label, $partial]): ?>
        <div
            class="tab-pane fade <?= $activeTab === $tabId ? 'show active' : '' ?>"
            id="<?= esc($tabId) ?>"
            role="tabpanel"
        >
            <?= $this->include($partial) ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allowedTabs = <?= json_encode($allowedTabs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    const params = new URLSearchParams(window.location.search);
    const hash = window.location.hash.replace('#', '').trim();
    let requestedTab = params.get('tab');

    if (!requestedTab && hash === 'add-vendor-tracking' && allowedTabs.includes('vendor')) {
        requestedTab = 'vendor';
    }

    if (!allowedTabs.includes(requestedTab)) {
        return;
    }

    const navContainer = document.querySelector('.nav-tabs');
    const contentContainer = document.querySelector('.tab-content');

    if (!navContainer || !contentContainer) {
        return;
    }

    const tabButton = navContainer.querySelector(
        '[href="#' + requestedTab + '"], [data-bs-target="#' + requestedTab + '"]'
    );
    const tabPane = document.getElementById(requestedTab);

    if (!tabButton || !tabPane) {
        return;
    }

    navContainer.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (button) {
        button.classList.remove('active');
        button.setAttribute('aria-selected', 'false');
    });

    contentContainer.querySelectorAll('.tab-pane').forEach(function (pane) {
        pane.classList.remove('show', 'active');
    });

    tabButton.classList.add('active');
    tabButton.setAttribute('aria-selected', 'true');
    tabPane.classList.add('show', 'active');

    if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        bootstrap.Tab.getOrCreateInstance(tabButton).show();
    }

    const anchorName = params.get('anchor') || hash;

    if (!anchorName) {
        return;
    }

    window.setTimeout(function () {
        const target = document.getElementById(anchorName);

        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 350);
});
</script>
