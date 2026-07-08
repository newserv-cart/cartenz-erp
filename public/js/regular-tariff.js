document.addEventListener("DOMContentLoaded", function () {

    // Ambil elemen tabel
    const tbody = document.querySelector("#tariffTable tbody");
    const btnAddRange = document.getElementById("btnAddRange");

    // Cek dulu apakah elemen tabel ada di halaman
    if (!tbody) {
        console.warn("⚠️ Elemen tabel #tariffTable tidak ditemukan");
        return;
    }

    //--------------------------------------------------
    // LOAD TARIFF DARI SERVER
    //--------------------------------------------------
    async function loadTariff() {
        let country = document.querySelector("[name='country_id']")?.value.trim() || "";
        let service = document.querySelector("[name='service_id']")?.value.trim() || "";

        if (country === "" || service === "") {
            console.log("ℹ️ Data negara atau layanan belum lengkap");
            return;
        }

        try {
            console.log("🔄 Memuat tarif...", { country, service });

            let response = await fetch(
                BASEURL + "/master/regular-tariffs/load?country_id=" + country +
                "&service_id=" + service
            );

            if (!response.ok) throw new Error("Gagal terhubung ke server");

            let data = await response.json();
            console.log("✅ Data tarif diterima:", data);

            // Kosongkan isi tabel lama
            tbody.innerHTML = "";

            // Jika ada data, tampilkan
            if (Array.isArray(data) && data.length > 0) {
                data.forEach(function (row) {
                    tbody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>
                                <input type="number" step="0.01" min="0.01" 
                                       name="weight_from[]" class="form-control text-end" 
                                       value="${row.weight_from || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="${row.weight_from || 0.01}" 
                                       name="weight_to[]" class="form-control text-end" 
                                       value="${row.weight_to || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" 
                                       name="agent_a_price[]" class="form-control text-end" 
                                       value="${row.agent_a_price || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" 
                                       name="agent_b_price[]" class="form-control text-end" 
                                       value="${row.agent_b_price || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" 
                                       name="agent_c_price[]" class="form-control text-end" 
                                       value="${row.agent_c_price || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" 
                                       name="publish_price[]" class="form-control text-end" 
                                       value="${row.publish_price || 0}" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" 
                                       name="vendor_cost[]" class="form-control text-end" 
                                       value="${row.vendor_cost || 0}" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm removeRow">🗑</button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                console.log("ℹ️ Tidak ada data tarif untuk kriteria ini");
            }

        } catch (err) {
            console.error("❌ Error memuat tarif:", err);
        }
    }

    //--------------------------------------------------
    // FUNGSI TAMBAH BARIS BARU
    //--------------------------------------------------
    function addNewRow(from = 1, to = 1) {
        const safeFrom = Math.max(0.01, parseFloat(from) || 0.01);
        const safeTo = Math.max(safeFrom, parseFloat(to) || safeFrom);

        tbody.insertAdjacentHTML("beforeend", `
            <tr>
                <td><input type="number" step="0.01" min="0.01" name="weight_from[]" class="form-control text-end" value="${safeFrom}" required></td>
                <td><input type="number" step="0.01" min="${safeFrom}" name="weight_to[]" class="form-control text-end" value="${safeTo}" required></td>
                <td><input type="number" step="0.01" min="0" name="agent_a_price[]" class="form-control text-end" value="0" required></td>
                <td><input type="number" step="0.01" min="0" name="agent_b_price[]" class="form-control text-end" value="0" required></td>
                <td><input type="number" step="0.01" min="0" name="agent_c_price[]" class="form-control text-end" value="0" required></td>
                <td><input type="number" step="0.01" min="0" name="publish_price[]" class="form-control text-end" value="0" required></td>
                <td><input type="number" step="0.01" min="0" name="vendor_cost[]" class="form-control text-end" value="0" required></td>
                <td class="text-center"><button type="button" class="btn btn-danger btn-sm removeRow">🗑</button></td>
            </tr>
        `);
    }

    //--------------------------------------------------
    // EVENT LISTENER
    //--------------------------------------------------
    // Tambah baris saat tombol diklik
    if (btnAddRange) {
        btnAddRange.addEventListener("click", function () {
            const rows = tbody.querySelectorAll("tr");
            const lastTo = rows.length > 0 ? parseFloat(rows[rows.length - 1].querySelector("input[name='weight_to[]']").value) : 0;
            const nextFrom = Math.round((lastTo + 0.01) * 100) / 100;
            addNewRow(nextFrom, Math.round((nextFrom + 9.99) * 100) / 100);
        });
    }

    // Hapus baris
    tbody.addEventListener("click", function (e) {
        if (e.target.classList.contains("removeRow")) {
            const rows = tbody.querySelectorAll("tr");
            if (rows.length > 1) {
                e.target.closest("tr").remove();
            } else {
                alert("❌ Minimal harus ada satu baris tarif!");
            }
        }
    });

    // Panggil fungsi saat pilihan berubah
    document.querySelector("[name='country_id']")?.addEventListener("change", loadTariff);
    document.querySelector("[name='service_id']")?.addEventListener("change", loadTariff);

});