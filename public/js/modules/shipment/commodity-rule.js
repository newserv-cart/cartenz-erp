/*
=========================================================
CARTENZ ERP
Module : Shipment
Sprint : 24
File   : commodity-rule.js
=========================================================
*/

function initializeCommodityRule(){
    const commodity = document.getElementById("commodity_type_id");
    if(commodity){
        commodity.addEventListener("change", loadCommodityRule);
    }
}

function loadCommodityRule(){
    const commodity_id = document.getElementById("commodity_type_id")?.value;

    if(!commodity_id){
        console.log("Komoditas belum dipilih");
        return;
    }

    console.log("LOAD COMMODITY RULE :", commodity_id);

    // Panggil ke Controller yang benar
    fetch(BASE_URL + "/master/commodity-charge-rules/get-rule/" + commodity_id, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('input[name="csrf_test_name"]')?.value || ""
        }
    })
    .then(res => res.json())
    .then(function(res){
        console.log("RULE :", res);

        if(!res.success){
            console.info("ℹ️ Tidak ada aturan biaya untuk komoditas ini");
            // Set nilai ke 0 jika tidak ada aturan
            setDefaultCharges();
            return;
        }

        let rule = res.rule;

        // Isi nilai ke input
        if(document.getElementById("insurance_customer"))
            document.getElementById("insurance_customer").value = rule.customer_insurance ?? 0;

        if(document.getElementById("packing_customer"))
            document.getElementById("packing_customer").value = rule.customer_packing ?? 0;

        if(document.getElementById("additional_customer"))
            document.getElementById("additional_customer").value = rule.customer_additional ?? 0;

        if(document.getElementById("insurance_vendor"))
            document.getElementById("insurance_vendor").value = rule.vendor_insurance ?? 0;

        if(document.getElementById("packing_vendor"))
            document.getElementById("packing_vendor").value = rule.vendor_packing ?? 0;

        if(document.getElementById("additional_vendor"))
            document.getElementById("additional_vendor").value = rule.vendor_additional ?? 0;

        // Hitung ulang total biaya
        recalculateAll();
    })
    .catch(function(err){
        console.error("❌ Gagal memuat aturan:", err);
        setDefaultCharges();
    });
}

// Bantu fungsi: set semua biaya ke 0 jika tidak ada aturan
function setDefaultCharges(){
    ["insurance_customer", "packing_customer", "additional_customer",
     "insurance_vendor", "packing_vendor", "additional_vendor"].forEach(id => {
        if(document.getElementById(id)) document.getElementById(id).value = 0;
     });
    recalculateAll();
}

// Bantu fungsi: panggil semua perhitungan
function recalculateAll(){
    if(typeof calculateGrandTotal === "function") calculateGrandTotal();
    if(typeof calculateVendorTotal === "function") calculateVendorTotal();
    if(typeof calculateProfit === "function") calculateProfit();
    if(typeof loadPricing === "function") loadPricing();
}

document.addEventListener("DOMContentLoaded", initializeCommodityRule);