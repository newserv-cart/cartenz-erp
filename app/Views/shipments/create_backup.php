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

    <!-- Sender Information -->
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">Sender Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Sender Name</label>
                    <input type="text" class="form-control" name="sender_name" placeholder="Enter sender name" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" name="sender_phone" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>City</label>
                    <input type="text" class="form-control" name="sender_city">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Postal Code</label>
                    <input type="text" class="form-control" name="sender_postal_code">
                </div>
                <div class="col-md-9 mb-3">
                    <label>Address</label>
                    <textarea class="form-control" name="sender_address" rows="2" required></textarea>
                </div>
            </div>
        </div>
    </div>


    <!-- Receiver Address Book -->
    <div class="card mb-3">
        <div class="card-header bg-light">Receiver Address Book</div>
        <div class="card-body">
            <div class="mb-0">
                <label for="receiver_address" class="form-label">Select Saved Address</label>
                <select id="receiver_address" class="form-select">
                    <option value="">-- Select Address --</option>
                    <?php if (!empty($addresses)): ?>
                        <?php foreach ($addresses as $address): ?>
                            <option 
                                value="<?= esc($address['id']) ?>"
                                data-name="<?= esc($address['contact_name']) ?>"
                                data-company="<?= esc($address['company_name'] ?? '') ?>"
                                data-phone="<?= esc($address['phone']) ?>"
                                data-email="<?= esc($address['email'] ?? '') ?>"
                                data-address="<?= esc($address['address']) ?>"
                                data-city="<?= esc($address['city'] ?? '') ?>"
                                data-postal="<?= esc($address['postal_code'] ?? '') ?>"
                                data-country="<?= esc($address['country'] ?? 'ID') ?>"
                            >
                                <?= esc($address['address_label']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No addresses found</option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>


    <!-- Receiver Information -->
    <div class="card mb-3">
        <div class="card-header bg-success text-white">Receiver Information</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Receiver Type</label>
                    <select class="form-select" id="receiver_type" name="receiver_type">
                        <option value="PERSONAL" selected>Personal</option>
                        <option value="COMPANY">Company</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Receiver Name</label>
                    <input type="text" id="receiver_name" name="receiver_name" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Company</label>
                    <input type="text" id="receiver_company" name="receiver_company" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Phone Number</label>
                    <input type="text" id="receiver_phone" name="receiver_phone" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Identity Number</label>
                    <input type="text" id="identity_number" name="receiver_id_number" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Country</label>
                    <select class="form-select" id="receiver_country" name="receiver_country">
                        <option value="ID" selected>Indonesia</option>
                        <option value="SG">Singapore</option>
                        <option value="MY">Malaysia</option>
                        <option value="US">United States</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Postal Code</label>
                    <input type="text" id="postal_code" name="receiver_postal_code" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>City</label>
                    <input type="text" id="city" name="receiver_city" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label>Email</label>
                    <input type="email" id="receiver_email" name="receiver_email" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label>Complete Address</label>
                    <textarea 
                        id="receiver_address_text" 
                        name="receiver_address" 
                        class="form-control" 
                        rows="3" 
                        placeholder="Street name, building, floor, room, landmark..."
                        required
                    ></textarea>
                </div>
                <div class="col-md-12">
                    <button type="button" class="btn btn-sm btn-outline-secondary">+ Add Email</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Package / Colly Information -->
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
                                <input type="number" class="form-control berat" name="packages[0][actual_weight]" step="0.01" min="0" value="1.00" oninput="hitungSemua()">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Length (Cm)</label>
                                <input type="number" class="form-control panjang" name="packages[0][length_cm]" min="0" value="10" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Width (Cm)</label>
                                <input type="number" class="form-control lebar" name="packages[0][width_cm]" min="0" value="10" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Height (Cm)</label>
                                <input type="number" class="form-control tinggi" name="packages[0][height_cm]" min="0" value="10" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Volume Weight</label>
                                <input type="text" class="form-control volume" name="packages[0][volume_weight]" value="0.20" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Chargeable Weight</label>
                                <input type="text" class="form-control chargeable" name="packages[0][chargeable_weight]" value="1.00" readonly>
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
                                            <option value="IDR" selected>IDR</option>
                                            <option value="USD">USD</option>
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


    <!-- Shipment Summary -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
console.log('SCRIPT LOADED');

// ✅ HITUNG VOLUME & CHARGEABLE WEIGHT
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

// ✅ HITUNG TOTAL KESELURUHAN
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
}

// ✅ URUTKAN NOMOR COLLY
function renumberColly(){
    document.querySelectorAll('#colly-container .card.border-primary').forEach((card, index)=>{
        card.id = 'colly-' + (index + 1);
        card.querySelector('strong').innerText = 'COLLY #' + (index + 1);
    });
}

// ✅ URUTKAN NOMOR BARANG
function renumberItems(tbody){
    if(!tbody) return;
    [...tbody.rows].forEach((row, index)=>{
        row.cells[0].innerText = index + 1;
    });
}

// ✅ HITUNG NILAI BARANG
function hitungItem(row){
    if(!row) return;
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const unit = parseFloat(row.querySelector('.unit_value').value) || 0;
    row.querySelector('.total_value').value = (qty * unit).toFixed(2);
    hitungSemua();
}

// ✅ AKTIFKAN EVENT INPUT
function aktifkanEvent(){
    document.querySelectorAll('.qty, .unit_value').forEach(el => {
        el.oninput = () => hitungItem(el.closest('tr'));
    });
}

// ✅ ISI OTOMATIS ALAMAT PENERIMA
document.getElementById('receiver_address').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    if (!option.value) {
        document.getElementById('receiver_name').value = '';
        document.getElementById('receiver_company').value = '';
        document.getElementById('receiver_phone').value = '';
        document.getElementById('receiver_email').value = '';
        document.getElementById('city').value = '';
        document.getElementById('postal_code').value = '';
        document.getElementById('receiver_address_text').value = '';
        document.getElementById('identity_number').value = '';
        document.getElementById('receiver_country').value = 'ID';
        return;
    }

    document.getElementById('receiver_name').value = option.dataset.name || '';
    document.getElementById('receiver_company').value = option.dataset.company || '';
    document.getElementById('receiver_phone').value = option.dataset.phone || '';
    document.getElementById('receiver_email').value = option.dataset.email || '';
    document.getElementById('city').value = option.dataset.city || '';
    document.getElementById('postal_code').value = option.dataset.postal || '';
    document.getElementById('receiver_address_text').value = option.dataset.address || '';
    document.getElementById('receiver_country').value = option.dataset.country || 'ID';
});

// ✅ TAMBAH COLLY BARU
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
                    <input type="number" class="form-control berat" name="packages[${no-1}][actual_weight]" step="0.01" min="0" value="1.00" oninput="hitungSemua()">
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

// ✅ HAPUS COLLY
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

// ✅ DUPLIKAT COLLY
document.addEventListener('click', function(e){
    if(e.target.classList.contains('duplicate-colly')){
        const current = e.target.closest('.card.border-primary');
        const clone = current.cloneNode(true);        
        const newIndex = document.querySelectorAll('.card.border-primary').length;
        
        clone.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/packages\[\d+\]/g, `packages[${newIndex}]`);
        });

        clone.querySelector('.berat').value = '1.00';
        clone.querySelector('.panjang').value = '10';
        clone.querySelector('.lebar').value = '10';
        clone.querySelector('.tinggi').value = '10';
        clone.querySelector('.volume').value = '0.20';
        clone.querySelector('.chargeable').value = '1.00';

        current.after(clone);
        renumberColly();
        aktifkanEvent();
        hitungSemua();
    }
});

// ✅ TAMBAH BARANG
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

// ✅ HAPUS BARANG
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

// Jalankan awal
aktifkanEvent();
hitungSemua();
</script>

</form>

</body>
</html>