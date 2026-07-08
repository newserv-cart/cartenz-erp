/*
=========================================================
CARTENZ ERP
Module : Shipment
Sprint : 21
File   : profit.js
=========================================================
*/

function initializeProfit(){

    const ids = [

        "vendor_total",

        "insurance_vendor",

        "packing_vendor",

        "additional_vendor",

        "grand_total"

    ];

    ids.forEach(function(id){

        const obj = document.getElementById(id);

        if(obj){

            obj.addEventListener("input", function(){

                calculateVendorTotal();

            });

            obj.addEventListener("change", function(){

                calculateVendorTotal();

            });

        }

    });

    calculateVendorTotal();

}

// ======================================
// HITUNG TOTAL BIAYA VENDOR
// ======================================

function calculateVendorTotal(){

    let freight =
        parseFloat(document.getElementById("vendor_total")?.value) || 0;

    let insurance =
        parseFloat(document.getElementById("insurance_vendor")?.value) || 0;

    let packing =
        parseFloat(document.getElementById("packing_vendor")?.value) || 0;

    let additional =
        parseFloat(document.getElementById("additional_vendor")?.value) || 0;

    let total =
        freight +
        insurance +
        packing +
        additional;

    const hidden =
        document.getElementById("vendor_total_charge");

    if(hidden){

        hidden.value = total;

    }

    const text =
        document.getElementById("vendor_cost_text");

    if(text){

        text.innerHTML =
            "Rp " + total.toLocaleString("id-ID");

    }

    calculateProfit();

}

// ======================================
// HITUNG PROFIT
// ======================================

function calculateProfit(){

    let customer =
        parseFloat(document.getElementById("grand_total")?.value) || 0;

    let vendor =
        parseFloat(document.getElementById("vendor_total_charge")?.value) || 0;

    let profit =
        customer - vendor;

    let margin = 0;

    if(customer > 0){

        margin = (profit / customer) * 100;

    }

    const profitText =
        document.getElementById("profit_text");

    if(profitText){

        profitText.innerHTML =
            "Rp " + profit.toLocaleString("id-ID");

    }

    const marginText =
        document.getElementById("margin_text");

    if(marginText){

        marginText.innerHTML =
            margin.toFixed(2) + " %";

    }

    const hidden =
        document.getElementById("profit_amount");

    if(hidden){

        hidden.value = profit;

    }

}