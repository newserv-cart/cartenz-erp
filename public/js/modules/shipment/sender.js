/*
====================================================
CARTENZ ERP
Shipment Sender Module
Sprint 49.2
====================================================
*/

window.ShipmentSender = {

    initialized: false,

    init() {
        // Mencegah event terpasang dua kali
        if (this.initialized) {
            return;
        }

        this.initialized = true;

        this.bindCustomer();
        this.bindModal();
        this.bindSave();

        console.log("Sender Module Loaded");
    },

    // ==================================================
    // DROPDOWN SENDER
    // ==================================================
    bindCustomer() {
        $(document)
            .off("change.shipmentSender", "#customer_id")
            .on(
                "change.shipmentSender",
                "#customer_id",
                function () {
                    const option =
                        this.options[this.selectedIndex];

                    if (!option || !option.value) {
                        window.ShipmentSender.clear();
                        return;
                    }

                    window.ShipmentSender.fill(
                        option.dataset
                    );
                }
            );
    },

    // ==================================================
    // ISI FORM UTAMA
    // ==================================================
    fill(data) {

        $("#real_customer_id").val(
            data.customerId ||
            data.customer_id ||
            ""
        );

        $("#sender_address_id").val(
            data.addressId ||
            data.address_id ||
            ""
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

        $("#sender_city").val(
            data.city || ""
        );

        $("#sender_province").val(
            data.state || data.province || ""
        );

        const postalValue =
            data.postalCode ||
            data.postal ||
            "";

        $("#sender_postal").val(postalValue);
        $("#sender_postal_code").val(postalValue);

        $("#sender_address").val(
            data.address || ""
        );

        $("#sender_identity").val(
            data.identity || ""
        );

        $("#sender_country").val(
            data.country ||
            data.countryName ||
            ""
        );

        $("#sender_country_code").val(
            data.countryCode ||
            data.country_code ||
            ""
        );

        $("#sender_country_zone").val(
            data.countryZone ||
            data.country_zone ||
            data.expressZoneCode ||
            data.express_zone_code ||
            data.zoneCode ||
            ""
        );
    },

    // ==================================================
    // KOSONGKAN FORM UTAMA
    // ==================================================
    clear() {

        $("#real_customer_id").val("");
        $("#sender_address_id").val("");
        $("#sender_company").val("");
        $("#sender_phone").val("");
        $("#sender_email").val("");
        $("#sender_city").val("");
        $("#sender_province").val("");
        $("#sender_postal").val("");
        $("#sender_postal_code").val("");
        $("#sender_address").val("");
        $("#sender_identity").val("");
        $("#sender_country").val("");
        $("#sender_country_code").val("");
        $("#sender_country_zone").val("");
    },

    // ==================================================
    // BUKA MODAL
    // ==================================================
    bindModal() {
        $(document)
            .off(
                "click.shipmentSender",
                "#btnAddCustomer"
            )
            .on(
                "click.shipmentSender",
                "#btnAddCustomer",
                function () {
                    const modalElement =
                        document.getElementById(
                            "senderModal"
                        );

                    if (!modalElement) {
                        console.error(
                            "senderModal tidak ditemukan"
                        );
                        return;
                    }

                    bootstrap.Modal
                        .getOrCreateInstance(
                            modalElement
                        )
                        .show();
                }
            );
    },

    // ==================================================
    // SAVE SENDER
    // ==================================================
    bindSave() {
        $(document)
            .off(
                "click.shipmentSender",
                "#btnSaveSender"
            )
            .on(
                "click.shipmentSender",
                "#btnSaveSender",
                function (event) {
                    event.preventDefault();

                    const $button = $(this);

                    // Mencegah double click
                    if ($button.prop("disabled")) {
                        return;
                    }

                    const senderData = {
                        address_type: "SENDER",

                        customer_id: String(
                            $("#customer_id").val() || ""
                        ).trim(),

                        country_id: String(
                            $("#new_sender_country_id").val() || ""
                        ).trim(),

                        contact_name:
                            (
                                $("#new_sender_name").val()
                                || ""
                            ).trim(),

                        company_name:
                            (
                                $("#new_sender_company").val()
                                || ""
                            ).trim(),

                        phone:
                            (
                                $("#new_sender_phone").val()
                                || ""
                            ).trim(),

                        identity_number:
                            (
                                $("#new_sender_identity").val()
                                || ""
                            ).trim(),

                        province:
                            (
                                $("#new_sender_province").val()
                                || ""
                            ).trim(),

                        state:
                            (
                                $("#new_sender_province").val()
                                || ""
                            ).trim(),

                        city:
                            (
                                $("#new_sender_city").val()
                                || ""
                            ).trim(),

                        postal_code:
                            (
                                $("#new_sender_postal_code").val()
                                || ""
                            ).trim(),

                        email:
                            (
                                $("#new_sender_email").val()
                                || ""
                            ).trim(),

                        address:
                            (
                                $("#new_sender_address").val()
                                || ""
                            ).trim()
                    };

                    if (!senderData.customer_id) {
                        alert(
                            "Pilih Customer terlebih dahulu."
                        );
                        return;
                    }

                    if (!senderData.contact_name) {
                        alert(
                            "Customer Name is required!"
                        );
                        return;
                    }

                    if (!senderData.phone) {
                        alert(
                            "Customer Phone is required!"
                        );
                        return;
                    }

                    if (!senderData.country_id) {
                        alert(
                            "Country is required!"
                        );
                        return;
                    }

                    $button
                        .prop("disabled", true)
                        .text("Saving...");

                    $.ajax({
                        url:
                            BASE_URL +
                            "/customer/address/save",

                        type: "POST",
                        data: senderData,
                        dataType: "json",

                        success: function (response) {
                            console.log(
                                "SENDER DATA DIKIRIM:",
                                senderData
                            );

                            console.log(
                                "SENDER RESPONSE:",
                                response
                            );

                            if (
                                response.status !==
                                "success"
                            ) {
                                alert(
                                    "Gagal: " +
                                    (
                                        response.message ||
                                        "Unknown error"
                                    )
                                );
                                return;
                            }

                            const details =
                                response.address_details
                                || {};

                            const responseData =
                                response.data
                                || {};

                            const senderId =
                                responseData.id
                                || details.id;

                            if (!senderId) {
                                alert(
                                    "ID sender baru tidak ditemukan."
                                );
                                return;
                            }

                            /*
                             * Data yang baru diketik di modal
                             * menjadi sumber utama.
                             *
                             * Respons server hanya menjadi
                             * fallback.
                             */
                            const customerId =
                                details.customer_id || "";

                            const defaultCountryName =
                                String(
                                    $("#new_sender_country_name").val() || ""
                                ).trim()
                                ||
                                "Indonesia";

                            const defaultCountryCode =
                                String(
                                    $("#new_sender_country_code").val() || ""
                                ).trim()
                                ||
                                "ID";

                            const defaultCountryZone =
                                String(
                                    $("#new_sender_country_zone").val() || ""
                                ).trim();

                            const countryName =
                                details.country_name
                                ||
                                defaultCountryName;

                            const countryCode =
                                details.country_code
                                ||
                                defaultCountryCode;

                            const countryZone =
                                details.express_zone_code
                                ||
                                defaultCountryZone;

                            const normalizedData = {
                                customerId: customerId,
                                addressId: senderId,

                                company:
                                    senderData.company_name
                                    ||
                                    details.company_name
                                    ||
                                    "",

                                phone:
                                    senderData.phone
                                    ||
                                    details.phone
                                    ||
                                    "",

                                email:
                                    senderData.email
                                    ||
                                    details.email
                                    ||
                                    "",

                                city:
                                    senderData.city
                                    ||
                                    details.city
                                    ||
                                    "",

                                state:
                                    senderData.province
                                    ||
                                    details.state
                                    ||
                                    details.province
                                    ||
                                    "",

                                postalCode:
                                    senderData.postal_code
                                    ||
                                    details.postal_code
                                    ||
                                    "",

                                address:
                                    senderData.address
                                    ||
                                    details.address
                                    ||
                                    "",

                                identity:
                                    senderData.identity_number
                                    ||
                                    details.identity_number
                                    ||
                                    "",

                                country: countryName,
                                countryName: countryName,
                                countryCode: countryCode,
                                countryZone: countryZone
                            };

                            const contactName =
                                senderData.contact_name
                                ||
                                details.contact_name
                                ||
                                "";

                            const companyName =
                                senderData.company_name
                                ||
                                details.company_name
                                ||
                                "";

                            let optionLabel = contactName;

                            if (
                                companyName !== ""
                                && companyName.toLowerCase()
                                    !== contactName.toLowerCase()
                            ) {
                                optionLabel +=
                                    " — " + companyName;
                            }

                            const option =
                                new Option(
                                    optionLabel,

                                    String(customerId),

                                    true,
                                    true
                                );

                            $(option).attr({

                                "data-customer-id":
                                    customerId,

                                "data-address-id":
                                    senderId,

                                "data-contact-name":
                                    contactName,

                                "data-country-id":
                                    details.country_id
                                    || senderData.country_id
                                    || "",

                                "data-country-name":
                                    normalizedData.countryName,

                                "data-country-code":
                                    normalizedData.countryCode,

                                "data-country-zone":
                                    normalizedData.countryZone,

                                "data-company":
                                    normalizedData.company,

                                "data-phone":
                                    normalizedData.phone,

                                "data-email":
                                    normalizedData.email,

                                "data-city":
                                    normalizedData.city,

                                "data-state":
                                    normalizedData.state,

                                "data-postal-code":
                                    normalizedData.postalCode,

                                "data-address":
                                    normalizedData.address,

                                "data-identity":
                                    normalizedData.identity,

                                "data-country":
                                    normalizedData.country
                            });

                            /*
                             * Tambahkan option baru lalu pilih.
                             */
                            $("#customer_id")
                                .append(option)
                                .trigger("change");

                            /*
                             * Paksa isi dari data yang baru
                             * diketik agar tidak ditimpa oleh
                             * event lain.
                             */
                            setTimeout(function () {
                                window.ShipmentSender.fill(
                                    normalizedData
                                );

                                console.log(
                                    "SENDER FORM TERISI:",
                                    normalizedData
                                );
                            }, 200);

                            window.ShipmentSender
                                .closeModal();

                            window.ShipmentSender
                                .clearModal();

                            alert(
                                "Sender saved successfully!"
                            );
                        },

                        error: function (xhr) {
                            console.error(
                                "SAVE SENDER ERROR:",
                                xhr.responseText
                            );

                            let message =
                                "Error Save Sender: " +
                                xhr.status;

                            try {
                                const res =
                                    xhr.responseJSON ||
                                    JSON.parse(
                                        xhr.responseText || "{}"
                                    );

                                if (res.message) {
                                    message = res.message;
                                }

                                if (
                                    res.errors &&
                                    typeof res.errors ===
                                        "object"
                                ) {
                                    const errorList = Object
                                        .values(res.errors)
                                        .join("\n");

                                    if (errorList) {
                                        message +=
                                            "\n\n" + errorList;
                                    }
                                }
                            } catch (parseError) {
                                if (xhr.responseText) {
                                    message +=
                                        "\n\n" +
                                        xhr.responseText;
                                }
                            }

                            alert(
                                "Gagal menyimpan sender:\n\n" +
                                message
                            );
                        },

                        complete: function () {
                            $button
                                .prop("disabled", false)
                                .text("💾 Save Sender");
                        }
                    });
                }
            );
    },

    // ==================================================
    // TUTUP MODAL DENGAN AMAN
    // ==================================================
    closeModal() {
        const modalElement =
            document.getElementById(
                "senderModal"
            );

        if (!modalElement) {
            return;
        }

        // Hilangkan warning aria-hidden
        const activeElement =
            document.activeElement;

        if (
            activeElement &&
            modalElement.contains(
                activeElement
            )
        ) {
            activeElement.blur();
        }

        const modal =
            bootstrap.Modal
                .getOrCreateInstance(
                    modalElement
                );

        modalElement.addEventListener(
            "hidden.bs.modal",
            function () {
                document
                    .getElementById(
                        "btnAddCustomer"
                    )
                    ?.focus();
            },
            { once: true }
        );

        modal.hide();
    },

    // ==================================================
    // BERSIHKAN MODAL
    // ==================================================
    clearModal() {
        $(
            "#senderModal input:not([type=hidden]), " +
            "#senderModal textarea, " +
            "#senderModal select"
        ).val("");
    }
};

window.initializeSender = function () {
    window.ShipmentSender.init();
};