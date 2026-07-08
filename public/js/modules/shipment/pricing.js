/*
=========================================================
CARTENZ ERP
Module : Shipment
Sprint : 19
File   : pricing.js
=========================================================
*/

let rateTimer = null;

function initializePricing() {
    [
        "service_id",
        "receiver_country",
        "country_id",
        "total_weight",
        "total_chargeable",
        "customer_id"
    ].forEach(function(id) {
        const obj = document.getElementById(id);
        if (obj) {
            obj.addEventListener("change", loadPricing);
            obj.addEventListener("keyup", loadPricing);
        }
    });
}

// Fungsi utama sesuai format baru
function loadPricing() {
    // Ambil nilai dasar untuk pengecekan
    const customer_id    = $("#customer_id").val();
    const service_id     = $("#service_id").val();
    const country_id     = $("#country_id").val();
    const actual_weight  = $("#total_weight").val();
    const volume_weight  = $("#total_chargeable").val();

    // Validasi data wajib
    if (!customer_id || !service_id || !country_id || !actual_weight || !volume_weight) {
        return;
    }

    $.ajax({
        url: BASE_URL + "/pricing/calculate",
        type: "POST",
        dataType: "json",
        data: {
            customer_id: customer_id,
            service_id: service_id,
            country_id: country_id,
            commodity_type_id: $("#commodity_type_id").val() || 0,
            actual_weight: actual_weight,
            volume_weight: volume_weight,
            insurance_amount: $("#insurance_customer").val() || 0,
            packing_charge: $("#packing_customer").val() || 0,
            additional_charge: $("#additional_customer").val() || 0,
            discount_amount: $("#discount_amount").val() || 0
        },
        success: function(res) {
            if (res.status !== "success") {
                console.warn("Pricing error:", res.message || "Gagal mengambil tarif");
                return;
            }

            const p = res.data;

            // Isi nilai input
            $("#selling_amount").val(p.freight || 0);
            $("#vendor_cost").val(p.vendor_total || 0);
            $("#total_charge").val(p.customer_total || 0);
            $("#profit_amount").val(p.profit || 0);
            $("#margin_percent").val(p.margin || 0);
            $("#markup_percent").val(p.markup || 0);

            // Isi tampilan format uang
            $("#shipment_charge_text").text(formatMoney(p.freight));
            $("#grand_total_text").text(formatMoney(p.customer_total));
            $("#vendor_cost_text").text(formatMoney(p.vendor_total));
            $("#profit_text").text(formatMoney(p.profit));
            $("#margin_text").text((p.margin || 0) + " %");
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error, xhr.responseText);
        }
    });
}

// Fungsi format uang
function formatMoney(x) {
    const amount = Number(x) || 0;
    return "Rp " + amount.toLocaleString("id-ID");
}

// Pemicu tambahan untuk perubahan pada biaya tambahan
$(document).on("change keyup",
    "#insurance_customer, #packing_customer, #additional_customer, #discount_amount, #commodity_type_id",
    loadPricing
);

// Tetap pertahankan fungsi lama jika masih dipakai di tempat lain
function triggerLoadRate() {
    loadPricing();
}

function loadShipmentRate() {
    // Tetap ada jika dipanggil dari tempat lain, tapi mengarah ke logika baru
    loadPricing();
}