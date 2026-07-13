/*
====================================================
CARTENZ ERP
Shipment Items Module
Sprint 49.6
====================================================
*/

(function (window, $) {
    "use strict";

    const ShipmentItems = {

        initialized: false,

        init() {
            if (this.initialized) {
                return;
            }

            this.initialized = true;

            this.bindEvents();
            this.renumberAll();
            this.calculateAll();

            console.log("✅ Items Module Loaded");
        },

        // ==================================================
        // SEMUA EVENT ITEMS
        // ==================================================
        bindEvents() {
            const self = this;

            /*
             * Qty atau Unit Value berubah.
             * Event delegation otomatis bekerja untuk
             * item lama maupun item yang baru ditambahkan.
             */
            $(document)
                .off(
                    "input.shipmentItems change.shipmentItems",
                    ".item-table .qty, .item-table .unit_value"
                )
                .on(
                    "input.shipmentItems change.shipmentItems",
                    ".item-table .qty, .item-table .unit_value",
                    function () {
                        const row = this.closest("tr");

                        self.calculateRow(row);
                    }
                );

            // ADD ITEM
            $(document)
                .off(
                    "click.shipmentItemsAdd",
                    ".add-item"
                )
                .on(
                    "click.shipmentItemsAdd",
                    ".add-item",
                    function (event) {
                        event.preventDefault();

                        self.addItem(this);
                    }
                );

            // DELETE ITEM
            $(document)
                .off(
                    "click.shipmentItemsDelete",
                    ".delete-item"
                )
                .on(
                    "click.shipmentItemsDelete",
                    ".delete-item",
                    function (event) {
                        event.preventDefault();

                        self.deleteItem(this);
                    }
                );
        },

        // ==================================================
        // HITUNG SATU BARIS
        // ==================================================
        calculateRow(row) {
            if (!row) {
                return;
            }

            const qtyElement =
                row.querySelector(".qty");

            const unitElement =
                row.querySelector(".unit_value");

            const totalElement =
                row.querySelector(".total_value");

            const qty =
                parseFloat(
                    qtyElement?.value || 0
                ) || 0;

            const unitValue =
                parseFloat(
                    unitElement?.value || 0
                ) || 0;

            const total =
                qty * unitValue;

            if (totalElement) {
                totalElement.value =
                    total.toFixed(2);
            }

            this.refreshSummary();
        },

        // ==================================================
        // HITUNG SEMUA ITEM
        // ==================================================
        calculateAll() {
            document
                .querySelectorAll(
                    ".item-table tbody tr"
                )
                .forEach((row) => {
                    const qty =
                        parseFloat(
                            row.querySelector(".qty")
                                ?.value || 0
                        ) || 0;

                    const unitValue =
                        parseFloat(
                            row.querySelector(".unit_value")
                                ?.value || 0
                        ) || 0;

                    const totalElement =
                        row.querySelector(".total_value");

                    if (totalElement) {
                        totalElement.value =
                            (qty * unitValue).toFixed(2);
                    }
                });

            this.refreshSummary();
        },

        // ==================================================
        // ADD ITEM
        // ==================================================
        addItem(button) {
            const card =
                button.closest(
                    ".card.border-primary"
                );

            if (!card) {
                console.error(
                    "❌ Colly untuk Add Item tidak ditemukan"
                );
                return;
            }

            const tbody =
                card.querySelector(
                    ".item-table tbody"
                );

            if (!tbody) {
                console.error(
                    "❌ tbody item tidak ditemukan"
                );
                return;
            }

            const packageIndex =
                this.getPackageIndex(card);

            const itemIndex =
                tbody.querySelectorAll("tr").length;

            const row =
                document.createElement("tr");

            row.innerHTML =
                this.buildItemRow(
                    packageIndex,
                    itemIndex
                );

            tbody.appendChild(row);

            this.renumberItems(tbody);
            this.calculateRow(row);
        },

        // ==================================================
        // DELETE ITEM
        // ==================================================
        deleteItem(button) {
            const tbody =
                button.closest("tbody");

            if (!tbody) {
                return;
            }

            if (tbody.rows.length <= 1) {
                alert(
                    "Minimal harus ada 1 item dalam setiap colly."
                );
                return;
            }

            const row =
                button.closest("tr");

            if (!row) {
                return;
            }

            row.remove();

            this.renumberItems(tbody);
            this.calculateAll();
        },

        // ==================================================
        // INDEX COLLY
        // ==================================================
        getPackageIndex(card) {
            const cards = [
                ...document.querySelectorAll(
                    "#colly-container .card.border-primary"
                )
            ];

            const index =
                cards.indexOf(card);

            return index >= 0 ? index : 0;
        },

        // ==================================================
        // RENUMBER ITEM
        // ==================================================
        renumberItems(tbody) {
            if (!tbody) {
                return;
            }

            const card =
                tbody.closest(
                    ".card.border-primary"
                );

            const packageIndex =
                this.getPackageIndex(card);

            [...tbody.rows].forEach(
                (row, itemIndex) => {
                    if (row.cells[0]) {
                        row.cells[0].textContent =
                            itemIndex + 1;
                    }

                    row
                        .querySelectorAll("[name]")
                        .forEach((element) => {
                            element.name =
                                element.name.replace(
                                    /packages\[\d+\]\[items\]\[[^\]]+\]/g,
                                    "packages[" +
                                    packageIndex +
                                    "][items][" +
                                    itemIndex +
                                    "]"
                                );
                        });
                }
            );
        },

        renumberAll() {
            document
                .querySelectorAll(
                    ".item-table tbody"
                )
                .forEach((tbody) => {
                    this.renumberItems(tbody);
                });
        },

        // ==================================================
        // REFRESH DECLARED VALUE SUMMARY
        // ==================================================
        refreshSummary() {
            if (
                window.ShipmentSummary &&
                typeof window.ShipmentSummary
                    .hitungSemua === "function"
            ) {
                window.ShipmentSummary
                    .hitungSemua();

                return;
            }

            if (
                typeof window.hitungSemua ===
                "function"
            ) {
                window.hitungSemua();
            }
        },

        // ==================================================
        // TEMPLATE ITEM BARU
        // ==================================================
        buildItemRow(packageIndex, itemIndex) {
            return `
                <td class="text-center">
                    ${itemIndex + 1}
                </td>

                <td>
                    <input
                        type="text"
                        class="form-control"
                        name="packages[${packageIndex}][items][${itemIndex}][item_description]"
                        placeholder="Item Description">
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control qty"
                        name="packages[${packageIndex}][items][${itemIndex}][quantity]"
                        value="1"
                        min="1">
                </td>

                <td>
                    <select
                        class="form-select currency"
                        name="packages[${packageIndex}][items][${itemIndex}][currency]">

                        <option value="USD" selected>
                            USD
                        </option>

                        <option value="IDR">
                            IDR
                        </option>

                        <option value="SGD">
                            SGD
                        </option>

                        <option value="MYR">
                            MYR
                        </option>

                        <option value="TWD">
                            TWD
                        </option>
                    </select>
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control unit_value"
                        name="packages[${packageIndex}][items][${itemIndex}][unit_value]"
                        step="0.01"
                        value="0.00">
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control total_value"
                        name="packages[${packageIndex}][items][${itemIndex}][total_value]"
                        value="0.00"
                        readonly>
                </td>

                <td class="text-center">
                    <button
                        type="button"
                        class="btn btn-sm btn-danger delete-item">
                        🗑
                    </button>
                </td>
            `;
        },

        /*
         * Kompatibilitas untuk Colly Module yang
         * masih memanggil aktifkanEvent().
         */
        aktifkanEvent() {
            this.bindEvents();
            this.renumberAll();
            this.calculateAll();
        }
    };

    window.ShipmentItems =
        ShipmentItems;

    window.renumberItems = function (tbody) {
        return window.ShipmentItems
            .renumberItems(tbody);
    };

    window.hitungItem = function (row) {
        return window.ShipmentItems
            .calculateRow(row);
    };

    window.aktifkanEvent = function () {
        return window.ShipmentItems
            .aktifkanEvent();
    };

    window.initializeItems = function () {
        return window.ShipmentItems.init();
    };

})(window, jQuery);