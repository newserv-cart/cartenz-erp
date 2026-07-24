/*
====================================================
CARTENZ ERP
Shipment Receiver Module
Sprint 49.3
====================================================
*/

window.ShipmentReceiver = {

    initialized: false,

    init() {
        if (this.initialized) return;
        this.initialized = true;

        this.bindReceiver();
        this.bindModal();
        this.bindSave();

        console.log("✅ Receiver Module Loaded");
    },

    // ==================================================
    // Dropdown Receiver
    // ==================================================
    bindReceiver() {
        $(document)
            .off("change.shipmentReceiver", "#receiver_id")
            .on("change.shipmentReceiver", "#receiver_id", function () {
                const option = this.options[this.selectedIndex];

                if (!option || !option.value) {
                    window.ShipmentReceiver.clear();
                    return;
                }

                window.ShipmentReceiver.fill(option.dataset);
            });
    },

    // ==================================================
    // Isi Form
    // ==================================================
    fill(data) {
        $("#receiver_company").val(data.company || "");
        $("#receiver_phone").val(data.phone || "");
        $("#receiver_email").val(data.email || "");
        $("#receiver_city").val(data.city || "");
        $("#receiver_province").val(data.state || data.province || "");
        $("#receiver_postal_code").val(data.postalCode || data.postal_code || "");
        $("#receiver_address").val(data.address || "");
        $("#receiver_identity").val(data.identity || "");
        $("#receiver_country").val(data.country || "");

        // Set country_id dan trigger event untuk memulai perhitungan tarif
        $("#country_id")
            .val(data.countryId || data.country_id || "")
            .trigger("change");
    },

    // ==================================================
    // Kosongkan Form
    // ==================================================
    clear() {
        $("#receiver_company").val("");
        $("#receiver_phone").val("");
        $("#receiver_email").val("");
        $("#receiver_city").val("");
        $("#receiver_province").val("");
        $("#receiver_postal_code").val("");
        $("#receiver_address").val("");
        $("#receiver_identity").val("");
        $("#receiver_country").val("");
        $("#country_id").val("").trigger("change");
    },

    // ==================================================
    // Buka Modal
    // ==================================================
    bindModal() {
        $(document)
            .off("click.shipmentReceiver", "#btnAddReceiver")
            .on("click.shipmentReceiver", "#btnAddReceiver", function () {
                const modalEl = document.getElementById("receiverModal");
                if (!modalEl) {
                    console.error("❌ receiverModal tidak ditemukan");
                    return;
                }
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
    },

    // ==================================================
    // Simpan Receiver Baru
    // ==================================================
    bindSave() {
        $(document)
            .off("click.shipmentReceiver", "#btnSaveReceiver")
            .on("click.shipmentReceiver", "#btnSaveReceiver", function (e) {
                e.preventDefault();
                const $btn = $(this);

                if ($btn.prop("disabled")) return;

                // Ambil nama negara dari dropdown untuk fallback
                const selectedCountryName = String(
                    $("#new_receiver_country option:selected").text() || ""
                ).trim();

                // Pola aman: anti null/undefined error saat pakai .trim()
                const customerId = String(
                    $("#customer_id").val() || ""
                ).trim();

                if (!customerId) {
                    alert("Pilih Customer/Pengirim terlebih dahulu.");
                    return;
                }

                const receiverData = {
                    address_type: "RECEIVER",
                    customer_id: customerId,
                    country_id: String($("#new_receiver_country").val() || "").trim(),
                    contact_name: String($("#new_receiver_name").val() || "").trim(),
                    company_name: String($("#new_receiver_company").val() || "").trim(),
                    phone: String($("#new_receiver_phone").val() || "").trim(),
                    identity_number: String($("#new_receiver_identity").val() || "").trim(),
                    province: String($("#new_receiver_province").val() || "").trim(),
                    state: String($("#new_receiver_province").val() || "").trim(),
                    city: String($("#new_receiver_city").val() || "").trim(),
                    postal_code: String($("#new_receiver_postal").val() || "").trim(),
                    email: String($("#new_receiver_email").val() || "").trim(),
                    address: String($("#new_receiver_address").val() || "").trim()
                };

                // Validasi wajib
                if (!receiverData.contact_name) return alert("❌ Receiver Name is required!");
                if (!receiverData.phone) return alert("❌ Receiver Phone is required!");
                if (!receiverData.country_id) return alert("❌ Destination Country is required!");

                $btn.prop("disabled", true).text("Saving...");

                $.ajax({
                    url: BASE_URL + "/customer/address/save",
                    type: "POST",
                    data: receiverData,
                    dataType: "json",

                    success: function (res) {
                        console.log("📤 Receiver Data:", receiverData);
                        console.log("📥 Response:", res);

                        if (res.status !== "success") {
                            alert("❌ Gagal: " + (res.message || "Unknown error"));
                            return;
                        }

                        const details = res.address_details || res.data || {};
                        const resData = res.data || {};
                        const receiverId = resData.id || details.id;

                        if (!receiverId) {
                            alert("❌ ID Receiver tidak ditemukan");
                            return;
                        }

                        // Normalisasi data dengan fallback nama negara
                        const normalized = {
                            company: receiverData.company_name || details.company_name || "",
                            phone: receiverData.phone || details.phone || "",
                            email: receiverData.email || details.email || "",
                            city: receiverData.city || details.city || "",
                            state: receiverData.province || details.state || details.province || "",
                            postalCode: receiverData.postal_code || details.postal_code || "",
                            address: receiverData.address || details.address || "",
                            identity: receiverData.identity_number || details.identity_number || "",
                            country: details.country_name || selectedCountryName || "",
                            countryId: receiverData.country_id || details.country_id || ""
                        };

                        const option = new Option(
                            resData.text || receiverData.contact_name,
                            receiverId,
                            true,
                            true
                        );

                        $(option).attr({
                            "data-country-id": normalized.countryId,
                            "data-company": normalized.company,
                            "data-phone": normalized.phone,
                            "data-email": normalized.email,
                            "data-city": normalized.city,
                            "data-state": normalized.state,
                            "data-postal-code": normalized.postalCode,
                            "data-address": normalized.address,
                            "data-identity": normalized.identity,
                            "data-country": normalized.country
                        });

                        $("#receiver_id").append(option).val(receiverId).trigger("change");

                        setTimeout(() => {
                            window.ShipmentReceiver.fill(normalized);
                            console.log("✅ RECEIVER FORM TERISI:", normalized);
                        }, 200);

                        window.ShipmentReceiver.closeModal();
                        window.ShipmentReceiver.clearModal();
                        alert("✅ Receiver saved successfully!");
                    },

                    error: function (xhr) {
                        console.error("❌ Save Receiver Error:", xhr.responseText);

                        let message = "⚠️ Server error: " + xhr.status;

                        try {
                            const res = xhr.responseJSON ||
                                JSON.parse(xhr.responseText || "{}");

                            if (res.message) {
                                message = res.message;
                            }

                            if (
                                res.errors &&
                                typeof res.errors === "object"
                            ) {
                                const errorList = Object
                                    .values(res.errors)
                                    .join("\n");

                                if (errorList) {
                                    message += "\n\n" + errorList;
                                }
                            }
                        } catch (parseError) {
                            if (xhr.responseText) {
                                message += "\n\n" + xhr.responseText;
                            }
                        }

                        alert(
                            "Gagal menyimpan receiver:\n\n" +
                            message
                        );
                    },

                    complete: function () {
                        $btn.prop("disabled", false).text("💾 Save Receiver");
                    }
                });
            });
    },

    // ==================================================
    // Tutup Modal (tanpa warning aria-hidden)
    // ==================================================
    closeModal() {
        const modalEl = document.getElementById("receiverModal");
        if (!modalEl) return;

        const activeElement = document.activeElement;
        if (activeElement && modalEl.contains(activeElement)) {
            activeElement.blur();
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        modalEl.addEventListener(
            "hidden.bs.modal",
            function () {
                document.getElementById("btnAddReceiver")?.focus();
            },
            { once: true }
        );

        modal.hide();
    },

    // ==================================================
    // Bersihkan Modal
    // ==================================================
    clearModal() {
        $("#receiverModal input, #receiverModal textarea, #receiverModal select").val("");
    }
};

window.initializeReceiver = function () {
    window.ShipmentReceiver.init();
};/*
====================================================
CARTENZ ERP
Shipment Receiver Module
Sprint 49.3
====================================================
*/

window.ShipmentReceiver = {

    initialized: false,

    init() {
        if (this.initialized) return;
        this.initialized = true;

        this.bindReceiver();
        this.bindModal();
        this.bindSave();

        console.log("✅ Receiver Module Loaded");
    },

    // ==================================================
    // Dropdown Receiver
    // ==================================================
    bindReceiver() {
        $(document)
            .off("change.shipmentReceiver", "#receiver_id")
            .on("change.shipmentReceiver", "#receiver_id", function () {
                const option = this.options[this.selectedIndex];

                if (!option || !option.value) {
                    window.ShipmentReceiver.clear();
                    return;
                }

                window.ShipmentReceiver.fill(option.dataset);
            });
    },

    // ==================================================
    // Isi Form
    // ==================================================
    fill(data) {
        $("#receiver_company").val(data.company || "");
        $("#receiver_phone").val(data.phone || "");
        $("#receiver_email").val(data.email || "");
        $("#receiver_city").val(data.city || "");
        $("#receiver_province").val(data.state || data.province || "");
        $("#receiver_postal_code").val(data.postalCode || data.postal_code || "");
        $("#receiver_address").val(data.address || "");
        $("#receiver_identity").val(data.identity || "");
        $("#receiver_country").val(data.country || "");

        // Set country_id dan trigger event untuk memulai perhitungan tarif
        $("#country_id")
            .val(data.countryId || data.country_id || "")
            .trigger("change");
    },

    // ==================================================
    // Kosongkan Form
    // ==================================================
    clear() {
        $("#receiver_company").val("");
        $("#receiver_phone").val("");
        $("#receiver_email").val("");
        $("#receiver_city").val("");
        $("#receiver_province").val("");
        $("#receiver_postal_code").val("");
        $("#receiver_address").val("");
        $("#receiver_identity").val("");
        $("#receiver_country").val("");
        $("#country_id").val("").trigger("change");
    },

    // ==================================================
    // Buka Modal
    // ==================================================
    bindModal() {
        $(document)
            .off("click.shipmentReceiver", "#btnAddReceiver")
            .on("click.shipmentReceiver", "#btnAddReceiver", function () {
                const modalEl = document.getElementById("receiverModal");
                if (!modalEl) {
                    console.error("❌ receiverModal tidak ditemukan");
                    return;
                }
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
    },

    // ==================================================
    // Simpan Receiver Baru
    // ==================================================
    bindSave() {
        $(document)
            .off("click.shipmentReceiver", "#btnSaveReceiver")
            .on("click.shipmentReceiver", "#btnSaveReceiver", function (e) {
                e.preventDefault();
                const $btn = $(this);

                if ($btn.prop("disabled")) return;

                // Ambil nama negara dari dropdown untuk fallback
                const selectedCountryName = String(
                    $("#new_receiver_country option:selected").text() || ""
                ).trim();

                // Pola aman: anti null/undefined error saat pakai .trim()
                const customerId = String(
                    $("#customer_id").val() || ""
                ).trim();

                if (!customerId) {
                    alert("Pilih Customer/Pengirim terlebih dahulu.");
                    return;
                }

                const receiverData = {
                    address_type: "RECEIVER",
                    customer_id: customerId,
                    country_id: String($("#new_receiver_country").val() || "").trim(),
                    contact_name: String($("#new_receiver_name").val() || "").trim(),
                    company_name: String($("#new_receiver_company").val() || "").trim(),
                    phone: String($("#new_receiver_phone").val() || "").trim(),
                    identity_number: String($("#new_receiver_identity").val() || "").trim(),
                    province: String($("#new_receiver_province").val() || "").trim(),
                    state: String($("#new_receiver_province").val() || "").trim(),
                    city: String($("#new_receiver_city").val() || "").trim(),
                    postal_code: String($("#new_receiver_postal").val() || "").trim(),
                    email: String($("#new_receiver_email").val() || "").trim(),
                    address: String($("#new_receiver_address").val() || "").trim()
                };

                // Validasi wajib
                if (!receiverData.contact_name) return alert("❌ Receiver Name is required!");
                if (!receiverData.phone) return alert("❌ Receiver Phone is required!");
                if (!receiverData.country_id) return alert("❌ Destination Country is required!");

                $btn.prop("disabled", true).text("Saving...");

                $.ajax({
                    url: BASE_URL + "/customer/address/save",
                    type: "POST",
                    data: receiverData,
                    dataType: "json",

                    success: function (res) {
                        console.log("📤 Receiver Data:", receiverData);
                        console.log("📥 Response:", res);

                        if (res.status !== "success") {
                            alert("❌ Gagal: " + (res.message || "Unknown error"));
                            return;
                        }

                        const details = res.address_details || res.data || {};
                        const resData = res.data || {};
                        const receiverId = resData.id || details.id;

                        if (!receiverId) {
                            alert("❌ ID Receiver tidak ditemukan");
                            return;
                        }

                        // Normalisasi data dengan fallback nama negara
                        const normalized = {
                            company: receiverData.company_name || details.company_name || "",
                            phone: receiverData.phone || details.phone || "",
                            email: receiverData.email || details.email || "",
                            city: receiverData.city || details.city || "",
                            state: receiverData.province || details.state || details.province || "",
                            postalCode: receiverData.postal_code || details.postal_code || "",
                            address: receiverData.address || details.address || "",
                            identity: receiverData.identity_number || details.identity_number || "",
                            country: details.country_name || selectedCountryName || "",
                            countryId: receiverData.country_id || details.country_id || ""
                        };

                        const option = new Option(
                            resData.text || receiverData.contact_name,
                            receiverId,
                            true,
                            true
                        );

                        $(option).attr({
                            "data-country-id": normalized.countryId,
                            "data-company": normalized.company,
                            "data-phone": normalized.phone,
                            "data-email": normalized.email,
                            "data-city": normalized.city,
                            "data-state": normalized.state,
                            "data-postal-code": normalized.postalCode,
                            "data-address": normalized.address,
                            "data-identity": normalized.identity,
                            "data-country": normalized.country
                        });

                        $("#receiver_id").append(option).val(receiverId).trigger("change");

                        setTimeout(() => {
                            window.ShipmentReceiver.fill(normalized);
                            console.log("✅ RECEIVER FORM TERISI:", normalized);
                        }, 200);

                        window.ShipmentReceiver.closeModal();
                        window.ShipmentReceiver.clearModal();
                        alert("✅ Receiver saved successfully!");
                    },

                    error: function (xhr) {
                        console.error("❌ Save Receiver Error:", xhr.responseText);

                        let message = "⚠️ Server error: " + xhr.status;

                        try {
                            const res = xhr.responseJSON ||
                                JSON.parse(xhr.responseText || "{}");

                            if (res.message) {
                                message = res.message;
                            }

                            if (
                                res.errors &&
                                typeof res.errors === "object"
                            ) {
                                const errorList = Object
                                    .values(res.errors)
                                    .join("\n");

                                if (errorList) {
                                    message += "\n\n" + errorList;
                                }
                            }
                        } catch (parseError) {
                            if (xhr.responseText) {
                                message += "\n\n" + xhr.responseText;
                            }
                        }

                        alert(
                            "Gagal menyimpan receiver:\n\n" +
                            message
                        );
                    },

                    complete: function () {
                        $btn.prop("disabled", false).text("💾 Save Receiver");
                    }
                });
            });
    },

    // ==================================================
    // Tutup Modal (tanpa warning aria-hidden)
    // ==================================================
    closeModal() {
        const modalEl = document.getElementById("receiverModal");
        if (!modalEl) return;

        const activeElement = document.activeElement;
        if (activeElement && modalEl.contains(activeElement)) {
            activeElement.blur();
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        modalEl.addEventListener(
            "hidden.bs.modal",
            function () {
                document.getElementById("btnAddReceiver")?.focus();
            },
            { once: true }
        );

        modal.hide();
    },

    // ==================================================
    // Bersihkan Modal
    // ==================================================
    clearModal() {
        $("#receiverModal input, #receiverModal textarea, #receiverModal select").val("");
    }
};

window.initializeReceiver = function () {
    window.ShipmentReceiver.init();
};