/*
=========================================================
CARTENZ ERP
Shipment Pricing Module
Sprint 49.7A
=========================================================
*/

(function (window, $) {
    "use strict";

    const ShipmentPricing = {
        initialized: false,
        timer: null,
        xhr: null,
        lastHash: "",

        init() {
            if (this.initialized) return;

            this.initialized = true;
            this.bindEvents();

            console.log("✅ Pricing Module Loaded");
        },

        bindEvents() {
            const selectors = [
                "#customer_id",
                "#shipment_date",
                "#receiver_id",
                "#country_id",
                "#service_id",
                "#commodity_type_id",
                "#transport_mode_id",
                "#is_remote",
                "#insurance_customer",
                "#packing_customer",
                "#additional_customer",
                "#discount_amount",
                ".berat",
                ".panjang",
                ".lebar",
                ".tinggi"
            ].join(",");

            $(document)
                .off(
                    "input.shipmentPricing change.shipmentPricing",
                    selectors
                )
                .on(
                    "input.shipmentPricing change.shipmentPricing",
                    selectors,
                    () => this.schedule()
                );
        },

        number(value) {
            const parsed = parseFloat(value);
            return Number.isFinite(parsed) ? parsed : 0;
        },

        money(value) {
            if (
                window.ShipmentUtils &&
                typeof window.ShipmentUtils.formatRupiah === "function"
            ) {
                return window.ShipmentUtils.formatRupiah(value);
            }

            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0
            }).format(this.number(value));
        },

        endpoint() {
            const baseUrl = String(
                window.BASE_URL ||
                window.BASEURL ||
                window.baseUrl ||
                ""
            ).replace(/\/+$/, "");

            return (
                window.SHIPMENT_PRICING_ENDPOINT ||
                baseUrl + "/pricing/calculate"
            );
        },

        collectData() {
            const actualWeight =
                this.number($("#total_berat").val());

            const chargeableWeight =
                this.number($("#total_chargeable").val());

            const discount =
                this.number($("#discount_amount").val());

            const senderOption =
    document.querySelector(
        "#customer_id option:checked"
    );

const customerId =
    senderOption?.dataset.customerId ||
    $("#real_customer_id").val() ||
    "";

if (customerId) {
    $("#real_customer_id").val(customerId);
}    

            return {
                customer_id: customerId,
                shipment_date: $("#shipment_date").val() || "",
                receiver_id: $("#receiver_id").val() || "",
                country_id: $("#country_id").val() || "",
                service_id: $("#service_id").val() || "",
                commodity_type_id:
                    $("#commodity_type_id").val() || 0,
                transport_mode_id:
                    $("#transport_mode_id").val() || 1,
                is_remote: $("#is_remote").val() || 0,

                actual_weight: actualWeight,

                /*
                 * Backend memakai max(actual_weight, volume_weight).
                 * total_chargeable dikirim sebagai volume_weight agar
                 * hasil multi-colly tetap mengikuti chargeable summary.
                 */
                volume_weight: chargeableWeight,
                chargeable_weight: chargeableWeight,

                insurance_amount:
                    this.number($("#insurance_customer").val()),
                packing_charge:
                    this.number($("#packing_customer").val()),
                additional_charge:
                    this.number($("#additional_customer").val()),

                discount: discount,
                discount_amount: discount,

                receiver_city:
                    $("#receiver_city").val() || "",
                receiver_postcode:
                    $("#receiver_postal_code").val() || ""
            };
        },

        isReady(data) {
            return Boolean(
                data.customer_id &&
                data.country_id &&
                data.service_id &&
                data.chargeable_weight > 0
            );
        },

        schedule(delay = 350) {
            clearTimeout(this.timer);

            this.timer = setTimeout(
                () => this.load(),
                delay
            );
        },

        load(force = false) {
            const data = this.collectData();

            if (!this.isReady(data)) {
                this.cancel();
                this.lastHash = "";
                return;
            }

            const hash = JSON.stringify(data);

            if (!force && hash === this.lastHash) {
                return;
            }

            this.lastHash = hash;
            this.cancel();

            this.xhr = $.ajax({
                url: this.endpoint(),
                type: "POST",
                dataType: "json",
                data: data,

                success: (response) => {
                    const normalized =
                        this.normalizeResponse(response);

                    if (!normalized.success) {
                        this.lastHash = "";

                        console.warn(
                            "Pricing error:",
                            normalized.message
                        );

                        return;
                    }

                    this.applyResult(normalized.data);
                },

                error: (xhr, status, error) => {
                    if (status === "abort") return;

                    this.lastHash = "";

                    console.error(
                        "Pricing AJAX error:",
                        error,
                        xhr.responseText
                    );
                },

                complete: () => {
                    this.xhr = null;
                }
            });
        },

        cancel() {
            if (!this.xhr) return;

            this.xhr.abort();
            this.xhr = null;
        },

        normalizeResponse(response) {
            if (!response || typeof response !== "object") {
                return {
                    success: false,
                    message: "Response pricing tidak valid.",
                    data: {}
                };
            }

            if (
                response.status &&
                response.status !== "success"
            ) {
                return {
                    success: false,
                    message:
                        response.message ||
                        "Gagal menghitung pricing.",
                    data: {}
                };
            }

            if (response.success === false) {
                return {
                    success: false,
                    message:
                        response.message ||
                        "Gagal menghitung pricing.",
                    data: {}
                };
            }

            return {
                success: true,
                message: "",
                data:
                    response.data &&
                    typeof response.data === "object"
                        ? response.data
                        : response
            };
        },

        applyResult(payload) {
            const freight = this.number(
                payload.freight ??
                payload.base_tariff?.selling_rate
            );

            const fuel =
                this.number(payload.fuel);

            const remote =
                this.number(payload.remote);

            const goGreen = this.number(
                payload.gogreen ??
                payload.go_green
            );

            const insurance =
                this.number(payload.insurance);

            const packing =
                this.number(payload.packing);

            const additional =
                this.number(
                    payload.additional ??
                    payload.customer_charge_total
                );

            const discount =
                this.number(payload.discount);

            const customerTotal =
                this.number(
                    payload.customer_total ??
                    payload.total
                );

            const vendorTotal =
                this.number(
                    payload.vendor_total ??
                    payload.vendor_cost
                );

            /*
             * Charges Module lama menghitung:
             * selling + insurance + packing + additional - discount.
             * Karena itu selling_amount diisi subtotal sistem supaya
             * hasil akhirnya sama dengan customer_total backend.
             */
            const systemSubtotal = Math.max(
                0,
                customerTotal -
                insurance -
                packing -
                additional +
                discount
            );

            $("#selling_amount").val(systemSubtotal);
            $("#vendor_total").val(vendorTotal);

            $("#insurance_customer").val(insurance);
            $("#packing_customer").val(packing);
            $("#additional_customer").val(additional);
            $("#discount_amount").val(discount);

            $("#shipment_charge_text").text(
                this.money(freight)
            );

            $("#remote_charge_text").text(
                this.money(remote)
            );

            window.ShipmentPricingState = {
                freight,
                fuel,
                remote,
                gogreen: goGreen,
                insurance,
                packing,
                additional,
                discount,
                customerTotal,
                vendorTotal,
                profit: this.number(payload.profit),
                margin: this.number(
                    payload.margin ??
                    payload.margin_percent
                ),
                markup: this.number(
                    payload.markup ??
                    payload.markup_percent
                ),
                warnings: Array.isArray(payload.warnings)
                    ? payload.warnings
                    : []
            };

            if (
                typeof window.calculateGrandTotal ===
                "function"
            ) {
                window.calculateGrandTotal();
            }

            if (
                typeof window.calculateVendorTotal ===
                "function"
            ) {
                window.calculateVendorTotal();
            }

            $(document).trigger(
                "shipment:pricing:updated",
                [window.ShipmentPricingState]
            );
        }
    };

    window.ShipmentPricing = ShipmentPricing;

    window.initializePricing = function () {
        return window.ShipmentPricing.init();
    };

    window.loadPricing = function () {
        return window.ShipmentPricing.load(true);
    };

    window.calculatePricing = function () {
        return window.ShipmentPricing.load(true);
    };

    window.triggerLoadRate = function () {
        return window.ShipmentPricing.schedule();
    };

    window.loadShipmentRate = function () {
        return window.ShipmentPricing.schedule();
    };
})(window, jQuery);
