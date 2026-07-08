<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShipmentModel;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\Models\PaymentModel;
use App\Models\ManifestModel;
use App\Models\PickupRequestModel;

class DashboardController extends BaseController
{
    public function index()
    {
        // Model
        $shipmentModel = new ShipmentModel();
        $customerModel = new CustomerModel();
        $invoiceModel  = new InvoiceModel();
        $paymentModel  = new PaymentModel();
        $manifestModel = new ManifestModel();
        $pickupModel   = new PickupRequestModel();

        $data = [];

        // =========================
        // KPI TODAY
        // =========================

        $data['todayShipments'] = $shipmentModel
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        $data['todayRevenue'] = $shipmentModel
            ->selectSum('total_charge')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->first()['total_charge'] ?? 0;

        $data['todayManifest'] = $manifestModel
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        $data['pendingPickup'] = $pickupModel
            ->where('pickup_status', 'REQUESTED')
            ->countAllResults();

        // =========================
        // SUMMARY
        // =========================

        $data['totalShipments'] = $shipmentModel->countAllResults();

        $data['totalCustomers'] = $customerModel->countAllResults();

        $data['totalManifest'] = $manifestModel->countAllResults();

        $data['totalInvoices'] = $invoiceModel->countAllResults();

        $data['invoiceValue'] = $invoiceModel
            ->selectSum('total_amount')
            ->first()['total_amount'] ?? 0;

        $data['cashReceived'] = $paymentModel
            ->selectSum('amount')
            ->where('status', 'APPROVED')
            ->first()['amount'] ?? 0;

        $data['outstanding'] = $invoiceModel
            ->selectSum('balance_amount')
            ->first()['balance_amount'] ?? 0;

        $data['pendingPayments'] = $paymentModel
            ->where('status', 'PENDING')
            ->countAllResults();


           

$data['totalManifest'] = $manifestModel->countAllResults();

$data['totalInvoices'] = $invoiceModel->countAllResults();
        // =========================
        // RECENT DATA
        // =========================

        $data['recentShipments'] = $shipmentModel
            ->orderBy('id', 'DESC')
            ->findAll(5);

        $data['recentPayments'] = $paymentModel
            ->orderBy('id', 'DESC')
            ->findAll(5);

        $db = db_connect();

$data['topCustomers'] = $db->query("
SELECT
customers.customer_name,
COUNT(shipments.id) total
FROM shipments
LEFT JOIN customers
ON customers.id=shipments.customer_id
GROUP BY customers.customer_name
ORDER BY total DESC
LIMIT 5
")->getResultArray();


$data['topDestination'] = $db->query("
SELECT
countries.country_name,
COUNT(shipments.id) total
FROM shipments
LEFT JOIN countries
ON countries.id=shipments.country_id
GROUP BY countries.country_name
ORDER BY total DESC
LIMIT 5
")->getResultArray();


$data['shipmentStatus'] = $db->query("
SELECT
shipment_status,
COUNT(*) total
FROM shipments
GROUP BY shipment_status
")->getResultArray();


$data['monthlyRevenue'] = $db->query("
SELECT

MONTH(created_at) bulan,

SUM(total_charge) total

FROM shipments

GROUP BY MONTH(created_at)

ORDER BY bulan

")->getResultArray();





$data['topAgents'] = $db->query("
SELECT
agents.agent_name,
COUNT(shipments.id) total
FROM shipments
LEFT JOIN agents
ON agents.id = shipments.agent_id
GROUP BY agents.agent_name
ORDER BY total DESC
LIMIT 5
")->getResultArray();



$data['countryRevenue'] = $db->query("
SELECT
countries.country_name,
SUM(shipments.total_charge) total
FROM shipments
LEFT JOIN countries
ON countries.id = shipments.country_id
GROUP BY countries.country_name
ORDER BY total DESC
LIMIT 10
")->getResultArray();






        return view('admin/dashboard/index', $data);
    }
}