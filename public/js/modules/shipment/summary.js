/*
====================================================
CARTENZ ERP
Shipment Summary Module
Sprint 49.4
====================================================
*/

window.ShipmentSummary = {

    initialized: false,

    init() {
        if (this.initialized) {
            return;
        }

        this.initialized = true;

        this.bindVolumeEvents();
        this.hitungSemua();

        console.log("✅ Summary Module Loaded");
    },

    // ==================================================
    // Event Weight dan Dimension
    // ==================================================
    bindVolumeEvents() {
        $(document)
            .off(
                "input.shipmentSummaryVolume",
                ".berat, .panjang, .lebar, .tinggi"
            )
            .on(
                "input.shipmentSummaryVolume",
                ".berat, .panjang, .lebar, .tinggi",
                function () {
                    window.ShipmentSummary.hitungVolume(this);
                }
            );
    },

    // ==================================================
    // Hitung Volume Per Colly
    // ==================================================
    hitungVolume(element) {
        const card = element.closest(
            ".card.border-primary"
        );

        if (!card) {
            return;
        }

        const panjang =
            parseFloat(
                card.querySelector(".panjang")?.value
            ) || 0;

        const lebar =
            parseFloat(
                card.querySelector(".lebar")?.value
            ) || 0;

        const tinggi =
            parseFloat(
                card.querySelector(".tinggi")?.value
            ) || 0;

        const berat =
            parseFloat(
                card.querySelector(".berat")?.value
            ) || 0;

        const volumeWeight =
            (panjang * lebar * tinggi) / 5000;

        const chargeableWeight =
            Math.max(berat, volumeWeight);

        const volumeElement =
            card.querySelector(".volume");

        const chargeableElement =
            card.querySelector(".chargeable");

        if (volumeElement) {
            volumeElement.value =
                volumeWeight.toFixed(2);
        }

        if (chargeableElement) {
            chargeableElement.value =
                chargeableWeight.toFixed(2);
        }

        this.hitungSemua();
    },

    // ==================================================
    // Hitung Summary
    // ==================================================
    hitungSemua() {
        let totalBerat = 0;
        let totalCharge = 0;
        let totalNilai = 0;

        document
            .querySelectorAll(".berat")
            .forEach((element) => {
                totalBerat +=
                    parseFloat(element.value) || 0;
            });

        document
            .querySelectorAll(".chargeable")
            .forEach((element) => {
                totalCharge +=
                    parseFloat(element.value) || 0;
            });

        document
            .querySelectorAll(".total_value")
            .forEach((element) => {
                totalNilai +=
                    parseFloat(element.value) || 0;
            });

        const totalColly =
            document.querySelectorAll(
                "#colly-container .card.border-primary"
            ).length;

        $("#total_berat").val(
            totalBerat.toFixed(2)
        );

        $("#total_chargeable").val(
            totalCharge.toFixed(2)
        );

        $("#total_nilai").val(
            totalNilai.toFixed(2)
        );

        $("#total_colly").val(totalColly);

        this.triggerPricing();

        return {
            total_colly: totalColly,
            total_weight: totalBerat,
            total_chargeable: totalCharge,
            total_declared_value: totalNilai
        };
    },

    // ==================================================
    // Trigger Pricing
    // ==================================================
    triggerPricing() {
        if (
            typeof window.triggerLoadRate ===
            "function"
        ) {
            window.triggerLoadRate();
            return;
        }

        if (
            typeof window.loadShipmentRate ===
            "function"
        ) {
            window.loadShipmentRate();
            return;
        }

        if (
            typeof window.calculatePricing ===
            "function"
        ) {
            window.calculatePricing();
        }
    }
};

// Compatibility untuk kode lama
window.hitungVolume = function (element) {
    return window.ShipmentSummary.hitungVolume(
        element
    );
};

window.hitungSemua = function () {
    return window.ShipmentSummary.hitungSemua();
};

window.initializeSummary = function () {
    window.ShipmentSummary.init();
};