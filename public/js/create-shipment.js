/*
=========================================================
CARTENZ ERP
Module : Shipment
File   : create-shipment.js
Sprint : 15 - 57
Author : Cartenz ERP Team
=========================================================
*/

// ========================================
// CARTENZ ERP
// CREATE SHIPMENT - SENDER ADDRESS HANDLING
// ========================================
document.addEventListener("DOMContentLoaded", function () {
    // ======================================
    // ADD NEW SENDER BUTTON
    // ======================================
    const btnAddCustomer = document.getElementById("btnAddCustomer");
    if (btnAddCustomer) {
        btnAddCustomer.addEventListener("click", function () {
            bootstrap.Modal
                .getOrCreateInstance(document.getElementById("senderModal"))
                .show();
        });
    }

    // ===============================
    // SENDER DROPDOWN CHANGE
    // ===============================
    const senderSelect = document.getElementById("customer_id");
    if (senderSelect) {
        senderSelect.addEventListener("change", function () {

    const selected = this.options[this.selectedIndex];

    console.log("SELECTED OPTION");
    console.log(selected);

    console.log("DATASET");
    console.log(selected.dataset);

    if (this.value && Object.keys(selected.dataset).length > 0) {


        
                document.getElementById("sender_company").value      = selected.dataset.company || "";
                document.getElementById("sender_phone").value         = selected.dataset.phone || "";
                document.getElementById("sender_identity").value      = selected.dataset.identity || "";
                document.getElementById("sender_email").value        = selected.dataset.email || "";
                document.getElementById("sender_province").value      = selected.dataset.state || "";
                document.getElementById("sender_city").value          = selected.dataset.city || "";
                document.getElementById("sender_postal_code").value   = selected.dataset.postal_code || "";
                document.getElementById("sender_address").value      = selected.dataset.address || "";
                document.getElementById("sender_country").value       = selected.dataset.country || "";
            } else {
                document.getElementById("sender_company").value      = "";
                document.getElementById("sender_phone").value         = "";
                document.getElementById("sender_identity").value      = "";
                document.getElementById("sender_email").value        = "";
                document.getElementById("sender_province").value      = "";
                document.getElementById("sender_city").value          = "";
                document.getElementById("sender_postal_code").value   = "";
                document.getElementById("sender_address").value      = "";
                document.getElementById("sender_country").value       = "";
            }
        });
    }

    // ===============================
    // SAVE NEW SENDER ADDRESS
    // ===============================
    const btnSaveSender = document.getElementById("btnSaveSender");
    if (btnSaveSender) {
        btnSaveSender.addEventListener("click", function () {
            const countryEl = document.getElementById("new_customer_country");
            const countryId = countryEl ? countryEl.value : "";
            const countryName = countryEl && countryEl.selectedIndex >= 0 ? countryEl.options[countryEl.selectedIndex].text : "";

            const formData = new URLSearchParams({
                address_type    : "SENDER",
                contact_name    : document.getElementById("new_customer_name").value.trim(),
                company_name    : document.getElementById("new_customer_company").value.trim(),
                phone           : document.getElementById("new_customer_phone").value.trim(),
                identity_number : document.getElementById("new_customer_identity").value.trim(),
                province        : document.getElementById("new_customer_province").value.trim(),
                city            : document.getElementById("new_customer_city").value.trim(),
                postal_code     : document.getElementById("new_customer_postal_code").value.trim(),
                address         : document.getElementById("new_customer_address").value.trim(),
                email           : document.getElementById("new_customer_email").value.trim(),
                country_id      : countryId,
                csrf_token      : document.querySelector('input[name="csrf_token"]')?.value || ""
            });

            fetch("/customer/address/save", {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                },
                body: formData
            })
            .then(response => response.json())
            .then(res => {
    if (res.status === "success") {
        alert("✅ Sender address saved successfully!");
        
        const s = res.address_details; // Ini array $address dari PHP
        const newOption = document.createElement("option");
        
        newOption.value = res.data.id; // Mengambil ID dari res.data.id atau s.id
        newOption.text  = res.data.text; // Menggunakan textDisplay yang sudah dibuat di PHP

        // Mapping dataset disesuaikan dengan key dari Controller PHP
        newOption.dataset.company   = s.company_name || "";
        newOption.dataset.phone     = s.phone || "";
        newOption.dataset.email     = s.email || "";   
        newOption.dataset.state     = s.state || "";        // 🛠️ FIXED: dari PHP namanya 'state'
        newOption.dataset.city      = s.city || "";
        newOption.dataset.postal    = s.postal_code || "";  // 🛠️ FIXED: pastikan konsisten camelCase/lowercase di dropdown change
        newOption.dataset.identity  = s.identity_number || "";
        newOption.dataset.country   = s.country_name || "";
        newOption.dataset.countryId = s.country_id || "";

        // Tambah ke dropdown dan select otomatis
        senderSelect.appendChild(newOption);
        senderSelect.value = res.data.id;
        
        // Pemicu biar input form langsung auto-fill setelah modal close
        senderSelect.dispatchEvent(new Event("change"));

        bootstrap.Modal.getOrCreateInstance(document.getElementById("senderModal")).hide();
    } else {
        alert("❌ Error: " + (res.message || "Failed to save address"));
    }
})
            .catch(err => {
                console.error("Save error:", err);
                alert("⚠️ Server error. Please try again.");
            });
        });
    }
});

// ========================================
// CARTENZ ERP
// CREATE SHIPMENT - RECEIVER ADDRESS HANDLING
// ========================================
document.addEventListener("DOMContentLoaded", function () {
    // ======================================
    // ADD NEW RECEIVER BUTTON
    // ======================================
    const btnAddReceiver = document.getElementById("btnAddReceiver");
    if (btnAddReceiver) {
        btnAddReceiver.addEventListener("click", function () {
            bootstrap.Modal
                .getOrCreateInstance(document.getElementById("receiverModal"))
                .show();
        });
    }

    // ===============================
    // RECEIVER DROPDOWN CHANGE
    // ===============================
    const receiverSelect = document.getElementById("receiver_id");
    if (receiverSelect) {
        receiverSelect.addEventListener("change", function () {
            const selected = this.options[this.selectedIndex];
            if (this.value && Object.keys(selected.dataset).length > 0) {
                document.getElementById("receiver_company").value      = selected.dataset.company || "";
                document.getElementById("receiver_phone").value         = selected.dataset.phone || "";
                document.getElementById("receiver_identity").value      = selected.dataset.identity || "";
                document.getElementById("receiver_email").value        = selected.dataset.email || "";
                document.getElementById("receiver_province").value      = selected.dataset.state || "";
                document.getElementById("receiver_city").value          = selected.dataset.city || "";
                document.getElementById("receiver_postal_code").value   = selected.dataset.postal_code || "";
                document.getElementById("receiver_address").value      = selected.dataset.address || "";
                document.getElementById("receiver_country").value       = selected.dataset.country || "";
                document.getElementById("country_id").value             = selected.dataset.countryId || "";

                if (typeof loadShipmentRate === "function") {
                    loadShipmentRate();
                }
            } else {
                document.getElementById("receiver_company").value      = "";
                document.getElementById("receiver_phone").value         = "";
                document.getElementById("receiver_identity").value      = "";
                document.getElementById("receiver_email").value        = "";
                document.getElementById("receiver_province").value      = "";
                document.getElementById("receiver_city").value          = "";
                document.getElementById("receiver_postal_code").value   = "";
                document.getElementById("receiver_address").value      = "";
                document.getElementById("receiver_country").value       = "";
            }
        });
    }

    // ===============================
    // SAVE NEW RECEIVER ADDRESS
    // ===============================
    const btnSaveReceiver = document.getElementById("btnSaveReceiver");
    if (btnSaveReceiver) {
        btnSaveReceiver.addEventListener("click", function () {
            const countryEl = document.getElementById("new_receiver_country");
            const countryId = countryEl ? countryEl.value : "";
            const countryName = countryEl && countryEl.selectedIndex >= 0 ? countryEl.options[countryEl.selectedIndex].text : "";

const formData = new URLSearchParams({

    customer_id:
        document.getElementById("customer_id").value,

    receiver_name:
        document.getElementById("new_receiver_name").value,

    company_name:
        document.getElementById("new_receiver_company").value,

    phone:
        document.getElementById("new_receiver_phone").value,

    email:
        document.getElementById("new_receiver_email").value,

    city:
        document.getElementById("new_receiver_city").value,

    state_province:
        document.getElementById("new_receiver_province").value,

    postal_code:
        document.getElementById("new_receiver_postal").value,

    address:
        document.getElementById("new_receiver_address").value,

    identity_number:
        document.getElementById("new_receiver_identity").value,

    country_id:
        document.getElementById("new_receiver_country").value

});
             

fetch("/receivers/store_ajax", {
    method: "POST",
    headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json"
    },
    body: formData
})
            .then(response => response.json())
            .then(res => {
                if (res.status === "success") {
                    const s = res.address_details;
                    const newOption = document.createElement("option");
                    newOption.value = s.id;
                    newOption.text = s.contact_name + (s.city ? " - " + s.city : "");

                    newOption.dataset.name     = s.contact_name || "";
                    newOption.dataset.company  = s.company_name || "";
                    newOption.dataset.phone    = s.phone || "";
                    newOption.dataset.email    = s.email || "";
                    newOption.dataset.state    = s.state || "";
                    newOption.dataset.city     = s.city || "";
                    newOption.dataset.postal   = s.postal_code || "";
                    newOption.dataset.address  = s.address || "";
                    newOption.dataset.identity = s.identity_number || "";
                    newOption.dataset.country = s.country_name;

                    receiverSelect.appendChild(newOption);
                    receiverSelect.value = s.id;
                    receiverSelect.dispatchEvent(new Event("change"));

                    bootstrap.Modal.getOrCreateInstance(document.getElementById("receiverModal")).hide();
                } else {
                    alert("❌ Error: " + (res.message || "Failed to save address"));
                }
            })
            .catch(err => {
                console.error("Save error:", err);
                alert("⚠️ Server error. Please try again.");
            });
        });
    }
});

// ======================================================
// SPRINT 20
// GRAND TOTAL ENGINE
// ======================================================
function calculateGrandTotal() {
    const shipment   = parseFloat(document.getElementById("selling_amount")?.value) || 0;
    const insurance  = parseFloat(document.getElementById("insurance_amount")?.value) || 0;
    const packing    = parseFloat(document.getElementById("packing_charge")?.value) || 0;
    const additional = parseFloat(document.getElementById("additional_charge")?.value) || 0;
    const discount   = parseFloat(document.getElementById("discount")?.value) || 0;

    let other = insurance + packing + additional - discount;
    if (other < 0) other = 0;

    let grand = shipment + other;
    if (grand < 0) grand = 0;

    const otherTextEl = document.getElementById("other_charge_text");
    if (otherTextEl) otherTextEl.innerHTML = "Rp " + other.toLocaleString("id-ID");

    const grandTextEl = document.getElementById("grand_total_text");
    if (grandTextEl) grandTextEl.innerHTML = "Rp " + grand.toLocaleString("id-ID");

    const grandInputEl = document.getElementById("grand_total");
    if (grandInputEl) grandInputEl.value = grand;
}

document.addEventListener("DOMContentLoaded", function () {
    const grandTotalFields = [
        "insurance_customer",
        "packing_customer",
        "additional_customer",
        "discount_amount"
    ];

    grandTotalFields.forEach(function (id) {
        const el = document.getElementById(id);
        if (el) el.addEventListener("input", calculateGrandTotal);
    });
});

/*
=========================================================
SPRINT 57 - LIVE PRICING ENGINE
Debounce, Anti-Spam, Loading, Error Handling
=========================================================
*/
let pricingTimer    = null;
let pricingXHR      = null;
let lastPricingHash = '';
const BASEURL       = window.BASEURL || window.baseUrl || '/';

// Format angka ke Rupiah
function formatRupiah(angka) {
    angka = Number(angka || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}

// Tampilkan peringatan
function updateWarnings(warnings) {
    const $container = $('#pricingWarnings');
    if (!$container.length) return;

    $container.empty();
    if (!warnings || !Array.isArray(warnings) || warnings.length === 0) return;

    let html = '<div class="alert alert-warning mt-2 p-2"><ul class="mb-0 ps-3 small">';
    warnings.forEach(msg => html += `<li>${msg}</li>`);
    html += '</ul></div>';

    $container.html(html);
}

// Pemicu perhitungan dengan jeda anti-spam
function triggerPricing() {
    clearTimeout(pricingTimer);
    pricingTimer = setTimeout(function () {
        calculatePricing();
    }, 300);
}

// ✅ FUNGSI UTAMA - LIVE PRICING
function calculatePricing() {
    // Cek perubahan data agar tidak request berulang sama persis
    const currentHash = $('#shipmentForm').serialize();
    if (currentHash === lastPricingHash) return;
    lastPricingHash = currentHash;

    // Tampilkan loading
    const spinner = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>';
    $('#freight_charge_text, #remote_charge_text, #fuel_charge_text, #gogreen_charge_text, #insurance_charge_text, #packing_charge_text, #additional_charge_text, #discount_charge_text, #grand_total_text, #vendor_total_text, #profit_text, #margin_text').html(spinner);

    // Batalkan request sebelumnya jika masih berjalan
    if (pricingXHR) {
        pricingXHR.abort();
        pricingXHR = null;
    }

    // Kirim request ke server
    pricingXHR = $.ajax({
        url: BASEURL + 'api/shipment/calculate',
        type: 'POST',
        data: $('#shipmentForm').serialize(),
        dataType: 'json',
        success: function(res) {
            updateWarnings(res.warnings || []);

            // Update rincian biaya sesuai tampilan
            $('#freight_charge_text').text(formatRupiah(res.freight || 0));
            $('#remote_charge_text').text(formatRupiah(res.remote || 0));
            $('#fuel_charge_text').text(formatRupiah(res.fuel || 0));
            $('#gogreen_charge_text').text(formatRupiah(res.gogreen || 0));
            $('#insurance_charge_text').text(formatRupiah(res.insurance || 0));
            $('#packing_charge_text').text(formatRupiah(res.packing || 0));
            $('#additional_charge_text').text(formatRupiah(res.additional || 0));
            $('#discount_charge_text').text(formatRupiah(res.discount || 0));

            // Update total & laba
            $('#grand_total_text').text(formatRupiah(res.customer_total || 0));
            $('#vendor_total_text').text(formatRupiah(res.vendor_total || 0));
            $('#profit_text').text(formatRupiah(res.profit || 0));
            $('#margin_text').text((res.margin || 0).toFixed(1) + ' %');
        },
        error: function(xhr, status) {
            if (status === 'abort') return;
            console.error('Pricing Error:', xhr.responseText);
            $('#freight_charge_text').text('Error');
            $('#grand_total_text').text('Error');
            $('#profit_text').text('Error');
        },
        complete: function() {
            pricingXHR = null;
        }
    });
}

// ✅ TRIGGER OTOMATIS SESUAI SPESIFIKASI
$(document).ready(function() {
    $('#customer_id').change(triggerPricing);
    $('#country_id').change(triggerPricing);
    $('#service_id').change(triggerPricing);
    $('#vendor_id').change(triggerPricing);
    $('#commodity_type_id').change(triggerPricing);
    $('#chargeable_weight').keyup(triggerPricing);
    $('#actual_weight').keyup(triggerPricing);
    $('#volume_weight').keyup(triggerPricing);
});