document.documentElement.classList.add("js");

function getJsonErrorMessage(payload, fallback) {
    if (payload && payload.errors) {
        const firstKey = Object.keys(payload.errors)[0];
        if (firstKey && payload.errors[firstKey] && payload.errors[firstKey][0]) {
            return payload.errors[firstKey][0];
        }
    }
    if (payload && payload.message) {
        return payload.message;
    }
    return fallback || "Request failed.";
}

async function readJsonResponse(res) {
    const contentType = res.headers.get("content-type") || "";
    if (!contentType.includes("application/json")) {
        await res.text();
        throw new Error(`Unexpected response (${res.status}).`);
    }
    return res.json();
}

var locationSelectControls = [];

var closeLocationSelectMenus = function () {
    locationSelectControls.forEach(function (control) {
        control.close();
    });
};

var syncLocationSelects = function () {
    locationSelectControls.forEach(function (control) {
        control.buildMenu();
        control.updateDisplay();
    });
};

var initLocationSelect = function (wrapper) {
    if (!wrapper) {
        return;
    }
    var select = wrapper.querySelector("select");
    var trigger = wrapper.querySelector(".staff-select-trigger");
    var valueText = wrapper.querySelector(".staff-select-value");
    var menu = wrapper.querySelector(".staff-select-menu");

    if (!select || !trigger || !valueText || !menu) {
        return;
    }

    var buildMenu = function () {
        menu.innerHTML = "";
        Array.prototype.slice.call(select.options).forEach(function (option) {
            var button = document.createElement("button");
            button.type = "button";
            button.className = "staff-select-item";
            button.textContent = option.text;
            button.dataset.value = option.value;
            if (option.selected) {
                button.classList.add("is-selected");
            }
            button.addEventListener("click", function () {
                select.value = option.value;
                select.dispatchEvent(new Event("change", { bubbles: true }));
                closeLocationSelectMenus();
            });
            menu.appendChild(button);
        });
    };

    var updateDisplay = function () {
        var selectedOption = select.options[select.selectedIndex];
        valueText.textContent = selectedOption ? selectedOption.text : "";
        if (selectedOption && selectedOption.value === "") {
            valueText.classList.add("is-placeholder");
        } else {
            valueText.classList.remove("is-placeholder");
        }
        Array.prototype.slice.call(menu.children).forEach(function (child) {
            if (child.dataset.value === select.value) {
                child.classList.add("is-selected");
            } else {
                child.classList.remove("is-selected");
            }
        });
    };

    var closeMenu = function () {
        menu.classList.remove("open");
        menu.setAttribute("aria-hidden", "true");
        trigger.setAttribute("aria-expanded", "false");
    };

    var openMenu = function () {
        buildMenu();
        menu.classList.add("open");
        menu.setAttribute("aria-hidden", "false");
        trigger.setAttribute("aria-expanded", "true");
    };

    trigger.addEventListener("click", function (event) {
        event.stopPropagation();
        var isOpen = menu.classList.contains("open");
        closeLocationSelectMenus();
        if (!isOpen) {
            openMenu();
        }
    });

    menu.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    select.addEventListener("change", updateDisplay);

    locationSelectControls.push({
        buildMenu: buildMenu,
        updateDisplay: updateDisplay,
        close: closeMenu
    });

    buildMenu();
    updateDisplay();
};

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("[data-location-select]").forEach(function (wrapper) {
        initLocationSelect(wrapper);
    });
    syncLocationSelects();
});

document.addEventListener("click", closeLocationSelectMenus);
document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        closeLocationSelectMenus();
    }
});

function resolveAsset(src) {
    if (!src) {
        return "";
    }
    if (/^(https?:)?\/\//i.test(src) || src.indexOf("data:") === 0) {
        return src;
    }
    return `${window.routes.assetUrl}${String(src).replace(/^\/+/, "")}`;
}

function createImageManager(config) {
    const previewImage = config.previewImage;
    const placeholderText = config.placeholderText;
    const removeButton = config.removeButton;
    const chooseButton = config.chooseButton;
    const fileInput = config.fileInput;
    const pathInput = config.pathInput;
    let selectedFile = null;

    const showPreview = function (src) {
        if (!previewImage || !placeholderText) {
            return;
        }
        previewImage.src = src;
        previewImage.style.display = "block";
        placeholderText.style.display = "none";
        if (removeButton) {
            removeButton.style.display = "inline-flex";
        }
    };

    const clear = function () {
        selectedFile = null;
        if (previewImage) {
            previewImage.src = "";
            previewImage.style.display = "none";
        }
        if (placeholderText) {
            placeholderText.style.display = "block";
        }
        if (removeButton) {
            removeButton.style.display = "none";
        }
        if (fileInput) {
            fileInput.value = "";
        }
        if (pathInput) {
            pathInput.value = "";
        }
    };

    const setStored = function (path) {
        selectedFile = null;
        if (fileInput) {
            fileInput.value = "";
        }
        if (pathInput) {
            pathInput.value = path || "";
        }
        if (path) {
            showPreview(resolveAsset(path));
        } else {
            clear();
        }
    };

    chooseButton?.addEventListener("click", function () {
        fileInput?.click();
    });

    fileInput?.addEventListener("change", function () {
        const file = fileInput.files?.[0];
        if (!file) {
            return;
        }
        selectedFile = file;
        if (pathInput) {
            pathInput.value = "";
        }
        const reader = new FileReader();
        reader.onload = function (event) {
            showPreview(event.target.result);
        };
        reader.readAsDataURL(file);
    });

    removeButton?.addEventListener("click", function (event) {
        event.preventDefault();
        clear();
    });

    return {
        clear: clear,
        setStored: setStored,
        getFile: function () {
            return selectedFile;
        },
        getPath: function () {
            return pathInput ? pathInput.value.trim() : "";
        }
    };
}

document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const searchInput = document.getElementById("location-search");
    const statusRadios = document.querySelectorAll("input[name='status']");
    const regionDropdown = document.getElementById("regionDropdown");
    const regionDisplay = regionDropdown?.querySelector(".selected-display");
    const regionItems = regionDropdown
        ? Array.from(regionDropdown.querySelectorAll(".dropdown-list li"))
        : [];
    const regionHidden = document.getElementById("regionSelect");
    const regionCurrentValue = document.getElementById("regionCurrentValue");
    const editRegionBtn = document.getElementById("editRegionBtn");
    const addRegionBtn = document.querySelector(".add-region-btn");
    const overlay = document.getElementById("popup-overlay");
    const regionPopup = document.getElementById("popup-add-region");
    const regionPopupTitle = document.getElementById("regionPopupTitle");
    const regionNameInput = document.getElementById("region-name");
    const regionSaveBtn = document.getElementById("save-popup");
    const regionCancelBtn = document.getElementById("cancel-popup");
    const regionDeleteBtn = document.getElementById("delete-popup");

    const pagination = document.getElementById("pagination");
    const pageInfo = document.getElementById("pageInfo");
    const prevPage = document.getElementById("prevPage");
    const nextPage = document.getElementById("nextPage");

    const locationFormOverlay = document.getElementById("locationFormOverlay");
    const locationFormTitle = document.getElementById("locationFormTitle");
    const locationForm = document.getElementById("locationForm");
    const locationFormClose = document.getElementById("locationFormClose");
    const locationSaveBtn = document.getElementById("locationSave");
    const locationCancelBtn = document.getElementById("locationCancel");
    const addLocationBtn = document.getElementById("addLocationBtn");
    const locationIdInput = document.getElementById("location_id");
    const locationCodeInput = document.getElementById("location_code");
    const locationNameInput = document.getElementById("location_name");
    const locationRegionSelect = document.getElementById("location_region");
    const locationCapacityInput = document.getElementById("location_capacity");
    const locationAreaInput = document.getElementById("location_area");
    const locationFloorsInput = document.getElementById("location_floors");
    const locationTimeStartInput = document.getElementById("location_time_start");
    const locationTimeEndInput = document.getElementById("location_time_end");
    const locationStatusSelect = document.getElementById("location_status");
    const locationMapUrlInput = document.getElementById("location_map_url");
    const detailSummaryInput = document.getElementById("detail_summary");
    const detailIntroTitleInput = document.getElementById("detail_intro_title");
    const detailIntroContentInput = document.getElementById("detail_intro_content");
    const detailMenuTitleInput = document.getElementById("detail_menu_title");
    const detailClosingTitleInput = document.getElementById("detail_closing_title");
    const detailClosingContentInput = document.getElementById("detail_closing_content");
    const detailAddressInput = document.getElementById("detail_address");
    const detailHotlineInput = document.getElementById("detail_hotline");
    const detailRatingInput = document.getElementById("detail_rating");
    const detailReviewCountInput = document.getElementById("detail_review_count");
    const detailWebsiteUrlInput = document.getElementById("detail_website_url");
    const detailFacebookUrlInput = document.getElementById("detail_facebook_url");
    const detailTiktokUrlInput = document.getElementById("detail_tiktok_url");
    const detailBookingNoteInput = document.getElementById("detail_booking_note");
    const detailParkingNoteInput = document.getElementById("detail_parking_note");
    const detailOpenNoteInput = document.getElementById("detail_open_note");
    const sectionsList = document.getElementById("locationSectionsList");
    const sectionTemplate = document.getElementById("locationSectionTemplate");
    const addLocationSectionBtn = document.getElementById("addLocationSectionBtn");

    const normalizeTimeValue = (value) => {
        if (!value) return "";
        const match = value.match(/^(\d{1,2}):(\d{2})/);
        if (!match) return value;
        return `${match[1].padStart(2, "0")}:${match[2]}`;
    };

    const rowsPerPage = 10;
    let currentPage = 1;
    let filters = {
        keyword: "",
        region: "",
        status: "all"
    };
    let editingRegionId = null;
    const locationImageManager = createImageManager({
        previewImage: document.getElementById("locationPreviewImage"),
        placeholderText: document.getElementById("locationAddImageText"),
        removeButton: document.getElementById("locationRemoveImageBtn"),
        chooseButton: document.getElementById("locationChooseImage"),
        fileInput: document.getElementById("locationImageInput"),
        pathInput: document.getElementById("location_thumbnail")
    });
    const detailLogoManager = createImageManager({
        previewImage: document.getElementById("detailLogoPreviewImage"),
        placeholderText: document.getElementById("detailLogoAddImageText"),
        removeButton: document.getElementById("detailLogoRemoveImageBtn"),
        chooseButton: document.getElementById("detailLogoChooseImage"),
        fileInput: document.getElementById("detailLogoImageInput"),
        pathInput: document.getElementById("detail_logo_image")
    });
    const detailCoverManager = createImageManager({
        previewImage: document.getElementById("detailCoverPreviewImage"),
        placeholderText: document.getElementById("detailCoverAddImageText"),
        removeButton: document.getElementById("detailCoverRemoveImageBtn"),
        chooseButton: document.getElementById("detailCoverChooseImage"),
        fileInput: document.getElementById("detailCoverImageInput"),
        pathInput: document.getElementById("detail_cover_image")
    });
    const detailMenuManager = createImageManager({
        previewImage: document.getElementById("detailMenuPreviewImage"),
        placeholderText: document.getElementById("detailMenuAddImageText"),
        removeButton: document.getElementById("detailMenuRemoveImageBtn"),
        chooseButton: document.getElementById("detailMenuChooseImage"),
        fileInput: document.getElementById("detailMenuImageInput"),
        pathInput: document.getElementById("detail_menu_image")
    });

    function refreshSectionHeadings() {
        Array.from(sectionsList?.querySelectorAll(".detail-section-card") || []).forEach((card, index) => {
            const heading = card.querySelector(".detail-section-head h5");
            if (heading) {
                heading.textContent = `Block nội dung ${index + 1}`;
            }
        });
    }

    function createSectionCard(section = {}) {
        if (!sectionTemplate || !sectionsList) {
            return null;
        }
        const fragment = sectionTemplate.content.cloneNode(true);
        const card = fragment.querySelector(".detail-section-card");
        const imageManager = createImageManager({
            previewImage: card.querySelector(".section-preview-image"),
            placeholderText: card.querySelector(".section-add-image-text"),
            removeButton: card.querySelector(".section-remove-image-btn"),
            chooseButton: card.querySelector(".section-choose-image-btn"),
            fileInput: card.querySelector(".section-image-input"),
            pathInput: card.querySelector(".section-image-path-input")
        });

        card.querySelector(".section-title-input").value = section.title || "";
        card.querySelector(".section-content-input").value = section.content || "";
        card.querySelector(".section-sort-order-input").value =
            section.sort_order !== undefined && section.sort_order !== null ? section.sort_order : "";
        imageManager.setStored(section.image || "");
        card._imageManager = imageManager;

        card.querySelector(".section-remove-btn")?.addEventListener("click", () => {
            card.remove();
            refreshSectionHeadings();
        });

        sectionsList.appendChild(card);
        refreshSectionHeadings();
        return card;
    }

    function renderSections(sections = []) {
        if (!sectionsList) {
            return;
        }
        sectionsList.innerHTML = "";
        sections.forEach((section) => createSectionCard(section));
    }

    function resetDetailFields() {
        detailSummaryInput.value = "";
        detailIntroTitleInput.value = "";
        detailIntroContentInput.value = "";
        detailMenuTitleInput.value = "";
        detailClosingTitleInput.value = "";
        detailClosingContentInput.value = "";
        detailAddressInput.value = "";
        detailHotlineInput.value = "";
        detailRatingInput.value = "";
        detailReviewCountInput.value = "";
        detailWebsiteUrlInput.value = "";
        detailFacebookUrlInput.value = "";
        detailTiktokUrlInput.value = "";
        detailBookingNoteInput.value = "";
        detailParkingNoteInput.value = "";
        detailOpenNoteInput.value = "";
        detailLogoManager.clear();
        detailCoverManager.clear();
        detailMenuManager.clear();
        renderSections([]);
    }

    function resetLocationForm() {
        locationForm.reset();
        locationIdInput.value = "";
        locationCodeInput.value = "";
        locationNameInput.value = "";
        locationRegionSelect.value = "";
        locationCapacityInput.value = "";
        locationAreaInput.value = "";
        locationFloorsInput.value = "";
        locationTimeStartInput.value = "";
        locationTimeEndInput.value = "";
        locationStatusSelect.value = "active";
        locationMapUrlInput.value = "";
        locationImageManager.clear();
        resetDetailFields();
        syncLocationSelects();
    }

    if (regionDisplay) {
        regionDisplay.addEventListener("click", (event) => {
            event.stopPropagation();
            regionDropdown.classList.toggle("active");
        });
    }

    regionItems.forEach((item) => {
        item.addEventListener("click", () => {
            const value = item.getAttribute("data-value");
            const text = item.textContent.trim();
            regionHidden.value = value;
            regionCurrentValue.textContent = text;
            regionDropdown.classList.remove("active");
            filters.region = value;
            if (value) {
                regionDropdown.classList.add("area-has-value");
                editRegionBtn?.classList.remove("d-none");
            } else {
                regionDropdown.classList.remove("area-has-value");
                editRegionBtn?.classList.add("d-none");
            }
            applyFilters();
        });
    });

    window.addEventListener("click", (event) => {
        if (regionDropdown && !regionDropdown.contains(event.target)) {
            regionDropdown.classList.remove("active");
        }
    });

    if (addRegionBtn) {
        addRegionBtn.addEventListener("click", (event) => {
            event.stopPropagation();
            openRegionPopup("add");
        });
    }

    if (editRegionBtn) {
        editRegionBtn.addEventListener("click", () => {
            const selectedId = regionHidden.value;
            const selectedText = regionCurrentValue.textContent.trim();
            if (!selectedId) {
                showToast("Vui lòng chọn khu vực cần chỉnh sửa.", "warning");
                return;
            }
            openRegionPopup("edit", selectedId, selectedText);
        });
    }

    if (overlay) {
        overlay.addEventListener("click", closeRegionPopup);
    }

    if (regionCancelBtn) {
        regionCancelBtn.addEventListener("click", closeRegionPopup);
    }

    if (regionSaveBtn) {
        regionSaveBtn.addEventListener("click", async () => {
            const name = regionNameInput.value.trim();
            if (!name) {
                showToast("Vui lòng nhập tên khu vực.", "warning");
                return;
            }
            const url = editingRegionId
                ? window.routes.region.updatePattern.replace("__ID__", editingRegionId)
                : window.routes.region.store;
            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    },
                    body: JSON.stringify({ name })
                });
                const data = await readJsonResponse(res);
                if (res.ok && data.success) {
                    showToast(editingRegionId ? "Cập nhật khu vực thành công" : "Thêm khu vực thành công");
                    closeRegionPopup();
                    setTimeout(() => {
                        location.reload();
                    }, 600);
                } else {
                    showToast(data.message || "Có lỗi xảy ra", "error");
                }
            } catch (error) {
                console.error(error);
                showToast("Lỗi server", "error");
            }
        });
    }

    if (regionDeleteBtn) {
        regionDeleteBtn.addEventListener("click", async () => {
            if (!editingRegionId) {
                showToast("Không có khu vực nào để xóa", "warning");
                return;
            }
            if (!await openConfirmDialog("Bạn có chắc chắn muốn xóa khu vực này?")) return;

            try {
                const res = await fetch(window.routes.region.deletePattern.replace("__ID__", editingRegionId), {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });
                const data = await readJsonResponse(res);
                if (res.ok && data.success) {
                    showToast("Xóa khu vực thành công");
                    closeRegionPopup();
                    setTimeout(() => location.reload(), 600);
                } else {
                    showToast(data.message || "Không thể xóa khu vực", "error");
                }
            } catch (err) {
                console.error(err);
                showToast("Lỗi server", "error");
            }
        });
    }

    function openRegionPopup(mode, id = null, name = "") {
        editingRegionId = mode === "edit" ? id : null;
        overlay.style.display = "block";
        regionPopup.style.display = "block";
        regionPopupTitle.textContent = mode === "edit" ? "Sửa khu vực" : "Thêm khu vực";
        regionNameInput.value = name;
        if (mode === "add") {
            regionDeleteBtn?.setAttribute("style", "display:none");
        } else {
            regionDeleteBtn?.setAttribute("style", "display:inline-block");
        }
    }

    function closeRegionPopup() {
        if (!overlay || !regionPopup) return;
        overlay.style.display = "none";
        regionPopup.style.display = "none";
        regionNameInput.value = "";
        editingRegionId = null;
    }

    searchInput?.addEventListener("input", (event) => {
        filters.keyword = event.target.value.trim().toLowerCase();
        applyFilters();
    });

    statusRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            filters.status = document.querySelector("input[name='status']:checked").value;
            applyFilters();
        });
    });

    prevPage?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            renderPagination();
        }
    });

    nextPage?.addEventListener("click", () => {
        currentPage++;
        renderPagination();
    });

    function applyFilters() {
        document.querySelectorAll(".location-row").forEach(row => {
            const text = `${row.dataset.name || ""} ${row.dataset.code || ""}`.toLowerCase();
            let match = true;
            if (filters.keyword && !text.includes(filters.keyword)) {
                match = false;
            }
            if (match && filters.region) {
                match = row.dataset.region === filters.region;
            }
            if (match && filters.status !== "all") {
                match = row.dataset.status === filters.status;
            }
            row.dataset.filtered = match ? "1" : "0";
        });
        currentPage = 1;
        renderPagination();
    }

    function getFilteredRows() {
        return Array.from(document.querySelectorAll(".location-row"))
            .filter(row => row.dataset.filtered === "1");
    }

    function renderPagination() {
        const filtered = getFilteredRows();
        const totalPages = Math.max(Math.ceil(filtered.length / rowsPerPage), 1);
        if (currentPage > totalPages) currentPage = totalPages;

        document.querySelectorAll(".location-row").forEach(row => {
            row.style.display = "none";
            const detail = document.getElementById(`detail-${row.dataset.id}`);
            if (detail) detail.style.display = "none";
            row.classList.remove("active");
        });

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filtered.slice(start, end).forEach(row => {
            row.style.display = "";
        });

        pageInfo && (pageInfo.textContent = `Trang ${currentPage} / ${totalPages}`);
        prevPage && (prevPage.disabled = currentPage <= 1);
        nextPage && (nextPage.disabled = currentPage >= totalPages);
        pagination?.classList.toggle("d-none", totalPages <= 1);
    }

    document.querySelectorAll(".location-row").forEach(row => {
        row.addEventListener("click", () => {
            const id = row.dataset.id;
            const detail = document.getElementById(`detail-${id}`);
            if (!detail) return;
            const wasVisible = detail.style.display === "table-row";
            document.querySelectorAll(".detail-row").forEach(d => d.style.display = "none");
            document.querySelectorAll(".location-row").forEach(r => r.classList.remove("active"));
            if (!wasVisible) {
                detail.style.display = "table-row";
                row.classList.add("active");
                initDetailButtons(id, detail);
            }
        });
    });

    function initDetailButtons(id, detailRow) {
        const btnUpdate = detailRow.querySelector(".tb-update");
        const btnDelete = detailRow.querySelector(".tb-delete");
        const btnStatus = detailRow.querySelector(".tb-status");

        if (btnStatus) {
            updateStatusButtonUI(btnStatus, btnStatus.dataset.status);
            btnStatus.onclick = async (event) => {
                event.preventDefault();
                try {
                    const res = await fetch(window.routes.location.toggleStatusPattern.replace("__ID__", id), {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });
                    const data = await readJsonResponse(res);
                    if (res.ok && data.success) {
                        const row = document.querySelector(`.location-row[data-id="${id}"]`);
                        if (row) {
                            row.dataset.status = data.status;
                            const statusCell = row.children[row.children.length - 1];
                            if (statusCell) {
                                statusCell.textContent = data.status === "active" ? "Đang hoạt động" : "Ngừng hoạt động";
                            }
                        }
                        updateStatusButtonUI(btnStatus, data.status);
                        showToast("Đã cập nhật trạng thái", "success");
                    } else {
                        showToast(data.message || "Không thể cập nhật trạng thái", "error");
                    }
                } catch (err) {
                    console.error(err);
                    showToast("Lỗi server", "error");
                }
            };
        }

        if (btnUpdate) {
            btnUpdate.onclick = async (event) => {
                event.preventDefault();
                await loadLocationInfo(id);
            };
        }

        if (btnDelete) {
            btnDelete.onclick = async (event) => {
                event.preventDefault();
                if (!await openConfirmDialog("Bạn có chắc chắn muốn xóa địa điểm này?")) return;
                try {
                    const res = await fetch(window.routes.location.deletePattern.replace("__ID__", id), {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });
                    const data = await readJsonResponse(res);
                    if (res.ok && data.success) {
                        showToast("Xóa địa điểm thành công", "success");
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showToast(data.message || "Không thể xóa địa điểm", "error");
                    }
                } catch (err) {
                    console.error(err);
                    showToast("Lỗi server", "error");
                }
            };
        }
    }

    function updateStatusButtonUI(btn, status) {
        if (!btn) return;
        btn.dataset.status = status;
        if (status === "active") {
            btn.innerHTML = `<i class="fa fa-lock"></i> Ngừng hoạt động`;
            btn.style.background = "#ff0000";
            btn.style.color = "#fff";
        } else {
            btn.innerHTML = `<i class="fa fa-check"></i> Cho phép hoạt động`;
            btn.style.background = "#00B63E";
            btn.style.color = "#fff";
        }
    }

    async function loadLocationInfo(id) {
        try {
            const url = window.routes.location.showPattern.replace("__ID__", id);
            const res = await fetch(url, {
                headers: {
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            });
            const data = await readJsonResponse(res);
            if (!res.ok || !data.success) {
                showToast("Không thể tải thông tin", "error");
                return;
            }
            const location = data.data;
            const detail = location.detail || {};
            locationIdInput.value = location.id;
            locationCodeInput.value = location.code || "";
            locationNameInput.value = location.name || "";
            locationRegionSelect.value = location.region_id || "";
            locationImageManager.setStored(location.thumbnail || "");
            locationCapacityInput.value = location.capacity || "";
            locationAreaInput.value = location.area || "";
            locationFloorsInput.value = location.floors || "";
            locationTimeStartInput.value = normalizeTimeValue(location.time_start || "");
            locationTimeEndInput.value = normalizeTimeValue(location.time_end || "");
            locationStatusSelect.value = location.status || "active";
            if (locationMapUrlInput) {
                locationMapUrlInput.value = location.map_url || "";
            }
            detailSummaryInput.value = detail.summary || "";
            detailIntroTitleInput.value = detail.intro_title || "";
            detailIntroContentInput.value = detail.intro_content || "";
            detailMenuTitleInput.value = detail.menu_title || "";
            detailClosingTitleInput.value = detail.closing_title || "";
            detailClosingContentInput.value = detail.closing_content || "";
            detailAddressInput.value = detail.address || "";
            detailHotlineInput.value = detail.hotline || "";
            detailRatingInput.value = detail.rating || "";
            detailReviewCountInput.value = detail.review_count || "";
            detailWebsiteUrlInput.value = detail.website_url || "";
            detailFacebookUrlInput.value = detail.facebook_url || "";
            detailTiktokUrlInput.value = detail.tiktok_url || "";
            detailBookingNoteInput.value = detail.booking_note || "";
            detailParkingNoteInput.value = detail.parking_note || "";
            detailOpenNoteInput.value = detail.open_note || "";
            detailLogoManager.setStored(detail.logo_image || "");
            detailCoverManager.setStored(detail.cover_image || "");
            detailMenuManager.setStored(detail.menu_image || "");
            renderSections(detail.sections || []);
            if (typeof syncLocationSelects === "function") {
                syncLocationSelects();
            }
            openLocationForm(true);
        } catch (err) {
            console.error("Lỗi load địa điểm:", err);
            showToast("Không thể tải thông tin địa điểm", "error");
        }
    }

    function openLocationForm(isEdit = false) {
        locationFormTitle.textContent = isEdit ? "Cập nhật địa điểm" : "Thêm địa điểm";
        locationFormOverlay.style.display = "flex";
        if (typeof syncLocationSelects === "function") {
            syncLocationSelects();
        }
    }

    function closeLocationForm() {
        locationFormOverlay.style.display = "none";
        resetLocationForm();
    }

    locationFormClose?.addEventListener("click", closeLocationForm);
    locationCancelBtn?.addEventListener("click", closeLocationForm);
    locationFormOverlay?.addEventListener("click", (event) => {
        if (event.target === locationFormOverlay) {
            closeLocationForm();
        }
    });

    addLocationBtn?.addEventListener("click", () => {
        resetLocationForm();
        openLocationForm(false);
    });

    addLocationSectionBtn?.addEventListener("click", () => {
        createSectionCard({});
    });

    locationSaveBtn?.addEventListener("click", async () => {
        const id = locationIdInput.value;
        const payload = {
            code: locationCodeInput.value.trim(),
            name: locationNameInput.value.trim(),
            region_id: locationRegionSelect.value,
            capacity: locationCapacityInput.value,
            area: locationAreaInput.value,
            floors: locationFloorsInput.value,
            time_start: locationTimeStartInput.value || "",
            time_end: locationTimeEndInput.value || "",
            status: locationStatusSelect.value,
            map_url: locationMapUrlInput ? locationMapUrlInput.value.trim() : ""
        };

        if (!payload.code || !payload.name || !payload.region_id) {
            showToast("Vui lòng điền đầy đủ mã, tên và khu vực", "warning");
            return;
        }

        const url = id
            ? window.routes.location.updatePattern.replace("__ID__", id)
            : window.routes.location.store;
        const formData = new FormData();

        Object.keys(payload).forEach((key) => {
            formData.append(key, payload[key]);
        });

        if (locationImageManager.getFile()) {
            formData.append("thumbnail", locationImageManager.getFile());
        } else {
            formData.append("thumbnail", locationImageManager.getPath());
        }

        formData.append("detail[summary]", detailSummaryInput.value.trim());
        formData.append("detail[intro_title]", detailIntroTitleInput.value.trim());
        formData.append("detail[intro_content]", detailIntroContentInput.value.trim());
        formData.append("detail[menu_title]", detailMenuTitleInput.value.trim());
        formData.append("detail[closing_title]", detailClosingTitleInput.value.trim());
        formData.append("detail[closing_content]", detailClosingContentInput.value.trim());
        formData.append("detail[address]", detailAddressInput.value.trim());
        formData.append("detail[hotline]", detailHotlineInput.value.trim());
        formData.append("detail[rating]", detailRatingInput.value.trim());
        formData.append("detail[review_count]", detailReviewCountInput.value.trim());
        formData.append("detail[website_url]", detailWebsiteUrlInput.value.trim());
        formData.append("detail[facebook_url]", detailFacebookUrlInput.value.trim());
        formData.append("detail[tiktok_url]", detailTiktokUrlInput.value.trim());
        formData.append("detail[booking_note]", detailBookingNoteInput.value.trim());
        formData.append("detail[parking_note]", detailParkingNoteInput.value.trim());
        formData.append("detail[open_note]", detailOpenNoteInput.value.trim());
        formData.append("detail[logo_image]", detailLogoManager.getPath());
        formData.append("detail[cover_image]", detailCoverManager.getPath());
        formData.append("detail[menu_image]", detailMenuManager.getPath());

        if (detailLogoManager.getFile()) {
            formData.append("detail_logo_image_file", detailLogoManager.getFile());
        }
        if (detailCoverManager.getFile()) {
            formData.append("detail_cover_image_file", detailCoverManager.getFile());
        }
        if (detailMenuManager.getFile()) {
            formData.append("detail_menu_image_file", detailMenuManager.getFile());
        }

        Array.from(sectionsList?.querySelectorAll(".detail-section-card") || []).forEach((card, index) => {
            const title = card.querySelector(".section-title-input")?.value.trim() || "";
            const content = card.querySelector(".section-content-input")?.value.trim() || "";
            const sortOrder = card.querySelector(".section-sort-order-input")?.value.trim() || "";
            const imageManager = card._imageManager;
            const imagePath = imageManager ? imageManager.getPath() : "";
            const imageFile = imageManager ? imageManager.getFile() : null;

            if (!title && !content && !imagePath && !imageFile) {
                return;
            }

            formData.append(`sections[${index}][title]`, title);
            formData.append(`sections[${index}][content]`, content);
            formData.append(`sections[${index}][sort_order]`, sortOrder);
            formData.append(`sections[${index}][image]`, imagePath);
            if (imageFile) {
                formData.append(`section_image_files[${index}]`, imageFile);
            }
        });

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: formData
            });
            const data = await readJsonResponse(res);
            if (res.ok && data.success) {
                showToast(id ? "Cập nhật địa điểm thành công" : "Thêm địa điểm thành công", "success");
                closeLocationForm();
                setTimeout(() => location.reload(), 600);
            } else {
                showToast(getJsonErrorMessage(data, "Không thể lưu địa điểm"), "error");
            }
        } catch (err) {
            console.error(err);
            showToast("Lỗi server", "error");
        }
    });

    applyFilters();
});
