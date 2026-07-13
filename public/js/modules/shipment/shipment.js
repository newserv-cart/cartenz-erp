/*
=========================================
CARTENZ ERP
Shipment Module Bootstrap
Sprint 49.7C
=========================================
*/

(function (window, document) {
    "use strict";

    const Shipment = {
        initialized: false,

        init() {
            if (this.initialized) {
                return;
            }

            this.initialized = true;

            this.loadConfiguration();
            this.initializeModules();

            console.log("✅ Shipment Module Loaded");
        },

        loadConfiguration() {
            const form =
                document.getElementById("shipmentForm");

            const baseUrl =
                form?.dataset.baseUrl || "";

            window.BASE_URL =
                String(baseUrl).replace(/\/+$/, "");

            window.userRole =
                form?.dataset.userRole || "user";
        },

        initializeModules() {
            /*
             * Urutan ini disengaja:
             * 1. Sender/Receiver menyiapkan data pihak.
             * 2. Charges/Profit siap menerima hasil Pricing.
             * 3. Pricing siap menerima perubahan Summary.
             * 4. Summary/Colly/Items menyiapkan paket.
             */
            const modules = [
                "initializeSender",
                "initializeReceiver",
                "initializeCharges",
                "initializeProfit",
                "initializePricing",
                "initializeSummary",
                "initializeColly",
                "initializeItems",
                "initializePackage",
                "initializeCommodityRule"
            ];

            modules.forEach((functionName) => {
                this.run(functionName);
            });
        },

        run(functionName) {
            const callback =
                window[functionName];

            if (typeof callback !== "function") {
                return;
            }

            try {
                callback();
            } catch (error) {
                console.error(
                    "❌ Gagal menjalankan " +
                    functionName,
                    error
                );
            }
        }
    };

    window.Shipment = Shipment;

    document.addEventListener(
        "DOMContentLoaded",
        function () {
            window.Shipment.init();
        },
        { once: true }
    );

    // =====================================================
    // GLOBAL MODAL FOCUS GUARD
    // =====================================================
    if (!window.CartenzModalFocusGuard) {
        window.CartenzModalFocusGuard = true;

        document.addEventListener(
            "hide.bs.modal",
            function (event) {
                const modal = event.target;
                const activeElement =
                    document.activeElement;

                if (
                    modal instanceof HTMLElement &&
                    activeElement instanceof HTMLElement &&
                    modal.contains(activeElement)
                ) {
                    activeElement.blur();
                }
            }
        );

        document.addEventListener(
            "hidden.bs.modal",
            function (event) {
                const modalId =
                    event.target.id;

                if (modalId === "senderModal") {
                    document
                        .getElementById(
                            "btnAddCustomer"
                        )
                        ?.focus();
                }

                if (modalId === "receiverModal") {
                    document
                        .getElementById(
                            "btnAddReceiver"
                        )
                        ?.focus();
                }
            }
        );
    }
})(window, document);
