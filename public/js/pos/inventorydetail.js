document.addEventListener("DOMContentLoaded", () => {
    const BASE_URL = document
        .querySelector('meta[name="base-url"]')
        ?.getAttribute("content") || "";

    const searchInput = document.getElementById("ingredientSearch");
    const suggestBox = document.getElementById("ingredientSuggest");
    const ingredientList = document.getElementById("ingredientList");
    const hiddenInputs = document.getElementById("hiddenInputs");
    const form = document.getElementById("inventoryForm");
    const statusInput = document.getElementById("inventory_status");
    const statusLabel = document.getElementById("statusLabel");
    const totalActual = document.getElementById("totalActual");
    const saveDraftBtn = document.getElementById("saveDraftBtn");
    const completeBtn = document.getElementById("completeBtn");

    let selectedIngredients = [];
    let activeTab = "all";

    const formatQty = value =>
        Number(value || 0).toLocaleString("vi-VN", { maximumFractionDigits: 2 });

    const formatMoney = value =>
        Number(value || 0).toLocaleString("vi-VN");

    function updateSummary() {
        const countAll = selectedIngredients.length;
        let countMatch = 0;
        let countDiff = 0;
        let countUnchecked = 0;
        let sumActual = 0;

        selectedIngredients.forEach(item => {
            if (Number.isFinite(item.actual)) {
                sumActual += item.actual;
                const diff = item.actual - item.stock;
                if (Math.abs(diff) < 0.0001) {
                    countMatch++;
                } else {
                    countDiff++;
                }
            } else {
                countUnchecked++;
            }
        });

        document.getElementById("countAll").innerText = countAll;
        document.getElementById("countMatch").innerText = countMatch;
        document.getElementById("countDiff").innerText = countDiff;
        document.getElementById("countUnchecked").innerText = countUnchecked;
        if (totalActual) {
            totalActual.innerText = formatQty(sumActual);
        }
    }

    function applyTabFilter() {
        if (!ingredientList) return;
        const rows = ingredientList.querySelectorAll("tr");
        rows.forEach(row => {
            const state = row.dataset.state || "unchecked";
            if (activeTab === "all" || state === activeTab) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    function renderIngredientTable() {
        if (!ingredientList) return;

        ingredientList.innerHTML = selectedIngredients.map((item, index) => {
            const hasActual = Number.isFinite(item.actual);
            const diff = hasActual ? item.actual - item.stock : null;
            const diffValue = hasActual ? diff * item.price : null;
            const state = !hasActual ? "unchecked" : (Math.abs(diff) < 0.0001 ? "match" : "diff");
            return `
                <tr data-id="${item.id}" data-state="${state}">
                    <td>${index + 1}</td>
                    <td>${item.code}</td>
                    <td>${item.name} (${item.unit})</td>
                    <td>${formatQty(item.stock)}</td>
                    <td>
                        <input type="number" min="0" step="0.01"
                               value="${hasActual ? item.actual : ''}"
                               data-id="${item.id}"
                               class="actual-input">
                    </td>
                    <td class="diff-cell">${hasActual ? formatQty(diff) : '--'}</td>
                    <td class="diff-value">${hasActual ? formatMoney(diffValue) : '--'}</td>
                    <td>
                        <button type="button"
                                class="remove-ingredient-btn"
                                data-id="${item.id}">✖</button>
                    </td>
                </tr>
            `;
        }).join("");

        updateSummary();
        applyTabFilter();
    }

    if (ingredientList) {
        ingredientList.addEventListener("input", (e) => {
            const input = e.target;
            if (!input.classList.contains("actual-input")) return;

            const item = selectedIngredients.find(i => i.id == input.dataset.id);
            if (!item) return;

            const raw = input.value.trim();
            if (raw === "") {
                item.actual = null;
            } else {
                const value = parseFloat(raw);
                item.actual = Number.isFinite(value) ? value : null;
            }

            const row = input.closest("tr");
            if (!row) return;

            const hasActual = Number.isFinite(item.actual);
            const diff = hasActual ? item.actual - item.stock : null;
            const diffValue = hasActual ? diff * item.price : null;
            const state = !hasActual ? "unchecked" : (Math.abs(diff) < 0.0001 ? "match" : "diff");

            row.dataset.state = state;
            row.querySelector(".diff-cell").innerText = hasActual ? formatQty(diff) : "--";
            row.querySelector(".diff-value").innerText = hasActual ? formatMoney(diffValue) : "--";

            updateSummary();
            applyTabFilter();
        });

        ingredientList.addEventListener("click", (e) => {
            const btn = e.target.closest(".remove-ingredient-btn");
            if (!btn) return;
            const id = btn.dataset.id;
            selectedIngredients = selectedIngredients.filter(i => i.id != id);
            renderIngredientTable();
        });
    }

    if (searchInput && suggestBox) {
        searchInput.addEventListener("input", async () => {
            const keyword = searchInput.value.trim();
            if (!keyword) {
                suggestBox.style.display = "none";
                return;
            }

            try {
                const res = await fetch(
                    `${BASE_URL}/pos/ingredients/search?keyword=${keyword}`
                );
                const data = await res.json();

                if (!data.length) {
                    suggestBox.innerHTML = `<div class="suggest-item">Không tìm thấy</div>`;
                    suggestBox.style.display = "block";
                    return;
                }

                suggestBox.innerHTML = data.map(item => `
                    <div class="suggest-item"
                        data-id="${item.id}"
                        data-code="${item.code}"
                        data-name="${item.name}"
                        data-unit="${item.unit}"
                        data-stock="${item.stock_qty}"
                        data-price="${item.last_price}">
                        <strong>${item.name}</strong> - ${item.code}
                    </div>
                `).join("");

                suggestBox.style.display = "block";
            } catch (e) {
                console.error(e);
            }
        });

        suggestBox.addEventListener("click", e => {
            const item = e.target.closest(".suggest-item");
            if (!item) return;
            if (!item.dataset.id) return;

            if (selectedIngredients.some(i => i.id == item.dataset.id)) {
                suggestBox.style.display = "none";
                searchInput.value = "";
                return;
            }

            selectedIngredients.push({
                id: item.dataset.id,
                code: item.dataset.code,
                name: item.dataset.name,
                unit: item.dataset.unit,
                stock: Number(item.dataset.stock) || 0,
                price: Number(item.dataset.price) || 0,
                actual: null,
            });

            renderIngredientTable();
            suggestBox.style.display = "none";
            searchInput.value = "";
        });
    }

    document.querySelectorAll(".inventory-tabs .tab").forEach(tab => {
        tab.addEventListener("click", () => {
            document.querySelectorAll(".inventory-tabs .tab").forEach(t => t.classList.remove("active"));
            tab.classList.add("active");
            activeTab = tab.dataset.tab;
            applyTabFilter();
        });
    });

    async function submitForm(status) {
        if (!selectedIngredients.length) {
            showToast("Chưa có nguyên liệu nào!", "warning");
            return;
        }

        const missingActual = selectedIngredients.some(i => !Number.isFinite(i.actual));
        if (missingActual) {
            showToast("Vui lòng nhập số lượng thực tế.", "warning");
            return;
        }

        if (statusInput) statusInput.value = status;
        if (statusLabel) {
            statusLabel.innerText = status === "completed" ? "Đã cân bằng kho" : "Phiếu tạm";
        }

        hiddenInputs.innerHTML = "";
        selectedIngredients.forEach((item, index) => {
            hiddenInputs.innerHTML += `
                <input type="hidden" name="items[${index}][ingredient_id]" value="${item.id}">
                <input type="hidden" name="items[${index}][actual_qty]" value="${item.actual}">
            `;
        });

        const formData = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: form.method,
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                showToast(data.message || "Đã lưu phiếu kiểm kho!", "success");
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                showToast(data.message || "Lưu phiếu thất bại!", "error");
            }
        } catch (err) {
            console.error(err);
            showToast("Lỗi server!", "error");
        }
    }

    if (saveDraftBtn) {
        saveDraftBtn.addEventListener("click", () => submitForm("draft"));
    }

    if (completeBtn) {
        completeBtn.addEventListener("click", () => submitForm("completed"));
    }
});
