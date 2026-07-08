const tableBody = document.querySelector("#tariffTable tbody");

document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("#formTariff");
    const btnAdd = document.getElementById("btnAddRange");

    if (!tableBody) return;

    // ==========================
    // ADD ROW
    // ==========================
    btnAdd?.addEventListener("click", function () {
        // Ambil nilai berat terakhir untuk lanjutkan
        const lastRow = tableBody.querySelector("tr:last-child");
        let nextFrom = 1;

        if (lastRow) {
            const lastTo = parseFloat(lastRow.querySelector("input[name='weight_to[]']").value) || 0;
            nextFrom = Math.round((lastTo + 0.01) * 100) / 100; // 2 desimal
        }

        let row = `
<tr>
<td>
<input type="number" step="0.01" min="0.01" required
name="weight_from[]"
class="form-control text-end"
value="${nextFrom}">
</td>
<td>
<input type="number" step="0.01" min="${nextFrom}" required
name="weight_to[]"
class="form-control text-end"
placeholder="Misal: ${nextFrom + 9.99}">
</td>
<td><input type="number" step="0.01" min="0" required name="agent_a_price[]" class="form-control text-end" value="0"></td>
<td><input type="number" step="0.01" min="0" required name="agent_b_price[]" class="form-control text-end" value="0"></td>
<td><input type="number" step="0.01" min="0" required name="agent_c_price[]" class="form-control text-end" value="0"></td>
<td><input type="number" step="0.01" min="0" required name="publish_price[]" class="form-control text-end" value="0"></td>
<td><input type="number" step="0.01" min="0" required name="vendor_cost[]" class="form-control text-end" value="0"></td>
<td class="text-center">
<button type="button" class="btn btn-danger btn-sm removeRow">🗑</button>
</td>
</tr>
`;

        tableBody.insertAdjacentHTML("beforeend", row);
    });

    // ==========================
    // DELETE ROW
    // ==========================
    document.addEventListener("click", function (e) {
        if (e.target.closest(".removeRow")) {
            const rows = tableBody.querySelectorAll("tr");
            if (rows.length > 1) {
                e.target.closest("tr").remove();
            } else {
                alert("Minimal harus ada satu rentang berat!");
            }
        }
    });

    // ==========================
    // VALIDASI SEBELUM KIRIM
    // ==========================
    form?.addEventListener("submit", function (e) {
        let fromInputs = document.querySelectorAll("[name='weight_from[]']");
        let toInputs = document.querySelectorAll("[name='weight_to[]']");

        for (let i = 0; i < fromInputs.length; i++) {
            let wFrom = parseFloat(fromInputs[i].value) || 0;
            let wTo = parseFloat(toInputs[i].value) || 0;

            if (wFrom <= 0) {
                alert(`Baris ${i + 1}: Weight From harus lebih besar dari 0`);
                e.preventDefault();
                return;
            }

            if (wTo < wFrom) {
                alert(`Baris ${i + 1}: Weight To tidak boleh lebih kecil dari Weight From`);
                e.preventDefault();
                return;
            }

            // Izinkan: wFrom == wTo (untuk 1-1, dst)
        }
    });

});

// =====================================
// AUTO LOAD TARIFF JIKA SUDAH ADA
// =====================================
async function loadTariff() {
    let country = document.querySelector("[name='country_id']").value;
    let service = document.querySelector("[name='service_id']").value;

    if (country === "" || service === "" ) return;

    try {
        let response = await fetch(
            BASEURL +
            "/master/regular-tariffs/load?country_id=" + country +
            "&service_id=" + service
        );

        let data = await response.json();

        if (!data || data.length === 0) return;

        tableBody.innerHTML = "";

        data.forEach(function (r) {
            tableBody.insertAdjacentHTML("beforeend", `
<tr>
<td>
<input type="number" step="0.01" min="0.01" required
name="weight_from[]"
class="form-control text-end"
value="${r.weight_from}">
</td>
<td>
<input type="number" step="0.01" min="${r.weight_from}" required
name="weight_to[]"
class="form-control text-end"
value="${r.weight_to}">
</td>
<td><input type="number" step="0.01" min="0" required name="agent_a_price[]" class="form-control text-end" value="${r.agent_a_price}"></td>
<td><input type="number" step="0.01" min="0" required name="agent_b_price[]" class="form-control text-end" value="${r.agent_b_price}"></td>
<td><input type="number" step="0.01" min="0" required name="agent_c_price[]" class="form-control text-end" value="${r.agent_c_price}"></td>
<td><input type="number" step="0.01" min="0" required name="publish_price[]" class="form-control text-end" value="${r.publish_price}"></td>
<td><input type="number" step="0.01" min="0" required name="vendor_cost[]" class="form-control text-end" value="${r.vendor_cost}"></td>
<td class="text-center">
<button type="button" class="btn btn-danger btn-sm removeRow">🗑</button>
</td>
</tr>
`);
        });

    } catch (err) {
        console.error("Gagal memuat tarif:", err);
    }
}

document.querySelector("[name='country_id']")?.addEventListener("change", loadTariff);
document.querySelector("[name='service_id']")?.addEventListener("change", loadTariff);

// =============================
// UPDATE LINK DOWNLOAD TEMPLATE
// =============================
const btnDownload = document.getElementById("btnDownloadTemplate");

function updateDownloadLink() {
    if (!btnDownload) return;

    let country = document.querySelector("[name='country_id']").value;
    let service = document.querySelector("[name='service_id']").value;

    btnDownload.href =
        BASEURL +
        "/master/regular-tariffs/download-template" +
        "?country_id=" + country +
        "&service_id=" + service;
}

document.querySelector("[name='country_id']")?.addEventListener("change", updateDownloadLink);
document.querySelector("[name='service_id']")?.addEventListener("change", updateDownloadLink);

updateDownloadLink();