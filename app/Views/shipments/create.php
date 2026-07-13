<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Shipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<form method="post" action="<?= site_url('shipments/store') ?>">
    <?= csrf_field() ?>

<div class="container mt-4 mb-5">
    <h2 class="mb-4">Create Shipment</h2>

    <!-- =======================================
         CUSTOMER / SENDER INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-danger fw-bold">
            🏢 CUSTOMER INFORMATION
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-9">
                    <label class="form-label fw-bold">🏢 Customer</label>
                    <input
    type="hidden"
    name="customer_id"
    id="real_customer_id"
    value="">

<select
    name="sender_address_id"
    id="customer_id"
    class="form-select">
                        <option value="">-- Select Customer --</option>
                        <?php foreach($senderAddresses as $row): ?>
                        <option
                            value="<?= $row['id'] ?>"
                            data-customer-id="<?= esc($row['customer_id'] ?? '') ?>"v
                            data-country-id="<?= $row['country_id']?>"
                            data-company="<?= esc($row['company_name']) ?>"
                            data-phone="<?= esc($row['phone']) ?>"
                            data-email="<?= esc($row['email']) ?>"
                            data-city="<?= esc($row['city']) ?>"
                            data-state="<?= esc($row['state']) ?>"
                             data-postal-code="<?= esc($row['postal_code']) ?>"
                            data-address="<?= esc($row['address']) ?>"
                            data-country="<?= esc($row['country_name']) ?>"
                            data-identity="<?= esc($row['identity_number']) ?>">
                            <?= esc($row['contact_name']) ?>
                            <?= $row['company_name'] ? ' - '.esc($row['company_name']) : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="btnAddCustomer" class="btn btn-success">➕ Add Customer</button>
                </div>
            </div>

          <div class="row">
    <div class="col-md-6 mb-3">
        <label>Company</label>
        <input id="sender_company" class="form-control" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label>Phone</label>
        <input id="sender_phone" class="form-control" readonly>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Email</label>
        <input id="sender_email" class="form-control" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label>Identity Number</label>
        <input id="sender_identity" class="form-control" readonly>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Province</label>
        <input id="sender_province" class="form-control" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label>City</label>
        <input id="sender_city" class="form-control" readonly>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Postal Code</label>
        <input id="sender_postal_code" class="form-control" readonly>
    </div>
    <div class="col-md-6 mb-3">
        <label>Country</label>
        <input id="sender_country" class="form-control" readonly>
    </div>
</div>

<div class="mb-3">
    <label>Complete Address</label>
    <textarea id="sender_address" class="form-control" rows="3" readonly></textarea>
</div>

    <!-- =======================================
         RECEIVER INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning">
            👤 Receiver Information
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-9">
<!-- DROPDOWN RECEIVER YANG BENAR -->
<select id="receiver_id" name="receiver_id" class="form-select">
    <option value="">-- Select Receiver --</option>
    <?php foreach($receiverAddresses as $row): ?>
    <option
        value="<?= $row['id'] ?>"
        data-country-id="<?= esc($row['country_id']) ?>"
        data-company="<?= esc($row['company_name']) ?>"
        data-phone="<?= esc($row['phone']) ?>"
        data-email="<?= esc($row['email']) ?>"
        data-city="<?= esc($row['city']) ?>"
        data-state="<?= esc($row['state']) ?>"
        data-postal-code="<?= esc($row['postal_code']) ?>"
        data-address="<?= esc($row['address']) ?>"
        data-country="<?= esc($row['country_name']) ?>"
        data-identity="<?= esc($row['identity_number']) ?>"
    >
        <?= esc($row['contact_name']) ?>
        <?= !empty($row['company_name']) ? ' - ' . esc($row['company_name']) : '' ?>
    </option>
    <?php endforeach; ?>
</select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="button" id="btnAddReceiver" class="btn btn-success">➕ Add Receiver</button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Company</label>
                    <input id="receiver_company" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Phone</label>
                    <input id="receiver_phone" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Identity Number</label>
                    <input id="receiver_identity" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Province</label>
                    <input id="receiver_province" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>City</label>
                    <input id="receiver_city" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Postal Code</label>
                    <input id="receiver_postal_code" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Destination Country</label>
                    <input type="hidden" id="country_id" name="country_id">
                    <input type="hidden" id="transport_mode_id" name="transport_mode_id" value="1">
                    <input id="receiver_country" name="receiver_country" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input id="receiver_email" class="form-control" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label>Complete Address</label>
                <textarea id="receiver_address" class="form-control" rows="3" readonly></textarea>
            </div>
        </div>
    </div>

    <!-- =======================================
         SHIPMENT INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            📦 Shipment Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Service <span class="text-danger">*</span></label>
                    <select name="service_id" id="service_id" class="form-select">
                        <option value="">-- Select Service --</option>
                        <?php foreach($services as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= esc($row['service_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Package Type <span class="text-danger">*</span></label>
                    <select name="package_type_id" id="package_type_id" class="form-select">
                        <option value="">-- Select Package Type --</option>
                        <?php foreach($packageTypes as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= esc($row['package_type_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select">
                        <option value="">-- Select Payment --</option>
                        <option value="CASH">CASH</option>
                        <option value="DEPOSIT">DEPOSIT</option>
                        <option value="INVOICE">INVOICE</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Commodity Type <span class="text-danger">*</span></label>
                    <select name="commodity_type_id" id="commodity_type_id" class="form-select">
                        <option value="">-- Select Commodity --</option>
                        <?php foreach($commodityTypes as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= esc($row['commodity_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Reference Number</label>
                    <input type="text" name="customer_reference_no" id="customer_reference_no" class="form-control" placeholder="Customer Reference Number">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Shipment Date</label>
                    <input type="date" name="shipment_date" value="<?= date('Y-m-d') ?>" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Expected Delivery</label>
                    <input type="text" id="expected_delivery" class="form-control" placeholder="Auto Calculate" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================================
         PACKAGE / COLLY INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning">Package / Colly Information</div>
        <div class="card-body">
            <button type="button" class="btn btn-primary me-2">⚡ Generate Multiple Colly</button>
            <button type="button" class="btn btn-success" id="add-colly">+ Add Colly</button>
            <hr>

            <div id="colly-container">
                <!-- Colly #1 -->
                <div class="card border-primary mb-3" id="colly-1">
                    <div class="card-header">
                        <strong>COLLY #1</strong>
                        <div class="float-end">
                            <button type="button" class="btn btn-sm btn-secondary me-1 duplicate-colly">📋 Duplicate</button>
                            <button type="button" class="btn btn-sm btn-danger delete-colly">🗑 Delete</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Weight (Kg)</label>
                                <input type="number" class="form-control berat" name="packages[0][actual_weight]" step="0.01" min="0" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Length (Cm)</label>
                                <input type="number" class="form-control panjang" name="packages[0][length_cm]" min="0" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Width (Cm)</label>
                                <input type="number" class="form-control lebar" name="packages[0][width_cm]" min="0" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Height (Cm)</label>
                                <input type="number" class="form-control tinggi" name="packages[0][height_cm]" min="0" value="0">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Volume Weight</label>
                                <input type="text" class="form-control volume" name="packages[0][volume_weight]" value="0" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Chargeable Weight</label>
                                <input type="text" class="form-control chargeable" name="packages[0][chargeable_weight]" value="0" readonly>
                            </div>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">Items</h6>
                            <button type="button" class="btn btn-sm btn-success add-item">+ Add Item</button>
                        </div>

                        <table class="table table-bordered table-sm item-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">#</th>
                                    <th>Item Description</th>
                                    <th width="100">Qty</th>
                                    <th width="120">Currency</th>
                                    <th width="140">Unit Value</th>
                                    <th width="140">Total Value</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td><input type="text" class="form-control" name="packages[0][items][0][item_description]" placeholder="Item Description"></td>
                                    <td><input type="number" class="form-control qty" name="packages[0][items][0][quantity]" value="1" min="1"></td>
                                    <td>
                                        <select class="form-select currency" name="packages[0][items][0][currency]">
                                            <option value="USD" selected>USD</option>
                                            <option value="IDR">IDR</option>
                                            <option value="SGD">SGD</option>
                                            <option value="MYR">MYR</option>
                                            <option value="TWD">TWD</option>
                                        </select>
                                    </td>
                                    <td><input type="number" name="packages[0][items][0][unit_value]" class="form-control unit_value" step="0.01" value="0.00"></td>
                                    <td><input type="number" class="form-control total_value" name="packages[0][items][0][total_value]" value="0.00" readonly></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================================
         CHARGES INFORMATION
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
            <strong>💰 Charges Information</strong>
            <button class="btn btn-sm btn-link text-dark text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#chargesCollapse">
                <span id="chargesIcon">▼</span>
            </button>
        </div>

        <div class="collapse" id="chargesCollapse">
            <div class="card-body">
                <div class="row mb-3" id="rowRemoteArea">
                    <label class="col-md-4 col-form-label">Remote Area</label>
                    <div class="col-md-8">
                        <select name="is_remote" id="is_remote" class="form-select">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerInsurance">
                    <div class="col-md-3">
                        <label>Customer Insurance</label>
                        <input type="number" id="insurance_customer" name="insurance_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorInsurance">
                        <label>Vendor Insurance</label>
                        <input type="number" id="insurance_vendor" name="insurance_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerPacking">
                    <div class="col-md-3">
                        <label>Customer Packing</label>
                        <input type="number" id="packing_customer" name="packing_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorPacking">
                        <label>Vendor Packing</label>
                        <input type="number" id="packing_vendor" name="packing_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowCustomerAdditional">
                    <div class="col-md-3">
                        <label>Customer Additional</label>
                        <input type="number" id="additional_customer" name="additional_customer" class="form-control text-end" value="0">
                    </div>
                    <div class="col-md-3" id="rowVendorAdditional">
                        <label>Vendor Additional</label>
                        <input type="number" id="additional_vendor" name="additional_vendor" class="form-control text-end" value="0">
                    </div>
                </div>

                <div class="row mb-3" id="rowDiscount">
                    <div class="col-md-3">
                        <label>Discount</label>
                        <input type="number" id="discount_amount" name="discount_amount" class="form-control text-end" value="0">
                    </div>
                </div>

                <div id="expressCharges" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Fuel Surcharge</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>Go Green</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                        <div class="col-md-3">
                            <label>VAT</label>
                            <input type="number" class="form-control text-end" value="0" readonly>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="customer_total" name="customer_total" value="0">
                <input type="hidden" id="vendor_total_charge" name="vendor_total_charge" value="0">
                <input type="hidden" id="profit_amount" name="profit_amount" value="0">
                <input type="hidden" id="selling_amount" value="0">
                <input type="hidden" id="grand_total" name="grand_total" value="0">
                <input type="hidden" id="vendor_total" value="0">

                <table class="table table-bordered mt-4">
                    <tr>
                        <th>Freight Customer</th>
                        <td class="text-end"><span id="shipment_charge_text">Rp 0</span></td>
                    </tr>
                    <tr>
                        <th>Remote Area Charges</th>
                        <td class="text-end"><span id="remote_charge_text">Rp 0</span></td>
                    </tr>
                    <tr>
                        <th>Customer Charges</th>
                        <td class="text-end"><span id="other_charge_text">Rp 0</span></td>
                    </tr>
                    <tr class="table-warning">
                        <th>Customer Total</th>
                        <th class="text-end text-danger"><span id="grand_total_text">Rp 0</span></th>
                    </tr>
                    <tr>
                        <th>Vendor Total</th>
                        <td class="text-end"><span id="vendor_cost_text">Rp 0</span></td>
                    </tr>
                    <tr class="table-success">
                        <th>Profit</th>
                        <th class="text-end"><span id="profit_text">Rp 0</span></th>
                    </tr>
                    <tr>
                        <th>Margin</th>
                        <td class="text-end"><span id="margin_text">0 %</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="text-end mt-3">
        <a href="#" id="btnPricingAnalysis" class="btn btn-outline-primary">📈 Pricing Analysis</a>
    </div>

    <!-- =======================================
         SHIPMENT SUMMARY & ACTIONS
    ======================================= -->
    <div class="card mb-3">
        <div class="card-header bg-dark text-white">Shipment Summary</div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label>Total Colly</label>
                    <input type="text" class="form-control" id="total_colly" name="total_colly" value="1" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Total Weight</label>
                    <input type="text" class="form-control" id="total_berat" name="total_weight" value="1.00" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Declared Value</label>
                    <input type="text" class="form-control" id="total_nilai" name="total_value" value="0.00" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Total Chargeable Weight</label>
                    <input type="text" class="form-control" id="total_chargeable" name="total_chargeable" value="1.00" readonly>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-2 mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2">💾 Save Shipment</button>
                <button type="reset" class="btn btn-secondary px-4 py-2">🔄 Reset</button>
            </div>
        </div>
    </div>

</div>

</form>

<!-- =======================================
     MODAL ADD NEW SENDER
======================================= -->
<div class="modal fade" id="senderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">➕ Add New Sender</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input type="text" id="new_sender_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input type="text" id="new_sender_company" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input type="text" id="new_sender_phone" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="new_sender_email" class="form-control" placeholder="email@example.com">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input type="text" id="new_sender_identity" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Province / State</label>
                        <input type="text" id="new_sender_province" class="form-control" placeholder="e.g. West Java">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <input type="text" id="new_sender_city" class="form-control" placeholder="e.g. Bekasi">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input type="text" id="new_sender_postal_code" class="form-control" placeholder="e.g. 17111">
                    </div>
                </div>

                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea id="new_sender_address" rows="3" class="form-control" placeholder="Street name, building, etc."></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveSender">💾 Save Sender</button>
            </div>
        </div>
    </div>
</div>
<!-- =======================================
     MODAL ADD NEW RECEIVER
======================================= -->
<div class="modal fade" id="receiverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">➕ Add New Receiver</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label>Destination Country *</label>
                        <select id="new_receiver_country" name="country_id" class="form-select">
                            <option value="">-- Select Country --</option>
                            <?php foreach($countries as $country): ?>
                                <option value="<?= $country['id'] ?>"><?= esc($country['country_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Name *</label>
                        <input type="text" id="new_receiver_name" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Company</label>
                        <input type="text" id="new_receiver_company" class="form-control">
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Phone *</label>
                        <input type="text" id="new_receiver_phone" class="form-control">
                    </div>


                    <div class="col-md-6 mb-3">
                        <label>Identity No.</label>
                        <input type="text" id="new_receiver_identity" class="form-control">
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Province / District</label>
                       <input
                        type="text"
                        id="new_receiver_province"
                        class="form-control"
                        placeholder="Province / State">
                    </div>


                    <div class="col-md-6 mb-3">
    <label>City</label>
    <input
        type="text"
        id="new_receiver_city"
        class="form-control"
        placeholder="City">
</div>
</div>


                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input type="text" id="new_receiver_postal" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="new_receiver_email" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea id="new_receiver_address" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnSaveReceiver">💾 Save Receiver</button>
            </div>
        </div>
    </div>
</div>

<!-- =======================================
     SCRIPTS
======================================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = "<?= base_url() ?>";
</script>
<script src="<?= base_url('js/modules/shipment/pricing.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/charges.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/profit.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/commodity-rule.js') ?>"></script>


<script>
console.log('SCRIPT LOADED');


window.userRole = 'admin';


$(document).ready(function () {
    // Panggil fungsi hitungSemua dari summary.js saja
    if (typeof window.hitungSemua === "function") {
        window.hitungSemua();
    }


});


</script>
<script src="<?= base_url('js/modules/shipment/utils.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/summary.js?v=49.4.2') ?>"></script>
<script src="<?= base_url('js/modules/shipment/colly.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/items.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/pricing.js?v=49.7.2') ?>"></script>
<script src="<?= base_url('js/modules/shipment/charges.js?v=49.7.3') ?>"></script>
<script src="<?= base_url('js/modules/shipment/profit.js?v=49.7.3') ?>"></script>
<script src="<?= base_url('js/modules/shipment/sender.js?v=49.7.2') ?>"></script>
<script src="<?= base_url('js/modules/shipment/receiver.js') ?>"></script>
<script src="<?= base_url('js/modules/shipment/shipment.js') ?>"></script>


</body>
</html>
