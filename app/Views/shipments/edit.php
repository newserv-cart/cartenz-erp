<!DOCTYPE html>
<html>
<head>
    <title>Create Shipment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<form method="post" action="<?= base_url('shipments/update/'.$shipment['id']) ?>">
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
                    <input type="text" class="form-control" placeholder="Search Sender">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Phone Number</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label>City</label>
                    <input type="text" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Postal Code</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-9 mb-3">
                    <label>Address</label>
                    <textarea class="form-control" rows="2"></textarea>
                </div>
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
                    <select class="form-select">
                        <option value="PERSONAL" selected>Personal</option>
                        <option value="COMPANY">Company</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Receiver Name</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Phone Number</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Identity Number</label>
                    <input type="text" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label>Country</label>
                    <select class="form-select"></select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Postal Code</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label>City</label>
                    <input type="text" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label>Address</label>
                    <textarea class="form-control" rows="3" placeholder="Complete address, apartment, building, hotel, floor, room number (if any)"></textarea>
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
                                <input type="number"
       class="form-control berat"
       name="packages[0][actual_weight]"
       value="<?= $packages[0]['actual_weight'] ?>"
       step="0.01"
       min="0"
       oninput="hitungSemua()">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Length (Cm)</label>
                                <input type="number" class="form-control panjang" name="packages[0][length_cm]" value="<?= $packages[0]['length_cm'] ?>" min="0" value="0" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Width (Cm)</label>
                                <input type="number" class="form-control lebar" name="packages[0][width_cm]" value="<?= $packages[0]['width_cm'] ?>" min="0" value="0" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Height (Cm)</label>
                                <input type="number" class="form-control tinggi" name="packages[0][height_cm]" value="<?= $packages[0]['height_cm'] ?>" min="0" value="0" oninput="hitungVolume(this)">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Volume Weight</label>
                                <input type="text" class="form-control volume" name="packages[0][volume_weight]" value="<?= $packages[0]['volume_weight'] ?>" value="0.00" readonly>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Chargeable Weight</label>
                                <input type="text" class="form-control chargeable" name="packages[0][chargeable_weight]" value="<?= $packages[0]['chargeable_weight'] ?>" value="0.00" readonly>
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

                                <td>
                                    <input type="text" 
                                    class="form-control" 
                                    name="packages[0][items][0][item_description]" 
                                    placeholder="Item Description"
                                    value="<?= $packages[0]['items'][0]['item_description'] ?>"
                                </td>

                                    <td>
                                    <input type="number" 
                                    class="form-control qty" 
                                    name="packages[0][items][0][quantity]"
                                    value="<?= $packages[0]['items'][0]['quantity'] ?>"
                                    value="1" min="1">
                                </td>

                                    <td>
                                        <select class="form-select currency" name="packages[0][items][0][currency]">
                                            <option>USD</option>
                                            <option>SGD</option>
                                            <option>MYR</option>
                                            <option>TWD</option>
                                            <option>IDR</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" 
                                        name="packages[0][items][0][unit_value]" 
                                        class="form-control unit_value" step="0.01" 
                                        value="<?= $packages[0]['items'][0]['unit_value'] ?>"></td>
                                    <td>
                                        <input type="number" 
                                        class="form-control total_value" 
                                        name="packages[0][items][0][total_value]" 
                                        value="<?= $packages[0]['items'][0]['total_value'] ?>" readonly></td>
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
                    <input type="text" 
                    class="form-control" 
                    id="total_colly" name="total_colly" 
                    value="1" readonly>
                </div>

                <div class="col-md-3 mb-3">
                    <label>Total Weight</label>
                    <input type="text" 
                    class="form-control" 
                    id="total_berat" 
                    name="total_weight" 
                    value="<?= $shipment['actual_weight'] ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Declared Value</label>
                    <input type="text" 
                    class="form-control" 
                    id="total_nilai" 
                    name="total_value" 
                    value="<?= $shipment['declared_value'] ?>" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Total Chargeable Weight</label>
                    <input type="text" class="form-control" id="total_chargeable" name="total_chargeable" value="<?= $shipment['chargeable_weight'] ?>" readonly>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-2 mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2">💾 Save Shipment</button>
                <button type="submit" class="btn btn-success px-4 py-2">🖨️ Save & Print</button>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
console.log('SCRIPT LOADED');

// ✅ HITUNG VOLUME - SUDAH AMAN & BENAR
function hitungVolume(el) {
    console.log('HITUNG VOLUME JALAN');

    let card = el.closest('.card.border-primary');
    if (!card) return; // Cegah error kalau tidak ketemu

    let row = card.querySelector('.row');
    if (!row) return;

    let p = parseFloat(row.querySelector('.panjang').value) || 0;
    let l = parseFloat(row.querySelector('.lebar').value) || 0;
    let t = parseFloat(row.querySelector('.tinggi').value) || 0;
    let berat = parseFloat(row.querySelector('.berat').value) || 0;
    
    // Rumus dibagi 5000 sesuai keinginan kamu
    let volume = (p * l * t) / 5000;
    if(row.querySelector('.volume')) row.querySelector('.volume').value = volume.toFixed(2);
    
    // Ambil nilai terbesar
    let charge = Math.max(berat, volume);
    if(row.querySelector('.chargeable')) row.querySelector('.chargeable').value = charge.toFixed(2);
    
    hitungSemua();
}

// ✅ HITUNG TOTAL KESELURUHAN
function hitungSemua(){
    let semuaBerat = document.querySelectorAll('.berat');
    let semuaCharge = document.querySelectorAll('.chargeable');
    let semuaNilai = document.querySelectorAll('.total_value');
    
    let totalBerat = 0;
    let totalCharge = 0;
    let totalNilai = 0;

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
    document.getElementById('total_colly').value = document.querySelectorAll('#colly-container .card.border-primary').length;
}

// ✅ URUTKAN NOMOR BARANG (HANYA 1 FUNGSI, TIDAK GANDA)
function renumberItems(tbody){
    if(!tbody) return;
    [...tbody.rows].forEach((row, index)=>{
        row.cells[0].innerText = index + 1;
    });
}

// ✅ HITUNG NILAI BARANG
function hitungItem(row){
    if(!row) return;
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let unit = parseFloat(row.querySelector('.unit_value').value) || 0;

    if(row.querySelector('.total_value')){
        row.querySelector('.total_value').value = (qty * unit).toFixed(2);
    }
    hitungSemua();
}

// ✅ AKTIFKAN EVENT INPUT
function aktifkanEvent(){
    document.querySelectorAll('.qty, .unit_value').forEach(el => {
        el.oninput = function(){
            hitungItem(this.closest('tr'));
        };
    });
}

// ✅ TAMBAH COLLY BARU
document.getElementById('add-colly').addEventListener('click', function(){
    let container = document.getElementById('colly-container');
    let no = container.children.length + 1;
    
    let html = `
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
                    <input type="number" class="form-control berat" name="packages[${no-1}][actual_weight]" step="0.01" min="0.1" value="1.00" oninput="hitungSemua()">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Length (Cm)</label>
                    <input type="number" class="form-control panjang" name="packages[${no-1}][length_cm]" min="1" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Width (Cm)</label>
                    <input type="number" class="form-control lebar" name="packages[${no-1}][width_cm]" min="1" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Height (Cm)</label>
                    <input type="number" class="form-control tinggi" name="packages[${no-1}][height_cm]" min="1" value="10" oninput="hitungVolume(this)">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Volume Weight</label>
                    <input type="text" class="form-control volume" name="packages[${no-1}][volume_weight]" value="0.00" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Chargeable Weight</label>
                    <input type="text" class="form-control chargeable" name="packages[${no-1}][chargeable_weight]" value="0.00" readonly>
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
                        <td><input type="text" name="packages[${no-1}][items][0][item_description]" value="<?= $packages[0]['items'][0]['item_description'] ?>" class="form-control" placeholder="Item Description"></td>
                        <td><input type="number" name="packages[${no-1}][items][0][quantity]" value="<?= $packages[0]['items'][0]['quantity'] ?>" class="form-control qty" value="1" min="1"></td>
                        <td>
                            <select name="packages[${no-1}][items][0][currency]" class="form-select currency">
                                <option>USD</option>
                                <option>SGD</option>
                                <option>MYR</option>
                                <option>TWD</option>
                                <option>IDR</option>
                            </select>
                        </td>
                        <td><input type="number" name="packages[${no-1}][items][0][unit_value]" value="<?= $packages[0]['items'][0]['unit_value'] ?>" class="form-control unit_value" step="0.01" value="0.00"></td>
                        <td><input type="number" name="packages[${no-1}][items][0][total_value]" value="<?= $packages[0]['items'][0]['total_value'] ?>" class="form-control total_value" value="0.00" readonly></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>`;
    
    container.insertAdjacentHTML('beforeend', html);
    aktifkanEvent(); // ✅ Aktifkan event di elemen baru
    hitungSemua();
});

// ✅ HAPUS COLLY
document.addEventListener('click', function(e){
    if(e.target.classList.contains('delete-colly')){
        e.target.closest('.card.border-primary').remove();
        renumberColly();
        hitungSemua();
    }
});

// ✅ DUPLIKAT COLLY
document.addEventListener('click', function(e){
    if(e.target.classList.contains('duplicate-colly')){
        let currentColly = e.target.closest('.card.border-primary');
        let clone = currentColly.cloneNode(true);        
        let newIndex = document.querySelectorAll('.card.border-primary').length;
        
        // Ubah nama index biar unik
        clone.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replace(/packages\[\d+\]/g, `packages[${newIndex}]`);
        });

        /// reset dimensi & berat
clone.querySelector('.berat').value = '0';

clone.querySelector('.panjang').value = '0';
clone.querySelector('.lebar').value   = '0';
clone.querySelector('.tinggi').value  = '0';

clone.querySelector('.volume').value = '0.00';
clone.querySelector('.chargeable').value = '0.00';

        currentColly.after(clone);
        renumberColly();
        aktifkanEvent(); // ✅ Aktifkan event di duplikat
        hitungSemua();
    }
});

// ✅ TAMBAH BARANG - SUDAH BERFUNGSI
document.addEventListener('click', function(e){
    if(e.target.classList.contains('add-item')){
        let tbody = e.target.closest('.card-body').querySelector('tbody');
        let no = tbody.rows.length + 1;
        let parentCard = e.target.closest('.card.border-primary');
        let indexColly = parentCard.id.split('-')[1] - 1;
        let itemIndex = Date.now(); // ID unik

        let baris = document.createElement('tr');
        baris.innerHTML = `
            <td class="text-center">${no}</td>
            <td><input type="text" 
            class="form-control" 
            name="packages[${indexColly}][items][${itemIndex}][item_description]">
            </td>


            <td>
            <input type="number" 
            class="form-control qty" 
            name="packages[${indexColly}][items][${itemIndex}][quantity]" value="1" min="1">
            </td>

            <td>
                <select class="form-select currency" name="packages[${indexColly}][items][${itemIndex}][currency]">
                    <option>USD</option><option>SGD</option><option>MYR</option><option>TWD</option><option>IDR</option>
                </select>
            </td>
            <td><input type="number" 
            class="form-control unit_value"
            name="packages[${indexColly}][items][${itemIndex}][unit_value]" step="0.01" value="0.00">
            </td>

            <td><input type="number" 
            class="form-control total_value" 
            name="packages[${indexColly}][items][${itemIndex}][total_value]" value="0.00" readonly>
            </td>
            
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
        `;
        tbody.appendChild(baris);
        aktifkanEvent(); // ✅ Aktifkan hitung di baris baru
        hitungSemua();
    }
});

// ✅ HAPUS BARANG + URUT ULANG NOMOR
document.addEventListener('click', function(e){
    if(e.target.classList.contains('delete-item')){
        let tbody = e.target.closest('tbody');
        if(tbody.rows.length > 1){ // Jangan hapus kalau cuma satu
            e.target.closest('tr').remove();
            renumberItems(tbody); // ✅ Nomor urut ulang
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