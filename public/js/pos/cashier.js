document.addEventListener('DOMContentLoaded', () => {
    updateServicingCount();
    const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');
    
    /* ================= ELEMENT ================= */
    const tabLinks = document.querySelectorAll('.nav-tabs li a');
    const tabContents = document.querySelectorAll('.tab-content');
    const tableItems = document.querySelectorAll('.table-item');
    const menuItems = document.querySelectorAll('.menu-item');
    const selectedTableBtn = document.getElementById('selectedTableBtn');
    const transferModal = document.getElementById('transferTableModal');
    const targetTableSelect = document.getElementById('targetTableSelect');
    const orderList = document.getElementById('orderList');
    const totalPriceEl = document.getElementById('totalPrice');
    const notifyBtn = document.getElementById('notifyBtn');
    const searchInput  = document.querySelector('.search-input');
    const searchResult = document.getElementById('searchResult');
    const searchUrl    = document.querySelector('meta[name="search-product-url"]').getAttribute('content');
    const payOverlay = document.getElementById('payOverlay');
    const payDrawer = document.getElementById('payDrawer');
    const openPayBtn = document.getElementById('openPay');
    const closePayBtn = document.getElementById('closePay');
    const discountBtn = document.getElementById('discountBtn');
    const discountPopup = document.getElementById('discountPopup');
    const discountCancel = document.getElementById('discountCancel');
    const discountSave = document.getElementById('discountSave');
    const discountValue = document.getElementById('discountValue');
    const discountInput = document.getElementById('discountInput');
    const promotionBtn = document.getElementById('promotionBtn');
    const promotionPopup = document.getElementById('promotionPopup');
    const promotionList = document.getElementById('promotionList');
    const promotionIdInput = document.getElementById('promotionId');
    const payTableInfo = document.getElementById('payTableInfo');
    const payTime = document.getElementById('payTime');
    const areaLinks = document.querySelectorAll('.area-filter a');
    const categoryLinks = document.querySelectorAll('.category-bar a');
    const menuBtn = document.getElementById('menuBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const logoutLink = document.getElementById('logoutLink');
    
    const CONFIG = {
    tablePerPage: 36,
    menuPerPage: 15
    };

    let orderItems = {};
    let searchTimeout = null;
    let discountType = 'vnd';
    let discountAmount = 0;
    let promotionDiscount = 0;
    let selectedPromotion = null;
    let currentTablePage = 1;
    let currentMenuPage = 1;
    
    const typeMap = {
    Food: 'Đồ ăn',
    Drink: 'Đồ uống',
    Other: 'Khác'
    };

    function generateVietQR(amount, description = '') {
        const bankId = 'MB';
        const accountNo = '8410113801888';
        const accountName = 'NGUYEN PHUC NHAT THANH';

        const encodedDesc = encodeURIComponent(description);

        return `https://img.vietqr.io/image/${bankId}-${accountNo}-compact2.png`
            + `?amount=${amount}`
            + `&addInfo=${encodedDesc}`
            + `&accountName=${encodeURIComponent(accountName)}`;
    }

    const qrTransferBox = document.getElementById('qrTransferBox');
    const vietqrImg = document.getElementById('vietqrImg');
    const payMethodRadios = document.querySelectorAll('input[name="pay_method"]');

payMethodRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        if (radio.value === 'transfer' && radio.checked) {

            const needPayText = document.getElementById('needPay').innerText;
            const amount = Number(needPayText.replace(/[^\d]/g, ''));

            const table = getSelectedTable();
            const desc = table
                ? `ToiBenQuan-${table.name}`
                : 'ToiBenQuan';

            vietqrImg.src = generateVietQR(amount, desc);
            qrTransferBox.style.display = 'block';

        } else {
            qrTransferBox.style.display = 'none';
        }
    });
});

    /* ================= DROPDOWN ================= */
    menuBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
    });

    document.addEventListener('click', function() {
        dropdownMenu.classList.remove('show');
    });

    if (logoutLink) {
        logoutLink.addEventListener('click', async function(e) {
            e.preventDefault();
            if (await openConfirmDialog('Bạn có chắc chắn muốn đăng xuất?')) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    /* ================= STT ORDER ================= */
    function nextOrderNo() {
        let orderNo = parseInt(localStorage.getItem('pos_order_no') || 0);
        orderNo++;
        localStorage.setItem('pos_order_no', orderNo);
        return `#${orderNo}`;
    }

    function updatePaginationUI(containerId, totalItems, itemsPerPage, currentPage, onPageChange) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const totalPages = Math.ceil(totalItems / itemsPerPage) || 1;
        const prevBtn = container.querySelector('.prev-btn');
        const nextBtn = container.querySelector('.next-btn');
        const pageInfo = container.querySelector('.page-info');

        if (pageInfo) {
            pageInfo.innerText = `Trang ${currentPage} / ${totalPages}`;
        }

        prevBtn.disabled = (currentPage === 1);
        nextBtn.disabled = (currentPage === totalPages);

        prevBtn.onclick = () => {
            if (currentPage > 1) onPageChange(currentPage - 1);
        };
        nextBtn.onclick = () => {
            if (currentPage < totalPages) onPageChange(currentPage + 1);
        };

        container.style.display = totalPages <= 1 ? 'none' : 'flex';
    }

    function paginateTables() {
        const allTables = Array.from(tableItems);
        const filtered = allTables.filter(t => t.getAttribute('data-filtered') !== 'hidden');
        
        const start = (currentTablePage - 1) * CONFIG.tablePerPage;
        const end = start + CONFIG.tablePerPage;

        allTables.forEach(t => t.style.setProperty('display', 'none', 'important'));

        filtered.forEach((table, index) => {
            if (index >= start && index < end) {
                table.style.setProperty('display', 'block', 'important');
            }
        });

        updatePaginationUI('pagination-tables', filtered.length, CONFIG.tablePerPage, currentTablePage, (newPage) => {
            currentTablePage = newPage;
            paginateTables();
        });
    }

    function paginateMenu() {
        const allMenus = Array.from(menuItems);
        const filtered = allMenus.filter(m => m.getAttribute('data-filtered') !== 'hidden');
        
        const start = (currentMenuPage - 1) * CONFIG.menuPerPage;
        const end = start + CONFIG.menuPerPage;

        allMenus.forEach(m => m.style.setProperty('display', 'none', 'important'));

        filtered.forEach((item, index) => {
            if (index >= start && index < end) {
                item.style.setProperty('display', 'block', 'important');
            }
        });

        updatePaginationUI('pagination-menu', filtered.length, CONFIG.menuPerPage, currentMenuPage, (newPage) => {
            currentMenuPage = newPage;
            paginateMenu();
        });
    }

    /* ================= FILTER AREA ================= */
    areaLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const areaId = link.getAttribute('data-area');
            areaLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            tableItems.forEach(table => {
                if (areaId === 'all' || table.dataset.areaId === areaId) {
                    table.setAttribute('data-filtered', 'visible');
                } else {
                    table.setAttribute('data-filtered', 'hidden');
                }
            });
            currentTablePage = 1;
            paginateTables();
        });
    });

    /* ================= FILTER CATEGORY ================= */
    categoryLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const catId = link.getAttribute('data-category');
            categoryLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            menuItems.forEach(item => {
                if (catId === 'all' || item.dataset.category === catId) {
                    item.setAttribute('data-filtered', 'visible');
                } else {
                    item.setAttribute('data-filtered', 'hidden');
                }
            });
            currentMenuPage = 1;
            paginateMenu();
        });
    });

    /* ================= DEFAULT TAB ================= */
    function showTab(tabName) {
        tabLinks.forEach(link => {
            link.parentElement.classList.remove('active');
        });

        tabContents.forEach(tab => {
            tab.classList.remove('active');
        });
        const activeLink = document.querySelector(`.nav-tabs li a[data-tab="${tabName}"]`);
        if (activeLink) {
            activeLink.parentElement.classList.add('active');
        }

        const activeContent = document.getElementById(`tab-${tabName}`);
        if (activeContent) {
            activeContent.classList.add('active');
        }
    }
    showTab('tables');

    /* ================= CLICK TAB ================= */
    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const tabName = link.dataset.tab;
            showTab(tabName);
        });
    });

    /* ================= SELECT TABLE ================= */
    function setSelectedTable(table) {
        tableItems.forEach(t => t.classList.remove('active'));
        table.classList.add('active');

        const name = table.dataset.name;
        const areaName = table.dataset.areaName;
        const tableId = table.dataset.id;
        const startKey = `order_start_${tableId}`;
        if (!localStorage.getItem(startKey)) {
            localStorage.setItem(startKey, new Date().toISOString());
        }
        
        selectedTableBtn.textContent = `${name} / ${areaName}`;
        selectedTableBtn.disabled = false;
        selectedTableBtn.classList.add('active');

        localStorage.setItem('selectedTable', JSON.stringify({
            id: tableId,
            name,
            areaName
        }));

        loadOrder();   
        updateTableStatus();
        renderOrderList();
        
    }

    function restoreSelectedTable() {
        const saved = localStorage.getItem('selectedTable');
        if (!saved) return;
        const data = JSON.parse(saved);

        tableItems.forEach(table => {
            if (table.dataset.id === data.id) {
                table.classList.add('active');
                selectedTableBtn.textContent = `${data.name} / ${data.areaName}`;
                selectedTableBtn.disabled = false;
                selectedTableBtn.classList.add('active');
            }
        });
    }

    /* ================= CLICK TABLE ================= */
    tableItems.forEach(table => {
        table.addEventListener('click', () => {
            setSelectedTable(table);
        });
    });

    /* ================= CLICK BUTTON ĐỂ MỞ FORM ĐỔI BÀN ================= */
        selectedTableBtn.addEventListener('click', () => {
            if (!selectedTableBtn.classList.contains('active')) return;
            if (Object.keys(orderItems).length === 0) {
                showToast('Bàn chưa có món, bạn phải chọn bàn có món để đổi bàn.', 'warning');
                return;
            }

            const saved = JSON.parse(localStorage.getItem('selectedTable'));
            document.getElementById('currentTableName').textContent = `${saved.name} / ${saved.areaName}`;
            
            transferModal.style.display = 'block';
        });

        // Đóng modal
        document.querySelector('.close-modal').onclick = () => transferModal.style.display = 'none';
        document.getElementById('cancelTransfer').onclick = () => transferModal.style.display = 'none';

        /* ================= XỬ LÝ CẬP NHẬT (ĐỔI BÀN) ================= */
        document.getElementById('confirmTransfer').addEventListener('click', () => {
            const targetOption = targetTableSelect.options[targetTableSelect.selectedIndex];
            const targetTableId = targetTableSelect.value;

            if (!targetTableId) {
                showToast('Vui lòng chọn bàn muốn chuyển đến!', 'warning');
                return;
            }

            const currentData = JSON.parse(localStorage.getItem('selectedTable'));
            const currentTableId = currentData.id;

            if (currentTableId == targetTableId) {
                showToast('Bạn đang chọn trùng bàn hiện tại!', 'warning');
                return;
            }

            const oldKey = `order_${currentTableId}`;
            const currentItems = JSON.parse(localStorage.getItem(oldKey)) || {};

            const newKey = `order_${targetTableId}`;
            let targetItems = JSON.parse(localStorage.getItem(newKey)) || {};

            Object.keys(currentItems).forEach(id => {
                if (targetItems[id]) {
                    targetItems[id].qty += currentItems[id].qty;
                } else {
                    targetItems[id] = currentItems[id];
                }
            });

            localStorage.setItem(newKey, JSON.stringify(targetItems));
            localStorage.removeItem(oldKey);
            localStorage.removeItem(`order_start_${currentTableId}`);

            const newTableElement = document.querySelector(`.table-item[data-id="${targetTableId}"]`);
            if (newTableElement) {
                setSelectedTable(newTableElement);
            }

            transferModal.style.display = 'none';
            showToast('Đổi bàn thành công!', 'success');
        });

    /* ================= RESTORE ON LOAD ================= */
    restoreSelectedTable();

    function restoreSelectedTableBtn() {
        const saved = localStorage.getItem('selectedTable');
        if (!saved) return;
        const data = JSON.parse(saved);

        selectedTableBtn.textContent = `${data.name} / ${data.areaName}`;
        selectedTableBtn.disabled = false;
        selectedTableBtn.classList.add('active');
    }
    restoreSelectedTableBtn();

    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab') || 'tables';

    document.querySelectorAll('.nav-tabs li').forEach(li => li.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));

    const activeTab = document.querySelector(`.nav-tabs a[data-tab="${tab}"]`);
    if (activeTab) activeTab.parentElement.classList.add('active');

    const activeContent = document.getElementById(`tab-${tab}`);
    if (activeContent) activeContent.classList.add('active');

    /* ================= RENDER ORDER LIST ================= */
    function formatPrice(num) {
        return Number(num || 0).toLocaleString('vi-VN');
    }

    function getSelectedTable() {
        return JSON.parse(localStorage.getItem('selectedTable'));
    }

    function getOrderKey() {
        const table = JSON.parse(localStorage.getItem('selectedTable'));
        return table ? `order_${table.id}` : null;
    }

    function saveOrder() {
        const key = getOrderKey();
        if (key) {
            localStorage.setItem(key, JSON.stringify(orderItems));
        }
    }

    function loadOrder() {
        const table = getSelectedTable();
        if (!table) return;

        const key = `order_${table.id}`;
        const startKey = `order_start_${table.id}`;
        if (localStorage.getItem(key)) {
            orderItems = JSON.parse(localStorage.getItem(key));
            if (
                Object.keys(orderItems).length > 0 &&
                !localStorage.getItem(startKey)
            ) {
                localStorage.setItem(startKey, new Date().toISOString());
            }
        } else {
            orderItems = {};
        }
    }

    /* ================= SEARCH PRODUCT ================= */
    searchInput.addEventListener('input', () => {
        const keyword = searchInput.value.trim();
        if (!keyword) {
            searchResult.style.display = 'none';
            searchResult.innerHTML = '';
            return;
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || !data.length) {
                        searchResult.innerHTML = `
                            <div class="search-item">
                                <span>Không tìm thấy món</span>
                            </div>
                        `;
                        searchResult.style.display = 'block';
                        return;
                    }
                    searchResult.innerHTML = data.map(p => `
                        <div class="search-item"
                            data-id="${p.id}"
                            data-name="${p.name}"
                            data-unit="${p.unit}"
                            data-price="${p.price}">
                            <strong>${p.name}</strong>
                            <span>${p.unit} • ${formatPrice(p.price)}</span>
                        </div>
                    `).join('');
                    searchResult.style.display = 'block';
                });
        }, 300);
    });

    searchResult.addEventListener('click', (e) => {
        const item = e.target.closest('.search-item');
        if (!item) return;
        addProduct({
            id: item.dataset.id,
            name: item.dataset.name,
            unit: item.dataset.unit,
            price: Number(item.dataset.price),
        });

        searchInput.value = '';
        searchResult.style.display = 'none';
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-box')) {
            searchResult.style.display = 'none';
        }
    });

    /* ================= NOTIFY BUTTON ================= */
    function updateNotifyBtn(hasOrder) {
        if (!notifyBtn) return;
        if (hasOrder) {
            notifyBtn.classList.add('has-order');
        } else {
            notifyBtn.classList.remove('has-order');
        }
    }

    /* ================= RENDER ================= */
    let hadItemsBefore = false;

    function renderOrderList() {

        orderList.innerHTML = '';
        const items = Object.values(orderItems);
        if (items.length === 0) {
            if (hadItemsBefore) {
                removeServing(getSelectedTable().id);
                updateServicingCount();
            }
            hadItemsBefore = false;
            orderList.innerHTML = `
                <p class="empty">
                    Chưa có món trong đơn<br>
                    Vui lòng chọn món trong thực đơn bên trái màn hình
                </p>
            `;
            updateNotifyBtn(false);
            updateTotal();
            return;
        }
        hadItemsBefore = true;

        items.forEach((item, index) => {
            const itemTotal = item.qty * item.price;
            const div = document.createElement('div');
            div.className = 'order-item';
            div.dataset.id = item.id;
            div.innerHTML = `
                <div class="oi-stt">${index + 1}</div>
                <div class="oi-name">
                    <strong>${item.name}</strong>
                    <small>${item.unit}</small>
                </div>
                <div class="oi-qty">
                    <button class="btn-minus">−</button>
                    <input type="text" value="${item.qty}" readonly>
                    <button class="btn-plus">+</button>
                </div>
                <div class="oi-price">${formatPrice(item.price)}</div>
                <div class="oi-total">${formatPrice(itemTotal)}</div>
                <div class="oi-remove">
                    <button title="Xóa món">×</button>
                </div>
            `;

            div.querySelector('.btn-plus').onclick = () => {
                item.qty++;
                saveOrder();
                updateTableStatus();
                renderOrderList();
            };

            div.querySelector('.btn-minus').onclick = () => {
                if (item.qty > 1) {
                    item.qty--;
                } else {
                    delete orderItems[item.id];
                }
                saveOrder();
                updateTableStatus();
                renderOrderList();
            };

            div.querySelector('.oi-remove button').onclick = () => {
                delete orderItems[item.id];
                saveOrder();
                updateTableStatus();
                renderOrderList();
            };
            orderList.appendChild(div);
        });
        updateNotifyBtn(true);
        updateTotal();
    }

    function updateTableStatus() {
        tableItems.forEach(table => {
            const key = `order_${table.dataset.id}`;
            const hasOrder =
                localStorage.getItem(key) &&
                Object.keys(JSON.parse(localStorage.getItem(key))).length > 0;
            table.classList.toggle('using', hasOrder);
        });
    }

    function updateTotal() {
        let sum = 0;
        Object.values(orderItems).forEach(item => {
            sum += item.qty * item.price;
        });
        totalPriceEl.textContent = formatPrice(sum);
    }

    async function startServingIfNeeded(tableId) {
        try {
            const res = await fetch(`${APP_URL}/pos/cashier/start-serving`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ table_id: tableId })
            });

            const data = await res.json();

            if (data.ok) {
                updateServicingCount();
            }
        } catch (e) {
            console.error('Error starting service:', e);
        }
    }

    async function removeServing(tableId) {
        await fetch(`${BASE_URL}/pos/cashier/remove-serving`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ table_id: tableId })
        });
    }

    async function updateServicingCount() {
        try {
            const res = await fetch(`${BASE_URL}/pos/cashier/servicing-count`, {
                credentials: 'same-origin'
            });
            const data = await res.json();
            const el = document.getElementById('servicing');
            if (el) el.textContent = data.count;
        } catch (e) {
            console.error('Không lấy được số bàn đang phục vụ', e);
        }
    }

    /* ================= ADD PRODUCT ================= */
    function addProduct(product) {
        const table = getSelectedTable();
        if (!table) {
            showToast('Vui lòng chọn bàn trước khi chọn món', 'warning');
            return;
        }
        startServingIfNeeded(table.id);
        if (orderItems[product.id]) {
            orderItems[product.id].qty++;
        } else {
            orderItems[product.id] = {
                id: product.id,
                name: product.name,
                unit: product.unit,
                price: product.price,
                qty: 1,
                type: product.type_menu
            };
        }
        saveOrder();
        updateTableStatus();
        renderOrderList();
    }

    /* ================= CLICK MENU ITEM ================= */
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', () => {
            addProduct({
                id: item.dataset.id,
                name: item.querySelector('h4').textContent,
                unit: item.dataset.unit,
                price: Number(item.dataset.price),
                type_menu: item.dataset.type
            });
        });
    });

    function getUrlParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    async function checkBookingFromUrl() {
        const bookingId = getUrlParam('booking_id');
        const tableId = getUrlParam('table_id');

        // 1. Tự động chọn bàn
        if (tableId) {
            const tableElement = document.querySelector(`.table-item[data-id="${tableId}"]`);
            if (tableElement) {
                setSelectedTable(tableElement);
                console.log("Hệ thống đã chọn bàn:", tableId);
            }
        }
        if (bookingId) {
            try {
                const apiUrl = typeof BASE_URL !== 'undefined' ? `${BASE_URL}/pos/api/booking-items/${bookingId}` : `/pos/api/booking-items/${bookingId}`;
                
                const res = await fetch(apiUrl);
                const data = await res.json();

                if (data.success && data.items.length > 0) {
                    orderItems = {}; 

                    data.items.forEach(item => {
                        const pId = item.product_id || item.id; 
                        orderItems[pId] = {
                            id: pId,
                            name: item.product_name || item.name,
                            qty: parseInt(item.qty) || 1,
                            price: parseFloat(item.price) || 0,
                            unit: item.unit || 'Món',
                            type: item.type_menu || 'Food'
                        };
                    });
                    saveOrder();
                    renderOrderList(); 
                    updateTotal();
                    updateTableStatus();
                    
                    console.log("Đã nạp món thành công từ booking:", bookingId);
                    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                    window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                }
            } catch (err) {
                console.error("Lỗi khi fetch booking items:", err);
            }
        }
    }

    /* ================= BOOKING TAB HIGHLIGHT ================= */
    function updateBookingHighlight() {
        const cards = document.querySelectorAll('.booking-card[data-booking-time]');
        if (!cards.length) return;
        const now = new Date();
        cards.forEach(card => {
            const timeValue = card.dataset.bookingTime;
            if (!timeValue) return;
            const bookingTime = new Date(timeValue);
            if (Number.isNaN(bookingTime.getTime())) return;
            const diff = bookingTime.getTime() - now.getTime();
            if (diff >= 0 && diff <= 30 * 60 * 1000) {
                card.classList.add('soon');
            } else {
                card.classList.remove('soon');
            }
        });
    }

    /* ================= INIT ================= */
    loadOrder();
    updateTableStatus();
    renderOrderList();
    updateServicingCount();
    checkBookingFromUrl();
    updateBookingHighlight();
    setInterval(updateBookingHighlight, 60000);
    paginateTables();
    paginateMenu();

    /* ================= PAY ================= */
    openPayBtn.addEventListener('click', openPayDrawer);
    closePayBtn.addEventListener('click', closePayDrawer);
    payOverlay.addEventListener('click', closePayDrawer);
    function openPayDrawer() {
        const table = getSelectedTable();
        if (!table) {
            showToast('Vui lòng chọn bàn trước khi thanh toán', 'warning');
            return;
        }
        payTableInfo.textContent = `${table.name} / ${table.areaName}`;
        const now = new Date();
        payTime.textContent = now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
        payOverlay.style.display = 'block';
        payDrawer.classList.add('show');
        renderPayOrder();
        document.getElementById('orderNo').innerText =
        `#${parseInt(localStorage.getItem('pos_order_no') || 0) + 1}`;
        document.querySelector('input[name="pay_method"][value="cash"]').checked = true;
        qrTransferBox.style.display = 'none';
    }

    function closePayDrawer() {
        payDrawer.classList.remove('show');
        setTimeout(() => {
            payOverlay.style.display = 'none';
        }, 300);
    }

    function updatePayAmount() {
        const total = Object.values(orderItems)
            .reduce((s, i) => s + i.qty * i.price, 0);

        let discount = 0;

        if (promotionDiscount > 0) {
            discount = promotionDiscount;
        } else {
            discount = Number(discountInput.value || 0);
        }

        discount = Math.min(discount, total);

        document.getElementById('sumPrice').textContent = formatPrice(total);
        document.getElementById('needPay').textContent = formatPrice(total - discount);
        const transferRadio = document.querySelector('input[name="pay_method"][value="transfer"]');
        if (transferRadio.checked) {
            const amount = Math.max(total - discount, 0);
            const table = getSelectedTable();
            const desc = table ? `ToiBenQuan-${table.name}` : 'ToiBenQuan';
            vietqrImg.src = generateVietQR(amount, desc);
        }
    }

    function renderPayOrder() {
        const payOrderList = document.getElementById('payOrderList');
        const sumPriceEl = document.getElementById('sumPrice');
        const needPayEl = document.getElementById('needPay');
        const discountInput = document.getElementById('discountInput');
        payOrderList.innerHTML = '';

        let sum = 0;
        const groups = {};
        Object.values(orderItems).forEach(item => {
            const type = typeMap[item.type] || 'Khác';

            if (!groups[type]) {
                groups[type] = [];
            }
            groups[type].push(item);
        });
        Object.keys(groups).forEach(type => {
            const groupDiv = document.createElement('div');
            groupDiv.className = 'pay-group';
            // title
            groupDiv.innerHTML = `
                <div class="group-title">${type}</div>
            `;
            groups[type].forEach(item => {
                const total = item.qty * item.price;
                sum += total;
                groupDiv.innerHTML += `
                    <div class="pay-item">
                        <div class="name">
                            <strong>${item.name}</strong>
                            <small>${item.unit}</small>
                        </div>
                        <div class="qty">${item.qty}</div>
                        <div class="price">${formatPrice(item.price)}</div>
                        <div class="total">${formatPrice(total)}</div>
                    </div>
                `;
            });
            payOrderList.appendChild(groupDiv);
        });
        sumPriceEl.textContent = formatPrice(sum);
        needPayEl.textContent = formatPrice(sum);
        updatePayAmount();

        discountInput.oninput = () => {
            const discount = Number(discountInput.value || 0);
            const needPay = Math.max(sum - discount, 0);
            needPayEl.textContent = formatPrice(needPay);
        };
    }
    discountBtn.addEventListener('click', () => {
        discountPopup.style.display = 'flex';
    });
    discountPopup.querySelectorAll('.discount-type button').forEach(btn => {
        btn.addEventListener('click', () => {
            discountPopup.querySelectorAll('.discount-type button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            discountType = btn.dataset.type;
            discountValue.value = '';
        });
    });

    discountCancel.addEventListener('click', () => {
        discountPopup.style.display = 'none';
    });

    discountSave.addEventListener('click', () => {
        const totalSum = Object.values(orderItems)
            .reduce((s, i) => s + i.qty * i.price, 0);
        let val = Number(discountValue.value || 0);
        if (discountType === 'percent') {
            if (val > 100) val = 100;
            discountAmount = Math.round(totalSum * val / 100);
            discountBtn.textContent = `${discountAmount.toLocaleString()}₫`;
        } else {
            discountAmount = Math.min(val, totalSum);
            discountBtn.textContent = `${discountAmount.toLocaleString()}₫`;
        }
        discountInput.value = discountAmount;
        const needPayEl = document.getElementById('needPay');
        needPayEl.textContent = formatPrice(totalSum - discountAmount);
        discountPopup.style.display = 'none';

        promotionDiscount = 0;
        selectedPromotion = null;
        promotionIdInput.value = '';
        promotionBtn.textContent = 'Chọn KM';

        discountInput.value = discountAmount;
        updatePayAmount();
    });

    promotionBtn.addEventListener('click', async () => {
        promotionPopup.style.display = 'flex';

        if (promotionList.children.length) return;

        try {
            const res = await fetch('/VNT-Restaurant/public/pos/promotions/available', {
                headers: { 'Accept': 'application/json' }
            });

            const resData = await res.json();

            if (!resData.success || !Array.isArray(resData.data)) {
                showToast('Không có chương trình khuyến mãi', 'warning');
                return;
            }

            renderPromotions(resData.data);

        } catch (e) {
            console.error(e);
            showToast(res.message || 'Không tải được chương trình', 'error');
        }
    });


    function renderPromotions(promotions) {
        promotionList.innerHTML = '';

        promotions.forEach(p => {
            let label = '';

            if (p.type === 'percent') {
                label = `Giảm ${p.discount}%`;
            } 
            else if (p.type === 'amount') {
                label = `Giảm ${Number(p.discount).toLocaleString()}₫`;
            } 
            else if (p.type_code === 'gift') {
                label = '🎁 Tặng món';
            }

            const div = document.createElement('div');
            div.className = 'promotion-item';
            div.innerHTML = `
                <strong>${p.name}</strong>
                <div>${label}</div>
                <small>${p.description || ''}</small>
            `;

            div.onclick = () => applyPromotion(p);
            promotionList.appendChild(div);
        });
    }

    function applyPromotion(promo) {
        selectedPromotion = promo;

        const total = Object.values(orderItems)
            .reduce((s, i) => s + i.qty * i.price, 0);

        let discount = 0;

        if (promo.type === 'percent') {
            discount = Math.round(total * promo.discount / 100);
        } 
        else if (promo.type === 'amount') {
            discount = Math.min(promo.discount, total);
        }

        promotionDiscount = discount;
        discountAmount = 0;

        discountInput.value = discount;
        promotionBtn.textContent = promo.name;
        promotionIdInput.value = promo.id;

        updatePayAmount();
        promotionPopup.style.display = 'none';
    }

    document.getElementById('promotionCancel').addEventListener('click', () => {
        promotionPopup.style.display = 'none';
    });

    document.querySelector('.btn-confirm-pay').addEventListener('click', () => {
        const table = getSelectedTable();
        if (!table) {
            showToast('Chưa chọn bàn', 'warning');
            return;
        }
        const currentTableId = table.id;
        const startTime = localStorage.getItem(`order_start_${currentTableId}`);
        if (!startTime) {
            showToast('Không tìm thấy thời gian bắt đầu', 'warning');
            return;
        }
        const items = Object.values(orderItems).map(item => ({
            product_id: item.id,
            qty: item.qty,
            price: item.price
        }));
        if (!items.length) {
            showToast('Chưa có món', 'warning');
            return;
        }
        function clearTableUI(tableId) {
            tableItems.forEach(t => {
                if (t.dataset.id == tableId) {
                    t.classList.remove('active', 'using');
                }
            });
            selectedTableBtn.textContent = 'Chưa chọn bàn';
            selectedTableBtn.disabled = true;
            selectedTableBtn.classList.remove('active');
            localStorage.removeItem('selectedTable');
        }
        function getPayMethod() {
            const radio = document.querySelector('input[name="pay_method"]:checked');
            return radio ? radio.value : null;
        }
        function showStockError(items) {
            items.forEach(item => {
                showToast(`${item.name} không đủ tồn kho, hiện còn ${item.current_qty}`, 'error');
            });
        }
        const total = Object.values(orderItems).reduce((sum, item) => sum + item.qty * item.price, 0);
        const discount = promotionDiscount > 0
            ? promotionDiscount
            : discountAmount;
        const payAmount = Math.max(total - discount, 0);
        const paymentMethod = getPayMethod();
            if (!paymentMethod) {
                showToast('Vui lòng chọn phương thức thanh toán', 'warning');
                return;
            }
        const checkoutUrl = document
        .querySelector('meta[name="checkout-url"]')
        .getAttribute('content');
        fetch(checkoutUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                table_id: currentTableId,
                time_start: startTime,
                items: items,
                total: total,
                discount: discount,
                pay_amount: payAmount,
                payment_method: paymentMethod,
                promotion_id: selectedPromotion ? selectedPromotion.id : null
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
               showToast('Thanh toán thành công', 'success');
                const orderCode = nextOrderNo();
                document.getElementById('orderNo').innerText = orderCode;

                localStorage.removeItem(`order_${currentTableId}`);
                localStorage.removeItem(`order_start_${currentTableId}`);
                orderItems = {};
                discountInput.value = 0;
                discountAmount = 0;
                discountBtn.textContent = 'Chọn giảm giá';
                promotionDiscount = 0;
                selectedPromotion = null;
                promotionIdInput.value = '';
                promotionBtn.textContent = 'Chọn KM';

                renderOrderList();
                updateTableStatus();
                removeServing(currentTableId)
                    .then(() => updateServicingCount())
                    .catch(err => console.error('Không thể cập nhật số lượng bàn đang phục vụ', err));
                closePayDrawer();
                clearTableUI(currentTableId);
            } else {
                if (res.insufficient_stock) {
                    showStockError(res.insufficient_stock);
                } else {
                    showToast(res.message || 'Lỗi thanh toán', 'error');
                }
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi server', 'error');
        });
    });
    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.addEventListener('change', () => {
            filterTablesByStatus(radio.value);
        });
    });
    function filterTablesByStatus(status) {
        tableItems.forEach(table => {
            const isUsing = table.classList.contains('using');

            if (status === 'all') {
                table.setAttribute('data-filtered', 'visible');
            }
            else if (status === 'active') {
                table.setAttribute('data-filtered', isUsing ? 'visible' : 'hidden');
            }
            else if (status === 'inactive') {
                table.setAttribute('data-filtered', !isUsing ? 'visible' : 'hidden');
            }
        });

        currentTablePage = 1;
        paginateTables();
    }

    
});

