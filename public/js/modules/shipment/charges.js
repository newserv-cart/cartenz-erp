/*
=========================================================
CARTENZ ERP
Shipment Charges Module
Sprint 49.7B
=========================================================
*/

(function (window, $) {
    "use strict";

    const ShipmentCharges = {
        initialized: false,

        init() {
            if (this.initialized) {
                return;
            }

            this.initialized = true;

            this.bindEvents();
            this.initializeCollapse();
            this.refreshServiceUI();
            this.calculateGrandTotal();

            console.log("✅ Charges Module Loaded");
        },

        bindEvents() {
            const chargeSelectors = [
                "#insurance_customer",
                "#packing_customer",
                "#additional_customer",
                "#discount_amount"
            ].join(",");

            $(document)
                .off(
                    "input.shipmentCharges change.shipmentCharges",
                    chargeSelectors
                )
                .on(
                    "input.shipmentCharges change.shipmentCharges",
                    chargeSelectors,
                    () => {
                        this.calculateGrandTotal();
                    }
                );

            $(document)
                .off(
                    "change.shipmentChargesUI",
                    "#service_id, #is_remote"
                )
                .on(
                    "change.shipmentChargesUI",
                    "#service_id, #is_remote",
                    () => {
                        this.refreshServiceUI();
                    }
                );

            $(document)
                .off(
                    "shipment:pricing:updated.shipmentCharges"
                )
                .on(
                    "shipment:pricing:updated.shipmentCharges",
                    () => {
                        this.calculateGrandTotal();
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

        calculateGrandTotal() {
            const shipment =
                this.value("selling_amount");

            const insurance =
                this.value("insurance_customer");

            const packing =
                this.value("packing_customer");

            const additional =
                this.value("additional_customer");

            const discount =
                this.value("discount_amount");

            const otherCharges = Math.max(
                0,
                insurance +
                packing +
                additional
            );

            const customerTotal = Math.max(
                0,
                shipment +
                otherCharges -
                discount
            );

            $("#other_charge_text").text(
                this.money(otherCharges)
            );

            $("#grand_total_text").text(
                this.money(customerTotal)
            );

            $("#grand_total").val(customerTotal);
            $("#customer_total").val(customerTotal);

            const state = {
                shipment,
                insurance,
                packing,
                additional,
                discount,
                otherCharges,
                customerTotal
            };

            window.ShipmentChargesState = state;

            $(document).trigger(
                "shipment:charges:updated",
                [state]
            );

            return state;
        },

        initializeCollapse() {
            const $collapse =
                $("#chargesCollapse");

            if (!$collapse.length) {
                return;
            }

            $collapse
                .off(
                    "show.bs.collapse.shipmentCharges " +
                    "hide.bs.collapse.shipmentCharges"
                )
                .on(
                    "show.bs.collapse.shipmentCharges",
                    () => {
                        $("#chargesIcon").text("▲");
                    }
                )
                .on(
                    "hide.bs.collapse.shipmentCharges",
                    () => {
                        $("#chargesIcon").text("▼");
                    }
                );
        },

        refreshServiceUI() {
            const selected =
                document.querySelector(
                    "#service_id option:checked"
                );

            const engine = String(
                selected?.dataset.engine ||
                selected?.dataset.serviceCode ||
                selected?.textContent ||
                "REGULAR"
            ).toUpperCase();

            $("#expressCharges").toggle(
                engine.includes("EXPRESS")
            );
        }
    };

    window.ShipmentCharges =
        ShipmentCharges;

    window.initializeCharges = function () {
        return window.ShipmentCharges.init();
    };

    window.calculateGrandTotal = function () {
        return window.ShipmentCharges
            .calculateGrandTotal();
    };

    window.refreshChargesUI = function () {
        return window.ShipmentCharges
            .refreshServiceUI();
    };
})(window, jQuery);
