/*
=========================================================
CARTENZ ERP
Shipment Profit Module
Sprint 49.7B
=========================================================
*/

(function (window, $) {
    "use strict";

    const ShipmentProfit = {
        initialized: false,

        init() {
            if (this.initialized) {
                return;
            }

            this.initialized = true;

            this.bindEvents();
            this.calculate();

            console.log("✅ Profit Module Loaded");
        },

        bindEvents() {
            const vendorSelectors = [
                "#insurance_vendor",
                "#packing_vendor",
                "#additional_vendor"
            ].join(",");

            $(document)
                .off(
                    "input.shipmentProfit change.shipmentProfit",
                    vendorSelectors
                )
                .on(
                    "input.shipmentProfit change.shipmentProfit",
                    vendorSelectors,
                    () => {
                        this.calculate();
                    }
                );

            $(document)
                .off(
                    "shipment:charges:updated.shipmentProfit"
                )
                .on(
                    "shipment:charges:updated.shipmentProfit",
                    () => {
                        this.calculate();
                    }
                );

            $(document)
                .off(
                    "shipment:pricing:updated.shipmentProfit"
                )
                .on(
                    "shipment:pricing:updated.shipmentProfit",
                    () => {
                        this.calculate();
                    }
                );
        },

        number(value) {
            const parsed = parseFloat(value);

            return Number.isFinite(parsed)
                ? parsed
                : 0;
        },

        value(id) {
            return this.number(
                document.getElementById(id)?.value
            );
        },

        money(value) {
            const amount = this.number(value);

            return "Rp " +
                amount.toLocaleString("id-ID");
        },

        calculate() {
            const customerTotal =
                this.value("grand_total");

            const vendorFreight =
                this.value("vendor_total");

            const vendorInsurance =
                this.value("insurance_vendor");

            const vendorPacking =
                this.value("packing_vendor");

            const vendorAdditional =
                this.value("additional_vendor");

            const vendorTotal = Math.max(
                0,
                vendorFreight +
                vendorInsurance +
                vendorPacking +
                vendorAdditional
            );

            const profit =
                customerTotal -
                vendorTotal;

            const margin =
                customerTotal > 0
                    ? (profit / customerTotal) * 100
                    : 0;

            $("#vendor_total_charge").val(
                vendorTotal
            );

            $("#profit_amount").val(profit);

            $("#vendor_cost_text").text(
                this.money(vendorTotal)
            );

            $("#profit_text").text(
                this.money(profit)
            );

            $("#margin_text").text(
                margin.toFixed(2) + " %"
            );

            const state = {
                customerTotal,
                vendorFreight,
                vendorInsurance,
                vendorPacking,
                vendorAdditional,
                vendorTotal,
                profit,
                margin
            };

            window.ShipmentProfitState = state;

            $(document).trigger(
                "shipment:profit:updated",
                [state]
            );

            return state;
        }
    };

    window.ShipmentProfit =
        ShipmentProfit;

    window.initializeProfit = function () {
        return window.ShipmentProfit.init();
    };

    window.calculateVendorTotal = function () {
        return window.ShipmentProfit.calculate();
    };

    window.calculateProfit = function () {
        return window.ShipmentProfit.calculate();
    };
})(window, jQuery);
