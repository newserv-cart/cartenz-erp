<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

////$routes->get('/', 'Home::index');

$routes->get('/', static function () {
    if (! auth()->loggedIn()) {
        return redirect()->to(site_url('login'));
    }

    $user = auth()->user();

    if (
        $user !== null
        && (
            $user->inGroup('superadmin')
            || $user->inGroup('admin')
        )
    ) {
        return redirect()->to(site_url('shipments'));
    }

    if (
        $user !== null
        && $user->inGroup('user')
    ) {
        return redirect()->to(
            site_url('customer/dashboard')
        );
    }

    return redirect()->to(site_url('login'));
});

service('auth')->routes($routes);

$routes->get('/shipments', 'ShipmentController::index');
$routes->get('/shipments/test-awb', 'ShipmentController::testAwb');

$routes->get('shipments/create', 'ShipmentController::create');
$routes->post('shipments/store', 'ShipmentController::store');

$routes->get('shipments/view/(:num)', 'ShipmentController::view/$1');


$routes->get('shipments/edit/(:num)', 'ShipmentController::edit/$1');
$routes->post('shipments/update/(:num)', 'ShipmentController::update/$1');

$routes->post(
    'shipments/cancel/(:num)',
    'ShipmentController::cancel/$1',
    ['filter' => 'group:superadmin,admin']
);

$routes->post(
    'shipments/delete/(:num)',
    'ShipmentController::delete/$1',
    ['filter' => 'group:superadmin,admin']
);
$routes->get('test-pdf', 'ShipmentController::testPdf');

$routes->get(
    'shipments/print/(:num)',
    'ShipmentController::printAwb/$1'
);

$routes->post(
    'shipments/tracking/add/(:num)',
    'ShipmentController::addTracking/$1'
);

// Public customer tracking — no authentication filter.
$routes->get(
    'track',
    'ShipmentController::publicTrackingIndex'
);

$routes->post(
    'track/search',
    'ShipmentController::publicTrackingSearch'
);

$routes->get(
    'track/(:segment)',
    'ShipmentController::track/$1'
);

$routes->get(
    'tracking-status',
    'TrackingStatusController::index'
);

$routes->post(
    'shipments/quick-status/(:num)/(:segment)',
    'ShipmentController::quickStatus/$1/$2'
);

$routes->group(
    'manifests',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get(
            '',
            'ManifestController::index'
        );

        $routes->get(
            'create',
            'ManifestController::create'
        );

        $routes->post(
            'store',
            'ManifestController::store'
        );

        $routes->get(
            'view/(:num)',
            'ManifestController::view/$1'
        );

        $routes->post(
            'add-shipment/(:num)',
            'ManifestController::addShipment/$1'
        );

        $routes->post(
            'remove-shipment/(:num)/(:num)',
            'ManifestController::removeShipment/$1/$2'
        );

        $routes->post(
            'dispatch/(:num)',
            'ManifestController::dispatch/$1'
        );

        $routes->post(
            'receive/(:num)',
            'ManifestController::receive/$1'
        );

        $routes->post(
            'close/(:num)',
            'ManifestController::close/$1'
        );

        $routes->get(
            'print/(:num)',
            'ManifestController::printManifest/$1'
        );

        $routes->get(
            'scan',
            'ManifestController::scan'
        );

        $routes->post(
            'scan',
            'ManifestController::scanSearch'
        );
    }
);



$routes->get('register-customer', 'CustomerPortalController::register');
$routes->post('register-customer', 'CustomerPortalController::store');


$routes->get(
    'customers/pending',
    'CustomerController::pending'
);



$routes->get(
    'customers/reject/(:num)',
    'CustomerController::reject/$1'
);

$routes->group(
    'customer',
    ['filter' => 'group:user'],
    static function ($routes) {
        $routes->get(
            'dashboard',
            'CustomerDashboardController::index'
        );

        $routes->get(
            'my-shipments',
            'CustomerDashboardController::myShipments'
        );

        $routes->get(
            'shipment/(:num)',
            'CustomerDashboardController::shipmentDetail/$1'
        );

        $routes->get(
            'create-shipment',
            'CustomerShipmentController::create'
        );

        $routes->post(
            'create-shipment',
            'CustomerShipmentController::store'
        );

        $routes->get(
            'addresses',
            'CustomerAddressController::index'
        );

        $routes->get(
            'addresses/create',
            'CustomerAddressController::create'
        );

        $routes->post(
            'addresses/store',
            'CustomerAddressController::store'
        );

        $routes->get(
            'addresses/edit/(:num)',
            'CustomerAddressController::edit/$1'
        );

        $routes->post(
            'addresses/update/(:num)',
            'CustomerAddressController::update/$1'
        );

        $routes->post(
            'addresses/delete/(:num)',
            'CustomerAddressController::delete/$1'
        );

        $routes->post(
            'addresses/default/(:num)',
            'CustomerAddressController::setDefault/$1'
        );

        $routes->post(
            'addresses/ajax-save',
            'CustomerAddressController::saveAddress'
        );
    }
);

$routes->get(
    'shipments/pending',
    'ShipmentApprovalController::pending'
);

$routes->get(
    'shipments/approve/(:num)',
    'ShipmentApprovalController::approve/$1'
);

$routes->get(
   'shipments/approval/view/(:num)',
    'ShipmentApprovalController::view/$1'
);


$routes->get(
    'shipments/reject/(:num)',
    'ShipmentApprovalController::reject/$1'
);






$routes->get(
    'pickup/request/(:num)',
    'PickupController::request/$1'
);



$routes->post(
    'pickup/store',
    'PickupController::store'
);

$routes->get(
    'pickups',
    'PickupController::index'
);

$routes->get(
    'shipments/view/(:num)',
    'ShipmentController::view/$1'
);

$routes->get(
    'pickups/assign/(:num)',
    'PickupController::assign/$1'
);
$routes->get(
    'pickups/done/(:num)',
    'PickupController::done/$1'
);



$routes->post(
    'invoices/generate',
    'InvoiceController::generate'
);

$routes->get(
    'invoices',
    'InvoiceController::index'
);

$routes->get(
'invoices/create',
'InvoiceController::create'
);


$routes->post('invoices/store',
'InvoiceController::store'
);


$routes->post(
    'payments/receive/(:num)',
    'PaymentController::receive/$1'
);

$routes->get(
    'invoices/show/(:num)',
    'InvoiceController::show/$1'
);

$routes->get('company', 'CompanyController::index');
$routes->post('company/update', 'CompanyController::update');

$routes->get(
    'invoices/pdf/(:num)',
    'InvoiceController::pdf/$1'
);

$routes->get(
    'soa',
    'SoaController::index'
);

$routes->get(
    'aging-report',
    'AgingReportController::index'
);

$routes->get(
    'finance/lock-customers',
    'FinanceController::lockOverdueCustomers'
);

$routes->get(
    'customer/my-invoices',
    'CustomerInvoiceController::index'
);

$routes->get(
    'customer/payments/create/(:num)',
    'Customer\PaymentController::create/$1'
);

$routes->post(
    'customer/payments/store',
    'Customer\PaymentController::store'
);

$routes->get(
    'finance/payments',
    'Finance\PaymentApprovalController::index'
);

$routes->get(
    'finance/payments/approve/(:num)',
    'Finance\PaymentApprovalController::approve/$1'
);

$routes->get(
    'finance/payments/reject/(:num)',
    'Finance\PaymentApprovalController::reject/$1'
);
$routes->get(
    'customer/my-payments',
    'Customer\PaymentController::myPayments'
);

$routes->get(
    'finance/dashboard',
    'Finance\DashboardController::index'
);

$routes->get(
    'finance/aging-report',
    'AgingReportController::index'
);

$routes->get(
    'admin/dashboard',
   'Admin\DashboardController::index'
);

$routes->get(
    'agent/dashboard',
    'AgentDashboardController::index'
);

$routes->get(
    'agent/customers',
    'AgentCustomerController::index'
);

$routes->get(
    'agent/customer/(:num)',
    'AgentCustomerController::detail/$1'
);

$routes->get(
    'shipments/label/(:num)',
    'ShipmentController::printLabel/$1'
);

$routes->get(
    'manifest/print/(:num)',
    'ManifestController::print/$1'
);

$routes->get(
    'shipments/label/(:num)',
    'ShipmentController::label/$1'
);

$routes->get(
    'manifests/scan',
    'ManifestController::scan'
);

$routes->post(
    'manifests/scan',
    'ManifestController::scanSearch'
);

$routes->get(
    'manifests/scan',
    'ManifestController::scan'
);

$routes->post(
    'manifests/scan',
    'ManifestController::scanSearch'
);

$routes->get(
    'agent/shipment/(:num)',
    'AgentShipmentController::detail/$1'
);

$routes->get(
    'agent/invoices',
    'AgentInvoiceController::index'
);

$routes->get(
    'agent/invoice/(:num)',
    'AgentInvoiceController::detail/$1'
);

$routes->get(
    'agent/invoices-cartenz',
    'AgentInvoiceController::index'
);

$routes->get(
    'agent/invoice-cartenz/(:num)',
    'AgentInvoiceController::detail/$1'
);

$routes->get(
    'agent/payment/upload/(:num)',
    'AgentPaymentController::create/$1'
);

$routes->post(
    'agent/payment/upload/(:num)',
    'AgentPaymentController::store/$1'
);


$routes->get('test-store', function () {
    dd('ROUTE HIDUP');



$routes->get('test-agent-payment', 'AgentPaymentController::test');

});

$routes->get(
    'test-agent',
    'AgentPaymentController::test'
);

/*
|--------------------------------------------------------------------------
| CUSTOMER PAYMENTS
|--------------------------------------------------------------------------
*/

$routes->get(
    'finance/customer-payments',
    'Finance\CustomerPaymentController::index'
);

$routes->get(
    'finance/customer-payments/approve/(:num)',
    'Finance\CustomerPaymentController::approve/$1'
);

$routes->get(
    'finance/customer-payments/reject/(:num)',
    'Finance\CustomerPaymentController::reject/$1'
);

/*
|--------------------------------------------------------------------------
| AGENT PAYMENTS
|--------------------------------------------------------------------------
*/

$routes->get(
    'finance/agent-payments',
    'Finance\AgentPaymentController::index'
);

$routes->get(
    'finance/agent-payments/approve/(:num)',
    'Finance\AgentPaymentController::approve/$1'
);

$routes->get(
    'finance/agent-payments/reject/(:num)',
    'Finance\AgentPaymentController::reject/$1'
);

$routes->group('admin', ['filter'=>'group:superadmin,admin'], function($routes){

    $routes->get('dashboard','Admin\DashboardController::index');

});

$routes->post(
    'customer/address/save',
    'CustomerAddressController::saveAddress'
);

$routes->get(
    'customer-addresses/search',
    'CustomerAddressController::search'
);

$routes->post(
    'shipments/calculate-rate',
    'ShipmentController::calculateRate'
);

$routes->group('master',function($routes){

    $routes->get(
        'regular-tariffs',
        'Master\RegularTariffController::index'
    );

});

/*
|--------------------------------------------------------------------------
| MASTER - REGULAR TARIFF
|--------------------------------------------------------------------------
*/

$routes->group('master', function($routes){

    $routes->get('regular-tariffs', 'Master\RegularTariffController::index');

    $routes->get('regular-tariffs/manual', 'Master\RegularTariffController::manual');
    $routes->post('regular-tariffs/save-manual', 'Master\RegularTariffController::saveManual');

    $routes->get('regular-tariffs/upload', 'Master\RegularTariffController::upload');
    $routes->get('regular-tariffs/download-template', 'Master\RegularTariffController::downloadTemplate');
    $routes->get('regular-tariffs/clone', 'Master\RegularTariffController::cloneTariff');

});


$routes->get('master/regular-tariffs/manual', 'Master\RegularTariffController::manual');
$routes->post('master/regular-tariffs/save-manual', 'Master\RegularTariffController::saveManual');

$routes->get('master/regular-tariffs/load','Master\RegularTariffController::loadTariff');

$routes->get('master/regular-tariffs/upload','Master\RegularTariffController::upload');

$routes->post('master/regular-tariffs/import','Master\RegularTariffController::import');

$routes->get(
    'test-rate',
    'ShipmentController::testRate'
);

$routes->group('master', function($routes){

    $routes->get('commodity-charge-rules', 'Master\CommodityChargeRuleController::index');

    $routes->get('commodity-charge-rules/create', 'Master\CommodityChargeRuleController::create');

    $routes->post('commodity-charge-rules/store', 'Master\CommodityChargeRuleController::store');

    $routes->get('commodity-charge-rules/edit/(:num)', 'Master\CommodityChargeRuleController::edit/$1');

    $routes->post('commodity-charge-rules/update/(:num)', 'Master\CommodityChargeRuleController::update/$1');

    $routes->get('commodity-charge-rules/delete/(:num)', 'Master\CommodityChargeRuleController::delete/$1');

});

$routes->get(
    'master/commodity-charge-rules/get-rule/(:num)',
    'Master\CommodityChargeRuleController::getRule/$1'
);

$routes->get(
    'shipments/get-commodity-rule',
    'ShipmentController::getCommodityRule'
);

$routes->group('master/express-charge-rules', function($routes){

    $routes->get('/', 'Master\ExpressChargeRuleController::index');

    $routes->get('create', 'Master\ExpressChargeRuleController::create');
    $routes->post('store', 'Master\ExpressChargeRuleController::store');

    $routes->get('edit/(:num)', 'Master\ExpressChargeRuleController::edit/$1');
    $routes->post('update/(:num)', 'Master\ExpressChargeRuleController::update/$1');

    $routes->get('delete/(:num)', 'Master\ExpressChargeRuleController::delete/$1');

});

$routes->group('master',function($routes){

    $routes->get('gogreen-zones',
        'Master\GoGreenZoneController::index');

    $routes->get('gogreen-zones/create',
        'Master\GoGreenZoneController::create');

    $routes->post('gogreen-zones/store',
        'Master\GoGreenZoneController::store');


    $routes->get('gogreen-zones/edit/(:num)', 'Master\GoGreenZoneController::edit/$1');
    $routes->post('gogreen-zones/update/(:num)', 'Master\GoGreenZoneController::update/$1');

    $routes->get('gogreen-zones/delete/(:num)', 'Master\GoGreenZoneController::delete/$1');


});

$routes->group('master',function($routes){

    $routes->get('gogreen-rules',
        'Master\GoGreenRuleController::index');

    $routes->get('gogreen-rules/create',
        'Master\GoGreenRuleController::create');

    $routes->post('gogreen-rules/store',
        'Master\GoGreenRuleController::store');

    $routes->get('gogreen-rules/edit/(:num)',
        'Master\GoGreenRuleController::edit/$1');

    $routes->post('gogreen-rules/update/(:num)',
        'Master\GoGreenRuleController::update/$1');

    $routes->get('gogreen-rules/delete/(:num)',
        'Master\GoGreenRuleController::delete/$1');

});

$routes->group('master', function($routes){

    $routes->get('gogreen-rules', 'Master\GoGreenRuleController::index');

    $routes->get('gogreen-rules/create', 'Master\GoGreenRuleController::create');

    $routes->post('gogreen-rules/store', 'Master\GoGreenRuleController::store');

    $routes->get('gogreen-rules/edit/(:num)', 'Master\GoGreenRuleController::edit/$1');

    $routes->post('gogreen-rules/update/(:num)', 'Master\GoGreenRuleController::update/$1');

    $routes->get('gogreen-rules/delete/(:num)', 'Master\GoGreenRuleController::delete/$1');

});

$routes->group('master', function($routes){

    $routes->get('fuel-rules','Master\FuelRuleController::index');

    $routes->get('fuel-rules/create','Master\FuelRuleController::create');

    $routes->post('fuel-rules/store','Master\FuelRuleController::store');

    $routes->get('fuel-rules/edit/(:num)','Master\FuelRuleController::edit/$1');

    $routes->post('fuel-rules/update/(:num)','Master\FuelRuleController::update/$1');

    $routes->get('fuel-rules/delete/(:num)','Master\FuelRuleController::delete/$1');

});

$routes->group(
    'master/vendors',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get(
            '',
            'Master\VendorController::index'
        );

        $routes->get(
            'create',
            'Master\VendorController::create'
        );

        $routes->post(
            'store',
            'Master\VendorController::store'
        );

        $routes->get(
            '(:num)',
            'Master\VendorController::show/$1'
        );

        $routes->get(
            'edit/(:num)',
            'Master\VendorController::edit/$1'
        );

        $routes->post(
            'update/(:num)',
            'Master\VendorController::update/$1'
        );

        $routes->post(
            'toggle-status/(:num)',
            'Master\VendorController::toggleStatus/$1'
        );

        $routes->get(
            '(:num)/regular-rates/create',
            'Master\VendorRateController::createRegular/$1'
        );

        $routes->post(
            '(:num)/regular-rates/store',
            'Master\VendorRateController::storeRegular/$1'
        );

        $routes->get(
            '(:num)/regular-rates/edit/(:num)',
            'Master\VendorRateController::editRegular/$1/$2'
        );

        $routes->post(
            '(:num)/regular-rates/update/(:num)',
            'Master\VendorRateController::updateRegular/$1/$2'
        );

        $routes->post(
            '(:num)/regular-rates/delete/(:num)',
            'Master\VendorRateController::deleteRegular/$1/$2'
        );

        $routes->get(
            '(:num)/express-rates/create',
            'Master\VendorRateController::createExpressHeader/$1'
        );

        $routes->post(
            '(:num)/express-rates/store',
            'Master\VendorRateController::storeExpressHeader/$1'
        );

        $routes->get(
            '(:num)/express-rates/(:num)',
            'Master\VendorRateController::showExpressHeader/$1/$2'
        );

        $routes->post(
            '(:num)/express-rates/delete/(:num)',
            'Master\VendorRateController::deleteExpressHeader/$1/$2'
        );

        $routes->post(
            '(:num)/express-rates/(:num)/details/store',
            'Master\VendorRateController::storeExpressDetail/$1/$2'
        );

        $routes->post(
            '(:num)/express-rates/(:num)/details/delete/(:num)',
            'Master\VendorRateController::deleteExpressDetail/$1/$2/$3'
        );


        $routes->get(
            'express-import/template',
            'Master\ExpressRateImportController::downloadTemplate'
        );

        $routes->get(
            '(:num)/express-import',
            'Master\ExpressRateImportController::form/$1'
        );

        $routes->post(
            '(:num)/express-import/preview',
            'Master\ExpressRateImportController::preview/$1'
        );

        $routes->get(
            '(:num)/express-import/preview/(:segment)',
            'Master\ExpressRateImportController::showPreview/$1/$2'
        );

        $routes->post(
            '(:num)/express-import/commit',
            'Master\ExpressRateImportController::commit/$1'
        );
    }
);

$routes->group(
    'shipments/vendor',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->post(
            '(:num)/recommendations/generate',
            'ShipmentVendorController::generate/$1'
        );

        $routes->post(
            '(:num)/assign',
            'ShipmentVendorController::assign/$1'
        );

        $routes->post(
            '(:num)/submit',
            'ShipmentVendorController::submit/$1'
        );

        $routes->post(
            '(:num)/tracking-events',
            'ShipmentVendorController::addTrackingEvent/$1'
        );
    }
);

$routes->get('shipments/detail/(:num)', 'ShipmentController::detail/$1');


$routes->post(
    'api/shipment/calculate',
    'ShipmentController::calculatePricing'
);


$routes->post('pricing/calculate','PricingController::calculate');



// Muat daftar tarif


// Simpan manual
$routes->post('master/regular-tariffs/save-manual', 'Master\RegularTariffController::saveManual');

// Download template
$routes->get('master/regular-tariffs/download-template', 'Master\RegularTariffController::downloadTemplate');


$routes->post('customers/store_ajax', 'Customers::store_ajax');

$routes->get( 'test/rule-engine',    'RuleEngineTestController::index');

$routes->get('test/pricing', 'PricingTestController::index');
$routes->post(
    'shipments/calculate-pricing',
    'ShipmentController::calculatePricing'
);

// Sprint 49.15B.2 — Shipment Acceptance & Inbound Intake
$routes->post(
    'shipments/direct-receive/(:num)',
    'ShipmentController::directReceive/$1',
    ['filter' => 'group:superadmin,admin']
);

$routes->group(
    'inbound-intakes',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get('', 'InboundIntakeController::index');
        $routes->get('create', 'InboundIntakeController::create');
        $routes->post('store', 'InboundIntakeController::store');
        $routes->get('(:num)', 'InboundIntakeController::show/$1');
        $routes->get('(:num)/start-shipment', 'InboundIntakeController::startShipment/$1');
        $routes->post('(:num)/cancel', 'InboundIntakeController::cancel/$1');
    }
);

// Sprint 49.15B.3 — Branch-aware acceptance, scan, and manifest origin
$routes->group(
    'admin/operational-profiles',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get(
            '',
            'Admin\UserOperationalProfileController::index'
        );

        $routes->post(
            'save',
            'Admin\UserOperationalProfileController::save'
        );

        $routes->post(
            'toggle/(:num)',
            'Admin\UserOperationalProfileController::toggle/$1'
        );
    }
);

$routes->group(
    'operations',
    ['filter' => 'auth'],
    static function ($routes) {
        $routes->get(
            'scan',
            'Operations\BranchScanController::index'
        );

        $routes->post(
            'scan',
            'Operations\BranchScanController::submit'
        );
    }
);

// Sprint 49.17 — Agent Charges & Self Billing
$routes->group(
    'agent/self-billing',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get('', 'AgentSelfBillingController::index');
        $routes->post('create', 'AgentSelfBillingController::createInvoice');
        $routes->get('invoice/(:num)', 'AgentSelfBillingController::show/$1');
        $routes->get('invoice/(:num)/print', 'AgentSelfBillingController::print/$1');
    }
);

$routes->group(
    'finance/agent-self-billing',
    ['filter' => 'group:superadmin,admin'],
    static function ($routes) {
        $routes->get('', 'Finance\\AgentSelfBillingApprovalController::index');
        $routes->post('approve/(:num)', 'Finance\\AgentSelfBillingApprovalController::approve/$1');
        $routes->post('reject/(:num)', 'Finance\\AgentSelfBillingApprovalController::reject/$1');
    }
);


$routes->group(
    'operations/pickups',
    ['filter' => 'session'],
    static function ($routes) {
        $routes->get(
            '/',
            'PickupOperationsController::index'
        );

        $routes->post(
            '(:num)/schedule',
            'PickupOperationsController::schedule/$1'
        );

        $routes->post(
            '(:num)/assign',
            'PickupOperationsController::assign/$1'
        );

        $routes->post(
            '(:num)/picked-up',
            'PickupOperationsController::markPickedUp/$1'
        );
    }
);