/*
=========================================
CARTENZ ERP
Shipment Loader
=========================================
*/

document.addEventListener("DOMContentLoaded", function () {

    if (typeof initializeSender === "function")
        initializeSender();

    if (typeof initializeReceiver === "function")
        initializeReceiver();

    if (typeof initializePackage === "function")
        initializePackage();

    if (typeof initializePricing === "function")
        initializePricing();

    if (typeof initializeCharges === "function")
        initializeCharges();

    if (typeof initializeProfit === "function")
        initializeProfit();

    if (typeof initializeCommodityRule === "function")
        initializeCommodityRule();

});


document.addEventListener("DOMContentLoaded", function(){

    if(typeof initializePricing==="function")
        initializePricing();

    if(typeof initializeCharges==="function")
        initializeCharges();

    if(typeof initializeProfit==="function")
        initializeProfit();

    if(typeof initializeCommodityRule==="function")
        initializeCommodityRule();

});