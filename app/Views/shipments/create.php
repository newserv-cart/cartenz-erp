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
                    <select name="customer_id" id="customer_id" class="form-select">
                        <option value="">-- Select Customer --</option>
                        <?php foreach($senderAddresses as $row): ?>
                        <option
                            value="<?= $row['id'] ?>"
                            data-country-id="<?= $row['country_id']?>"       
                            data-company="<?= esc($row['company_name']) ?>"
                            data-phone="<?= esc($row['phone']) ?>"
                            data-email="<?= esc($row['email']) ?>"
                            data-city="<?= esc($row['city']) ?>"
                            data-state="<?= esc($row['state']) ?>"
                            data-postal="<?= esc($row['postal_code']) ?>"
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
                    <label>Identity Number</label>
                    <input id="sender_identity" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Province</label>
                    <input id="sender_province" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>City</label>
                    <input id="sender_city" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Postal Code</label>
                    <input id="sender_postal_code" class="form-control" readonly>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Country</label>
                    <input id="sender_country" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input id="sender_email" class="form-control" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label>Complete Address</label>
                <textarea id="sender_address" class="form-control" rows="3" readonly></textarea>
            </div>
        </div>
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
                    <label class="form-label fw-bold">👤 Receiver</label>
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
                            data-postal="<?= esc($row['postal_code']) ?>"
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
                                <input type="number" class="form-control berat" name="packages[0][actual_weight]" step="0.01" min="0" value="0" oninput="hitungVolume(this);">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Length (Cm)</label>
                                <input type="number" class="form-control panjang" name="packages[0][length_cm]" min="0" value="0" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Width (Cm)</label>
                                <input type="number" class="form-control lebar" name="packages[0][width_cm]" min="0" value="0" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Height (Cm)</label>
                                <input type="number" class="form-control tinggi" name="packages[0][height_cm]" min="0" value="0" oninput="hitungVolume(this)">
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
                        <label>Identity No.</label>
                        <input type="text" id="new_sender_identity" class="form-control">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Province</label>
                        <select id="new_sender_province" class="form-select">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <select id="new_sender_city" class="form-select">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Postal Code</label>
                        <input type="text" id="new_sender_postal" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" id="new_sender_email" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label>Complete Address</label>
                    <textarea id="new_sender_address" rows="3" class="form-control"></textarea>
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
                        <select id="new_receiver_province" class="form-select">
                            <option value="">Select Province</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>City</label>
                        <select id="new_receiver_city" class="form-select">
                            <option value="">Select City</option>
                        </select>
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
<script src="<?= base_url('js/modules/shipment/shipment.js') ?>"></script>

<script>
console.log('SCRIPT LOADED');

function hitungVolume(el) {
    const card = el.closest('.card.border-primary');
    if (!card) return;

    const p = parseFloat(card.querySelector('.panjang').value) || 0;
    const l = parseFloat(card.querySelector('.lebar').value) || 0;
    const t = parseFloat(card.querySelector('.tinggi').value) || 0;
    const berat = parseFloat(card.querySelector('.berat').value) || 0;
    
    const volume = (p * l * t) / 5000;
    card.querySelector('.volume').value = volume.toFixed(2);
    
    const charge = Math.max(berat, volume);
    card.querySelector('.chargeable').value = charge.toFixed(2);
    
    hitungSemua();
}

function hitungSemua(){
    const semuaBerat = document.querySelectorAll('.berat');
    const semuaCharge = document.querySelectorAll('.chargeable');
    const semuaNilai = document.querySelectorAll('.total_value');
    
    let totalBerat = 0, totalCharge = 0, totalNilai = 0;

    semuaBerat.forEach(el => totalBerat += parseFloat(el.value) || 0);
    semuaCharge.forEach(el => totalCharge += parseFloat(el.value) || 0);
    semuaNilai.forEach(el => totalNilai += parseFloat(el.value) || 0);

    document.getElementById('total_berat').value = totalBerat.toFixed(2);
    document.getElementById('total_chargeable').value = totalCharge.toFixed(2);
    document.getElementById('total_nilai').value = totalNilai.toFixed(2);
    document.getElementById('total_colly').value = document.querySelectorAll('.card.border-primary').length;

    if (typeof loadShipmentRate === "function") {
        triggerLoadRate();
    }
}

function renumberColly(){
    document.querySelectorAll('#colly-container .card.border-primary').forEach((card, index)=>{
        card.id = 'colly-' + (index + 1);
        card.querySelector('strong').innerText = 'COLLY #' + (index + 1);
    });
}

function renumberItems(tbody){
    if(!tbody) return;
    [...tbody.rows].forEach((row, index)=>{
        row.cells[0].innerText = index + 1;
    });
}

function hitungItem(row){
    if(!row) return;
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const unit = parseFloat(row.querySelector('.unit_value').value) || 0;
    row.querySelector('.total_value').value = (qty * unit).toFixed(2);
    hitungSemua();
}

function aktifkanEvent(){
    document.querySelectorAll('.qty, .unit_value').forEach(el => {
        el.oninput = () => hitungItem(el.closest('tr'));
    });
}

document.getElementById('receiver_id').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    if (!option.value) {
        document.getElementById('receiver_company').value = '';
        document.getElementById('receiver_phone').value = '';
        document.getElementById('receiver_email').value = '';
        document.getElementById('receiver_city').value = '';
        document.getElementById('receiver_postal_code').value = '';
        document.getElementById('receiver_address').value = '';
        document.getElementById('receiver_identity').value = '';
        document.getElementById('receiver_country').value = '';
        document.getElementById('country_id').value = '';
        return;
    }

    document.getElementById("receiver_company").value = option.dataset.company || "";
    document.getElementById("receiver_phone").value = option.dataset.phone || "";
    document.getElementById("receiver_email").value = option.dataset.email || "";
    document.getElementById("receiver_city").value = option.dataset.city || "";
    document.getElementById("receiver_postal_code").value = option.dataset.postal || "";
    document.getElementById("receiver_address").value = option.dataset.address || "";
    document.getElementById("receiver_identity").value = option.dataset.identity || "";
    document.getElementById("receiver_country").value = option.dataset.country || "";
    document.getElementById("country_id").value = option.dataset.countryId || "";
});

document.getElementById('add-colly').addEventListener('click', function(){
    const container = document.getElementById('colly-container');
    const no = container.children.length + 1;
    
    const html = `
    <div class="card border-primary mb-3" id="colly-${no}">
        <div class="card-header">
            <strong>COLLY #${no}</strong>
            <div class="float-end">
                <button type="button" class="btn btn-sm btn-secondary me-1 duplicate-colly">📋 Duplicate</button>
                <button type="button" class="btn btn-sm btn-danger delete-colly">🗑 Delete</button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Weight (Kg)</label>
                   <input type="number" class="form-control berat" name="packages[${no-1}][actual_weight]" step="0.01" min="0" value="1.00" oninput="hitungVolume(this);">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Length (Cm)</label>
                    <input type="number" class="form-control panjang" name="packages[${no-1}][length_cm]" min="0" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Width (Cm)</label>
                    <input type="number" class="form-control lebar" name="packages[${no-1}][width_cm]" min="0" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Height (Cm)</label>
                    <input type="number" class="form-control tinggi" name="packages[${no-1}][height_cm]" min="0" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Volume Weight</label>
                    <input type="text" class="form-control volume" name="packages[${no-1}][volume_weight]" value="0.20" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Chargeable Weight</label>
                    <input type="text" class="form-control chargeable" name="packages[${no-1}][chargeable_weight]" value="1.00" readonly>
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
                        <td><input type="text" name="packages[${no-1}][items][0][item_description]" class="form-control" placeholder="Item Description"></td>
                        <td><input type="number" name="packages[${no-1}][items][0][quantity]" class="form-control qty" value="1" min="1"></td>
                        <td>
                            <select name="packages[${no-1}][items][0][currency]" class="form-select currency">
                                <option value="IDR" selected>IDR</option>
                                <option value="USD">USD</option>
                                <option value="SGD">SGD</option>
                                <option value="MYR">MYR</option>
                                <option value="TWD">TWD</option>
                            </select>
                        </td>
                        <td><input type="number" name="packages[${no-1}][items][0][unit_value]" class="form-control unit_value" step="0.01" value="0.00"></td>
                        <td><input type="number" name="packages[${no-1}][items][0][total_value]" class="form-control total_value" value="0.00" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', html);
    aktifkanEvent();
    hitungSemua();
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('delete-colly')){
        const container = document.getElementById('colly-container');
        if (container.children.length > 1) {
            e.target.closest('.card.border-primary').remove();
            renumberColly();
            hitungSemua();
        }
    }
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('duplicate-colly')){
        const current = e.target.closest('.card.border-primary');
        const clone = current.cloneNode(true);        
        const newIndex = document.querySelectorAll('.card.border-primary').length;
        
        clone.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/packages\[\d+\]/g, `packages[${newIndex}]`);
        });

        clone.querySelector('.berat').value = '0';
        clone.querySelector('.panjang').value = '0';
        clone.querySelector('.lebar').value = '0';
        clone.querySelector('.tinggi').value = '0';
        clone.querySelector('.volume').value = '0';
        clone.querySelector('.chargeable').value = '0';

        current.after(clone);
        renumberColly();
        aktifkanEvent();
        hitungSemua();
    }
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('add-item')){
        const tbody = e.target.closest('.card-body').querySelector('tbody');
        const no = tbody.rows.length + 1;
        const indexColly = e.target.closest('.card.border-primary').id.split('-')[1] - 1;
        const itemKey = Date.now();

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">${no}</td>
            <td><input type="text" class="form-control" name="packages[${indexColly}][items][${itemKey}][item_description]"></td>
            <td><input type="number" class="form-control qty" name="packages[${indexColly}][items][${itemKey}][quantity]" value="1" min="1"></td>
            <td>
                <select class="form-select currency" name="packages[${indexColly}][items][${itemKey}][currency]">
                    <option value="IDR" selected>IDR</option>
                    <option value="USD">USD</option>
                    <option value="SGD">SGD</option>
                    <option value="MYR">MYR</option>
                    <option value="TWD">TWD</option>
                </select>
            </td>
            <td><input type="number" class="form-control unit_value" name="packages[${indexColly}][items][${itemKey}][unit_value]" step="0.01" value="0.00"></td>
            <td><input type="number" class="form-control total_value" name="packages[${indexColly}][items][${itemKey}][total_value]" value="0.00" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
        `;
        tbody.appendChild(row);
        aktifkanEvent();
        hitungSemua();
    }
});

document.addEventListener('click', function(e){
    if(e.target.classList.contains('delete-item')){
        const tbody = e.target.closest('tbody');
        if(tbody.rows.length > 1){
            e.target.closest('tr').remove();
            renumberItems(tbody);
            hitungSemua();
        }
    }
});

window.userRole = 'admin';

function refreshChargesUI() {
    const selectedEngine = $('#service_id option:selected').data('engine') || 'REGULAR';
    $('#expressCharges').toggle(selectedEngine === 'EXPRESS');
}

$(document).ready(function(){
    aktifkanEvent();
    hitungSemua();
    refreshChargesUI();

    // 1. Mengaktifkan Tombol Buka Modal
    $('#btnAddCustomer').on('click', function() {
        $('#senderModal').modal('show');
    });

    // 2. Proses Simpan SENDER via AJAX (Kembali ke ID HTML Asli)
    $('#btnSaveSender').off('click').on('click', function() {
        const senderData = {
            address_type: 'SENDER',
            country_id: 1, // Default Indonesia
            contact_name: $('#new_sender_name').val(), // Dikembalikan ke id asli HTML kamu
            company_name: $('#new_sender_company').val(),
            phone: $('#new_sender_phone').val(), // pastikan ID di HTML input telepon adalah new_sender_phone
            identity_number: $('#new_sender_identity').val(),
            province: $('#new_sender_province').val(), 
            city: $('#new_sender_city').val(),
            postal_code: $('#new_sender_postal').val(),
            email: $('#new_sender_email').val(),
            address: $('#new_sender_address').val()
        };

        // Validasi data wajib
        if (!senderData.contact_name || !senderData.phone) {
            alert('Customer Name and Phone are required!');
            return;
        }

        $.ajax({
            url: BASE_URL + '/customer/address/save',
            type: 'POST',
            data: senderData,
            dataType: 'json',
            success: function(response) {
               if (response.status === 'success') {
                    alert('Sender address saved successfully!');
                    
                    // 1. Buat option baru
                    const newOption = new Option(response.data.text, response.data.id, true, true);
                    
                    // 2. Suntik data lengkap ke dalam elemen option sebagai data-attribute
                    $(newOption).attr({
                        'data-company': response.address_details.company_name || '',
                        'data-phone': response.address_details.phone || '',
                        'data-email': response.address_details.email || '',
                        'data-city': response.address_details.city || '',
                        'data-state': response.address_details.state || '',
                        'data-postal': response.address_details.postal_code || '',
                        'data-address': response.address_details.address || '',
                        'data-identity': response.address_details.identity_number || ''
                    });
                    
                    
                    // 3. Masukkan ke dropdown utama dan paksa pilih id-nya
                    // Kita sertakan data objek aslinya langsung ke dalam trigger event-nya
                    $('#customer_id').append(newOption).val(response.data.id).trigger({
                        type: 'change',
                        params: {
                            data: response.address_details
                        }
                    });
                    
                    // 4. PAKSA ISI FORM UTAMA SECARA LIVE (Diberi delay tipis agar tidak ditimpa script bawaan)
                    setTimeout(function() {
                        $('input[name="sender_company"], #sender_company, #customer_company').val(response.address_details.company_name || '');
                        $('input[name="sender_phone"], #sender_phone, #customer_phone').val(response.address_details.phone || '');
                        $('input[name="sender_email"], #sender_email, #customer_email').val(response.address_details.email || '');
                        $('textarea[name="sender_address"], #sender_address, #customer_address').val(response.address_details.address || '');
                        $('input[name="sender_city"], #sender_city, #customer_city').val(response.address_details.city || '');
                        $('input[name="receiver_province"], #receiver_province').val(response.address_details.province || '');
                        $('input[name="sender_postal"], #sender_postal, #customer_postal').val(response.address_details.postal_code || '');
                        $('input[name="sender_identity"], #sender_identity, #customer_identity').val(response.address_details.identity_number || '');
                    }, 200);

                    // 5. Tutup modal & bersihkan form modal
                    $('#senderModal').modal('hide');
                    $('#senderModal input, #senderModal textarea').val('');
                
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error AJAX Sender: ' + xhr.status + ' | ' + error);
            }
        });
    });

/// ==========================================
// 3. PROSES SIMPAN RECEIVER VIA AJAX (SUPER SAFE & ANTI ANTENG - OPTIMIZED)
// ==========================================
$('#btnSaveReceiver, #receiverModal .btn-primary, #receiverModal button[type="submit"]').off('click').on('click', function(e) {
    e.preventDefault(); 
    
    const $btn = $(this); // Amankan context tombol
    
    // Cegah double submit jika tombol sedang loading/disabled
    if ($btn.prop('disabled')) return;

    console.log("Tombol Receiver berhasil diklik!"); 

    const receiverData = {
        address_type: 'RECEIVER',
        country_id: 1, 
        contact_name: $('#new_receiver_name').val() || $('#receiverModal input[type="text"]').first().val(), 
        company_name: $('#new_receiver_company').val(),
        phone: $('#new_receiver_phone').val(), 
        identity_number: $('#new_receiver_identity').val(),
        province: $('#new_receiver_province').val(), 
        city: $('#new_receiver_city').val(),
        postal_code: $('#new_receiver_postal').val(),
        email: $('#new_receiver_email').val(),
        address: $('#new_receiver_address').val()
    };

    if (!receiverData.contact_name) {
        alert('Receiver Name is required!');
        return;
    }
    if (!receiverData.phone) {
        alert('Receiver Phone Number is required!');
        return;
    }

    // Kunci tombol biar user ga klik brutal pas internet lemot
    $btn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: BASE_URL + '/customer/address/save', 
        type: 'POST',
        data: receiverData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert('Receiver address saved successfully!');
                
                // PENTING: Amankan fallback jika backend membungkus detailnya di objek 'data' atau 'address_details'
                const details = response.address_details || response.data || {};
                const resData = response.data || {};
                
                // Bikin option baru dengan jQuery agar attribute nempel sempurna
                const $newOption = $('<option>', {
                    value: resData.id,
                    text: resData.text || receiverData.contact_name,
                    selected: true
                }).attr({
                    'data-company': details.company_name || '',
                    'data-phone': details.phone || '',
                    'data-email': details.email || '',
                    'data-city': details.city || '',
                    'data-state': details.state || '',
                    'data-postal': details.postal_code || '',
                    'data-address': details.address || '',
                    'data-identity': details.identity_number || ''
                });
                
                // Masukkan ke dropdown Receiver & trigger change untuk Select2
                $('#receiver_id').append($newOption).trigger({
                    type: 'change',
                    params: { data: details }
                });
                
                // Paksa isi form utama seksi Receiver
                setTimeout(function() {
                    $('input[name="receiver_company"], #receiver_company').val(details.company_name || '');
                    $('input[name="receiver_phone"], #receiver_phone').val(details.phone || '');
                    $('input[name="receiver_email"], #receiver_email').val(details.email || '');
                    $('textarea[name="receiver_address"], #receiver_address').val(details.address || '');
                    $('input[name="receiver_city"], #receiver_city').val(details.city || '');
                    $('input[name="receiver_state"], #receiver_state, #receiver_province').val(details.state || '');
                    $('input[name="receiver_postal"], #receiver_postal').val(details.postal_code || '');
                    $('input[name="receiver_identity"], #receiver_identity').val(details.identity_number || '');
                }, 200);

                $('#receiverModal').modal('hide');
                $('#receiverModal input, #receiverModal textarea').val('');
            } else {
                alert('Gagal: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            alert('Error AJAX Receiver: ' + xhr.status);
        },
        complete: function() {
            // Buka kembali kunci tombol setelah proses selesai (baik sukses/gagal)
            $btn.prop('disabled', false).text('Save');
        }
    });
});
                    
}); // TUTUP $(document).ready

// Event Listener di luar ready
$(document).off('change', '#service_id, #is_remote').on('change', '#service_id, #is_remote', refreshChargesUI);

</script>





<script>
document.addEventListener("DOMContentLoaded", function () {
    const customerSelect = document.getElementById("customer_id");

    if (!customerSelect) {
        console.log("customer_id tidak ditemukan");
        return;
    }

    console.log("CUSTOMER AUTOFILL READY");

    customerSelect.addEventListener("change", function () {
        const option = this.options[this.selectedIndex];

        document.getElementById("sender_company").value = option.dataset.company || "";
        document.getElementById("sender_phone").value = option.dataset.phone || "";
        document.getElementById("sender_email").value = option.dataset.email || "";
        document.getElementById("sender_city").value = option.dataset.city || "";
        document.getElementById("sender_postal_code").value = option.dataset.postal || "";
        document.getElementById("sender_address").value = option.dataset.address || "";
        document.getElementById("sender_identity").value = option.dataset.identity || "";
        document.getElementById("sender_country").value = option.dataset.country || "";
        document.getElementById("sender_province").value = option.dataset.state || "";

        console.log("SENDER AUTOFILLED:", option.dataset);
    });
});
</script>





</body>
</html>