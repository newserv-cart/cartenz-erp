/*
====================================================
CARTENZ ERP
Shipment Colly & Items Module
Sprint 49.4
====================================================
*/

window.ShipmentColly = {

    initialized: false,

    init() {
        if (this.initialized) return;
        this.initialized = true;

        this.bindEvents();
        console.log("✅ Colly Module Loaded");
    },

    // ==================================================
    // Hitung Volume & Berat Dapat Digunakan
    // ==================================================
    hitungVolume(el) {
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

        if (typeof hitungSemua === "function") {
            hitungSemua();
        }
    },

    // ==================================================
    // Hitung Total Nilai per Item
    // ==================================================
    hitungItem(row) {
        if (!row) return;
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const unit = parseFloat(row.querySelector('.unit_value').value) || 0;
        row.querySelector('.total_value').value = (qty * unit).toFixed(2);

        if (typeof hitungSemua === "function") {
            hitungSemua();
        }
    },

    // ==================================================
    // Penomoran Ulang Colly
    // ==================================================
    renumberColly() {
        document.querySelectorAll('#colly-container .card.border-primary').forEach((card, index) => {
            card.id = 'colly-' + (index + 1);
            card.querySelector('strong').innerText = 'COLLY #' + (index + 1);
        });
    },

    // ==================================================
    // Penomoran Ulang Baris Item
    // ==================================================
    renumberItems(tbody) {
        if (!tbody) return;
        [...tbody.rows].forEach((row, index) => {
            row.cells[0].innerText = index + 1;
        });
    },

    // ==================================================
    // Aktifkan Event Input pada Item
    // ==================================================
    aktifkanEvent() {
        document.querySelectorAll('.qty, .unit_value').forEach(el => {
            el.oninput = () => this.hitungItem(el.closest('tr'));
        });
    },

    // ==================================================
    // Daftar Semua Event
    // ==================================================
    bindEvents() {
        const self = this;

        // Tambah Colly Baru
        document.getElementById('add-colly')?.addEventListener('click', function () {
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
                            <input type="number" class="form-control berat" name="packages[${no-1}][actual_weight]" step="0.01" min="0" value="1.00" oninput="ShipmentColly.hitungVolume(this);">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Length (Cm)</label>
                            <input type="number" class="form-control panjang" name="packages[${no-1}][length_cm]" min="0" value="10" oninput="ShipmentColly.hitungVolume(this)">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Width (Cm)</label>
                            <input type="number" class="form-control lebar" name="packages[${no-1}][width_cm]" min="0" value="10" oninput="ShipmentColly.hitungVolume(this)">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Height (Cm)</label>
                            <input type="number" class="form-control tinggi" name="packages[${no-1}][height_cm]" min="0" value="10" oninput="ShipmentColly.hitungVolume(this)">
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
            self.aktifkanEvent();

            if (typeof hitungSemua === "function") {
                hitungSemua();
            }
        });

        // Hapus Colly
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('delete-colly')) {
                const container = document.getElementById('colly-container');
                if (container.children.length > 1) {
                    e.target.closest('.card.border-primary').remove();
                    self.renumberColly();

                    if (typeof hitungSemua === "function") {
                        hitungSemua();
                    }
                }
            }
        });

        // Duplikat Colly
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('duplicate-colly')) {
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
                self.renumberColly();
                self.aktifkanEvent();

                if (typeof hitungSemua === "function") {
                    hitungSemua();
                }
            }
        });

        // Tambah Item Baru
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-item')) {
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
                self.aktifkanEvent();

                if (typeof hitungSemua === "function") {
                    hitungSemua();
                }
            }
        });

        // Hapus Item
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('delete-item')) {
                const tbody = e.target.closest('tbody');
                if (tbody.rows.length > 1) {
                    e.target.closest('tr').remove();
                    self.renumberItems(tbody);

                    if (typeof hitungSemua === "function") {
                        hitungSemua();
                    }
                }
            }
        });
    }
};

// Ekspor fungsi global agar tetap kompatibel
window.hitungVolume = function (el) {
    window.ShipmentColly.hitungVolume(el);
};

window.hitungItem = function (row) {
    window.ShipmentColly.hitungItem(row);
};

window.renumberColly = function () {
    window.ShipmentColly.renumberColly();
};

window.aktifkanEvent = function () {
    window.ShipmentColly.aktifkanEvent();
};

window.initializeColly = function () {
    window.ShipmentColly.init();
};