/*
=========================================
CARTENZ ERP
Shipment Module Bootstrap
=========================================
*/

window.Shipment = {
    init() {
        console.log('✅ Shipment Module Loaded');

        if (typeof initializeSender === 'function') {
            initializeSender();
        }

        if (typeof initializeReceiver === 'function') {
            initializeReceiver();
        }

        if (typeof initializeSummary === "function") {
             initializeSummary();
        }

if (typeof initializeColly === "function") {
    initializeColly();
}

if (typeof window.initializeItems === "function") {
        window.initializeItems();
    }

        if (typeof initializePackage === 'function') {
            initializePackage();
        }

        if (typeof initializePricing === 'function') {
            initializePricing();
        }

        if (typeof initializeCharges === 'function') {
            initializeCharges();
        }

        if (typeof initializeProfit === 'function') {
            initializeProfit();
        }

        if (typeof initializeCommodityRule === 'function') {
            initializeCommodityRule();
        }
    }
};

document.addEventListener('DOMContentLoaded', function () {
    window.Shipment.init();
});

// =====================================================
// GLOBAL MODAL FOCUS GUARD
// Menghilangkan warning aria-hidden Bootstrap
// =====================================================
if (!window.CartenzModalFocusGuard) {
    window.CartenzModalFocusGuard = true;

    document.addEventListener("hide.bs.modal", function (event) {
        const modal = event.target;
        const activeElement = document.activeElement;

        if (
            modal instanceof HTMLElement &&
            activeElement instanceof HTMLElement &&
            modal.contains(activeElement)
        ) {
            activeElement.blur();
        }
    });

    document.addEventListener("hidden.bs.modal", function (event) {
        const modalId = event.target.id;

        if (modalId === "senderModal") {
            document
                .getElementById("btnAddCustomer")
                ?.focus();
        }

        if (modalId === "receiverModal") {
            document
                .getElementById("btnAddReceiver")
                ?.focus();
        }
    });
}