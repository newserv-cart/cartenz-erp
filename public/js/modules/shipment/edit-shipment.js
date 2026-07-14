/*
=========================================================
CARTENZ ERP
Shipment Edit Address & Hydration Module
Sprint 49.12A
=========================================================
*/

(function (window, document, $) {
    "use strict";

    const ShipmentEdit = {
        initialized: false,
        dirty: false,
        submitting: false,
        receiverOptions: [],

        init() {
            if (this.initialized) {
                return;
            }

            const form =
                document.getElementById("shipmentForm");

            if (
                !form
                || form.dataset.formMode !== "edit"
            ) {
                return;
            }

            this.initialized = true;

            this.captureReceiverOptions();
            this.hydrateSender();
            this.filterReceivers({
                preserveSelection: true,
                hydrateSelection: true
            });
            this.preserveStoredPricing();
            this.bindEvents(form);

            console.log(
                "✅ Shipment Edit Module Loaded"
            );
        },

        captureReceiverOptions() {
            const select =
                document.getElementById("receiver_id");

            if (!select) {
                return;
            }

            this.receiverOptions = [
                ...select.options
            ].map((option) => option.cloneNode(true));
        },

        selectedOption(selectId) {
            const select =
                document.getElementById(selectId);

            if (!select) {
                return null;
            }

            return select.options[
                select.selectedIndex
            ] || null;
        },

        hydrateSender() {
            const option =
                this.selectedOption("customer_id");

            if (!option || !option.value) {
                if (
                    window.ShipmentSender
                    && typeof window.ShipmentSender.clear
                        === "function"
                ) {
                    window.ShipmentSender.clear();
                }

                return;
            }

            if (
                window.ShipmentSender
                && typeof window.ShipmentSender.fill
                    === "function"
            ) {
                window.ShipmentSender.fill(
                    option.dataset
                );
                return;
            }

            const data = option.dataset;

            $("#real_customer_id").val(
                data.customerId || ""
            );
            $("#sender_company").val(
                data.company || ""
            );
            $("#sender_phone").val(
                data.phone || ""
            );
            $("#sender_email").val(
                data.email || ""
            );
            $("#sender_identity").val(
                data.identity || ""
            );
            $("#sender_province").val(
                data.state || ""
            );
            $("#sender_city").val(
                data.city || ""
            );
            $("#sender_postal_code").val(
                data.postalCode || ""
            );
            $("#sender_country").val(
                data.country || ""
            );
            $("#sender_address").val(
                data.address || ""
            );
        },

        hydrateReceiver() {
            const option =
                this.selectedOption("receiver_id");

            if (!option || !option.value) {
                if (
                    window.ShipmentReceiver
                    && typeof window.ShipmentReceiver.clear
                        === "function"
                ) {
                    window.ShipmentReceiver.clear();
                }

                return;
            }

            const senderCustomerId =
                String(
                    $("#real_customer_id").val() || ""
                );

            const receiverCustomerId =
                String(
                    option.dataset.customerId || ""
                );

            if (
                senderCustomerId
                && receiverCustomerId
                && senderCustomerId !== receiverCustomerId
            ) {
                $("#receiver_id").val("");

                if (
                    window.ShipmentReceiver
                    && typeof window.ShipmentReceiver.clear
                        === "function"
                ) {
                    window.ShipmentReceiver.clear();
                }

                return;
            }

            if (
                window.ShipmentReceiver
                && typeof window.ShipmentReceiver.fill
                    === "function"
            ) {
                window.ShipmentReceiver.fill(
                    option.dataset
                );
                return;
            }

            const data = option.dataset;

            $("#receiver_company").val(
                data.company || ""
            );
            $("#receiver_phone").val(
                data.phone || ""
            );
            $("#receiver_email").val(
                data.email || ""
            );
            $("#receiver_identity").val(
                data.identity || ""
            );
            $("#receiver_province").val(
                data.state || ""
            );
            $("#receiver_city").val(
                data.city || ""
            );
            $("#receiver_postal_code").val(
                data.postalCode || ""
            );
            $("#receiver_country").val(
                data.country || ""
            );
            $("#receiver_address").val(
                data.address || ""
            );
            $("#country_id")
                .val(data.countryId || "")
                .trigger("change");
        },

        filterReceivers(options = {}) {
            const {
                preserveSelection = false,
                hydrateSelection = false
            } = options;

            const select =
                document.getElementById("receiver_id");

            if (!select) {
                return;
            }

            const customerId =
                String(
                    $("#real_customer_id").val() || ""
                );

            const previousValue =
                preserveSelection
                    ? String(select.value || "")
                    : "";

            select.replaceChildren();

            this.receiverOptions.forEach(
                (storedOption) => {
                    const option =
                        storedOption.cloneNode(true);

                    const optionCustomerId =
                        String(
                            option.dataset.customerId || ""
                        );

                    if (
                        !option.value
                        || (
                            customerId
                            && optionCustomerId === customerId
                        )
                    ) {
                        option.selected =
                            previousValue !== ""
                            && option.value === previousValue;

                        select.appendChild(option);
                    }
                }
            );

            const selectedStillExists =
                previousValue !== ""
                && [...select.options].some(
                    (option) =>
                        option.value === previousValue
                );

            if (!selectedStillExists) {
                select.value = "";
            }

            select.disabled = !customerId;

            if (hydrateSelection) {
                this.hydrateReceiver();
            }
        },

        preserveStoredPricing() {
            const pricing =
                window.ShipmentPricing;

            if (!pricing) {
                return;
            }

            clearTimeout(pricing.timer);
            pricing.timer = null;

            if (
                typeof pricing.cancel ===
                "function"
            ) {
                pricing.cancel();
            }

            if (
                typeof pricing.collectData ===
                "function"
            ) {
                pricing.lastHash = JSON.stringify(
                    pricing.collectData()
                );
            }
        },

        requestPricing() {
            const pricing =
                window.ShipmentPricing;

            if (!pricing) {
                return;
            }

            if (
                typeof pricing.schedule ===
                "function"
            ) {
                pricing.schedule();
                return;
            }

            if (
                typeof pricing.load ===
                "function"
            ) {
                pricing.load(true);
            }
        },

        bindEvents(form) {
            $(document)
                .off(
                    "change.shipmentEditSender",
                    "#customer_id"
                )
                .on(
                    "change.shipmentEditSender",
                    "#customer_id",
                    () => {
                        this.hydrateSender();
                        this.filterReceivers({
                            preserveSelection: true,
                            hydrateSelection: true
                        });

                        this.dirty = true;
                    }
                );

            $(document)
                .off(
                    "change.shipmentEditReceiver",
                    "#receiver_id"
                )
                .on(
                    "change.shipmentEditReceiver",
                    "#receiver_id",
                    () => {
                        this.hydrateReceiver();
                        this.dirty = true;
                        this.requestPricing();
                    }
                );

            $(form)
                .off(
                    "input.shipmentEdit " +
                    "change.shipmentEdit"
                )
                .on(
                    "input.shipmentEdit " +
                    "change.shipmentEdit",
                    "input, select, textarea",
                    () => {
                        this.dirty = true;
                    }
                );

            $(document)
                .off(
                    "click.shipmentEditReprice",
                    "#btnRecalculatePricing"
                )
                .on(
                    "click.shipmentEditReprice",
                    "#btnRecalculatePricing",
                    () => {
                        if (
                            window.ShipmentPricing
                            && typeof
                                window.ShipmentPricing
                                    .load
                                === "function"
                        ) {
                            window.ShipmentPricing
                                .load(true);
                        }
                    }
                );

            form.addEventListener(
                "submit",
                (event) => {
                    const senderCustomerId =
                        String(
                            $("#real_customer_id").val()
                            || ""
                        );

                    const receiverOption =
                        this.selectedOption(
                            "receiver_id"
                        );

                    const receiverCustomerId =
                        String(
                            receiverOption?.dataset.customerId
                            || ""
                        );

                    if (
                        !receiverOption
                        || !receiverOption.value
                    ) {
                        event.preventDefault();

                        alert(
                            "Pilih Receiver milik customer yang sama."
                        );
                        return;
                    }

                    if (
                        senderCustomerId !==
                        receiverCustomerId
                    ) {
                        event.preventDefault();

                        alert(
                            "Sender dan Receiver harus milik customer yang sama."
                        );
                        return;
                    }

                    this.submitting = true;

                    const button =
                        document.getElementById(
                            "btnSaveShipment"
                        );

                    if (button) {
                        button.disabled = true;
                        button.textContent =
                            "Saving...";
                    }
                }
            );

            window.addEventListener(
                "beforeunload",
                (event) => {
                    if (
                        !this.dirty
                        || this.submitting
                    ) {
                        return;
                    }

                    event.preventDefault();
                    event.returnValue = "";
                }
            );
        }
    };

    window.ShipmentEdit =
        ShipmentEdit;

    document.addEventListener(
        "DOMContentLoaded",
        function () {
            window.setTimeout(
                () => ShipmentEdit.init(),
                0
            );
        },
        { once: true }
    );
})(window, document, jQuery);
