/*
====================================================
CARTENZ ERP
Shipment Colly Module ONLY
Sprint 49.6
====================================================
*/

window.ShipmentColly = {

    initialized: false,

    init() {
        if (this.initialized) return;
        this.initialized = true;

        this.unbindEvents();
        this.bindEvents();
        console.log("✅ Colly Module Loaded");
    },

    // Hapus event lama
    unbindEvents() {
        $(document)
            .off('click.addColly')
            .off('click.deleteColly')
            .off('click.duplicateColly');
    },

    // Hitung Volume & Berat Dapat Digunakan
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

        if (typeof hitungSemua === "function") hitungSemua();
    },

    // Penomoran Ulang Colly
    renumberColly() {
        document.querySelectorAll('#colly-container .card.border-primary').forEach((card, index) => {
            const nomor = index + 1;
            card.id = `colly-${nomor}`;
            const label = card.querySelector('strong');
            if (label) label.innerText = `COLLY #${nomor}`;

            // Perbarui indeks input
            card.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/^packages\[\d+\]/, `packages[${index}]`);
            });
        });
    },

    // Pasang Event Hanya untuk Colly
    bindEvents() {
        const self = this;

        // Tambah Colly Baru
        $(document).on('click.addColly', '#add-colly', function () {
            const container = document.getElementById('colly-container');
            const no = container.children.length + 1;
            const index = no - 1;

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
                            <input type="number" class="form-control berat" name="packages[${index}][actual_weight]" step="0.01" min="0" value="0" oninput="ShipmentColly.hitungVolume(this);">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Length (Cm)</label>
                            <input type="number" class="form-control panjang" name="packages[${index}][length_cm]" min="0" value="0" oninput="ShipmentColly.hitungVolume(this)">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Width (Cm)</label>
                            <input type="number" class="form-control lebar" name="packages[${index}][width_cm]" min="0" value="0" oninput="ShipmentColly.hitungVolume(this)">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Height (Cm)</label>
                            <input type="number" class="form-control tinggi" name="packages[${index}][height_cm]" min="0" value="0" oninput="ShipmentColly.hitungVolume(this)">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Volume Weight</label>
                            <input type="text" class="form-control volume" name="packages[${index}][volume_weight]" value="0" readonly>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Chargeable Weight</label>
                            <input type="text" class="form-control chargeable" name="packages[${index}][chargeable_weight]" value="0" readonly>
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
                                <td><input type="text" class="form-control" name="packages[${index}][items][0][item_description]" placeholder="Item Description"></td>
                                <td><input type="number" class="form-control qty" name="packages[${index}][items][0][quantity]" value="1" min="1"></td>
                                <td>
                                    <select class="form-select currency" name="packages[${index}][items][0][currency]">
                                        <option value="USD" selected>USD</option>
                                        <option value="IDR">IDR</option>
                                        <option value="SGD">SGD</option>
                                        <option value="MYR">MYR</option>
                                        <option value="TWD">TWD</option>
                                    </select>
                                </td>
                                <td><input type="number" class="form-control unit_value" name="packages[${index}][items][0][unit_value]" step="0.01" value="0.00"></td>
                                <td><input type="number" class="form-control total_value" name="packages[${index}][items][0][total_value]" value="0.00" readonly></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', html);
            hitungSemua();
        });

        // Hapus Colly
        $(document).on('click.deleteColly', '.delete-colly', function () {
            const container = document.getElementById('colly-container');
            if (container.children.length > 1) {
                $(this).closest('.card.border-primary').remove();
                self.renumberColly();
                hitungSemua();
            } else {
                alert("Minimal harus ada 1 Colly.");
            }
        });

        // Duplikat Colly
        $(document).on('click.duplicateColly', '.duplicate-colly', function () {
            const current = $(this).closest('.card.border-primary');
            const clone = current[0].cloneNode(true, false);

            // Reset nilai
            clone.querySelector('.berat').value = '0';
            clone.querySelector('.panjang').value = '0';
            clone.querySelector('.lebar').value = '0';
            clone.querySelector('.tinggi').value = '0';
            clone.querySelector('.volume').value = '0';
            clone.querySelector('.chargeable').value = '0';

            // Reset semua item di dalamnya
            const tbody = clone.querySelector('tbody');
            tbody.innerHTML = `
                <tr>
                    <td class="text-center">1</td>
                    <td><input type="text" class="form-control" name=""></td>
                    <td><input type="number" class="form-control qty" value="1" min="1"></td>
                    <td>
                        <select class="form-select currency">
                            <option value="USD" selected>USD</option>
                            <option value="IDR">IDR</option>
                            <option value="SGD">SGD</option>
                            <option value="MYR">MYR</option>
                            <option value="TWD">TWD</option>
                        </select>
                    </td>
                    <td><input type="number" class="form-control unit_value" step="0.01" value="0.00"></td>
                    <td><input type="number" class="form-control total_value" value="0.00" readonly></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger delete-item">🗑</button></td>
                </tr>`;

            current.after(clone);
            self.renumberColly();
            hitungSemua();
        });
    }
};

// Ekspor fungsi
window.hitungVolume = function (el) {
    window.ShipmentColly.hitungVolume(el);
};

window.initializeColly = function () {
    window.ShipmentColly.init();
};