/*
=========================================================
CARTENZ ERP
Module : Shipment
Sprint : 20
File   : charges.js
=========================================================
*/

function initializeCharges(){

    initializeChargeCalculation();

    initializeChargesCollapse();

}

// ======================================
// REGISTER EVENT
// ======================================

function initializeChargeCalculation(){

    const ids=[

        "insurance_customer",

        "packing_customer",

        "additional_customer",

        "discount_amount",

        "selling_amount"

    ];

    ids.forEach(function(id){

        const obj=document.getElementById(id);

        if(obj){

            obj.addEventListener("input",calculateGrandTotal);

            obj.addEventListener("change",calculateGrandTotal);

        }

    });

    calculateGrandTotal();

}

// ======================================
// GRAND TOTAL
// ======================================

function calculateGrandTotal(){

    let shipment =
        parseFloat(document.getElementById("selling_amount")?.value) || 0;

    let insurance =
        parseFloat(document.getElementById("insurance_customer")?.value) || 0;

    let packing =
        parseFloat(document.getElementById("packing_customer")?.value) || 0;

    let additional =
        parseFloat(document.getElementById("additional_customer")?.value) || 0;

    let discount =
        parseFloat(document.getElementById("discount_amount")?.value) || 0;

    let other =
        insurance +
        packing +
        additional;

    let grand =
        shipment +
        other -
        discount;

    if(other < 0){
        other = 0;
    }

    if(grand < 0){
        grand = 0;
    }

    const otherText =
        document.getElementById("other_charge_text");

    if(otherText){

        otherText.innerHTML =
            "Rp " + other.toLocaleString("id-ID");

    }

    const grandText =
        document.getElementById("grand_total_text");

    if(grandText){

        grandText.innerHTML =
            "Rp " + grand.toLocaleString("id-ID");

    }

    const hidden =
        document.getElementById("grand_total");

    if(hidden){

        hidden.value = grand;

    }

}

// ======================================
// COLLAPSE ICON
// ======================================

function initializeChargesCollapse(){

    const collapse =
        document.getElementById("chargesCollapse");

    const icon =
        document.getElementById("chargesIcon");

    if(collapse && icon){

        collapse.addEventListener("show.bs.collapse",function(){

            icon.innerHTML="▲";

        });

        collapse.addEventListener("hide.bs.collapse",function(){

            icon.innerHTML="▼";

        });

    }

}