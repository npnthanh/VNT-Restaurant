window.filters = {
    code: '',
    product: '',
    status: 'all',
    from: null,
    to: null,
    payment: [],
    area: '',
    table: ''
};

const BASE_URL = document
    .querySelector('meta[name="base-url"]')
    ?.getAttribute('content') || '';
const CSRF_TOKEN = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content') || '';

document.addEventListener('DOMContentLoaded', () => {
    setupCustomDropdown('areaDropdown', 'filter-area', 'currentAreaText', 'area', {
        otherInputId: 'filter-table',
        otherTextId: 'currentTableText',
        otherFilterKey: 'table',
        otherDefaultText: 'Chọn phòng/bàn'
    });

    setupCustomDropdown('tableDropdown', 'filter-table', 'currentTableText', 'table', {
        otherInputId: 'filter-area',
        otherTextId: 'currentAreaText',
        otherFilterKey: 'area',
        otherDefaultText: 'Chọn khu vực'
    });

    const btn = document.getElementById('timeBtn');
    const menu = document.getElementById('timeMenu');

    btn.addEventListener('click', e => {
        e.stopPropagation();
        btn.parentElement.classList.toggle('open');
    });

    menu.addEventListener('click', e => {
        e.stopPropagation();
    });

    document.addEventListener('click', e => {
        if (!btn.parentElement.contains(e.target)) {
            btn.parentElement.classList.remove('open');
        }
    });

    document.querySelectorAll('.time-item').forEach(item => {
        item.addEventListener('click', () => {
            const preset = item.dataset.preset;
            applyPreset(preset);
            btn.innerText = item.innerText;
            btn.parentElement.classList.remove('open');
        });
    });

    function applyPreset(preset) {
        const now = new Date();
        let from = null;
        let to = null;

        const startOfDay = date => new Date(date.setHours(0, 0, 0, 0));
        const endOfDay = date => new Date(date.setHours(23, 59, 59, 999));

        switch (preset) {
            case 'today':
                from = startOfDay(new Date());
                to = endOfDay(new Date());
                break;

            case 'yesterday': {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                from = startOfDay(new Date(yesterday));
                to = endOfDay(new Date(yesterday));
                break;
            }

            case 'this_week': {
                const firstDayOfWeek = new Date();
                const day = firstDayOfWeek.getDay() || 7;
                firstDayOfWeek.setDate(firstDayOfWeek.getDate() - day + 1);
                from = startOfDay(firstDayOfWeek);
                to = new Date();
                break;
            }

            case 'last_week': {
                const lastWeekStart = new Date();
                lastWeekStart.setDate(lastWeekStart.getDate() - lastWeekStart.getDay() - 6);
                from = startOfDay(lastWeekStart);

                const lastWeekEnd = new Date(lastWeekStart);
                lastWeekEnd.setDate(lastWeekEnd.getDate() + 6);
                to = endOfDay(lastWeekEnd);
                break;
            }

            case 'last_7_days':
                from = startOfDay(new Date(now.setDate(now.getDate() - 7)));
                to = new Date();
                break;

            case 'this_month':
                from = new Date(now.getFullYear(), now.getMonth(), 1);
                to = new Date();
                break;

            case 'last_month':
                from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                to = new Date(now.getFullYear(), now.getMonth(), 0, 23, 59, 59);
                break;

            case 'last_30_days':
                from = startOfDay(new Date(now.setDate(now.getDate() - 30)));
                to = new Date();
                break;

            case 'this_year':
                from = new Date(now.getFullYear(), 0, 1);
                to = new Date();
                break;

            case 'last_year':
                from = new Date(now.getFullYear() - 1, 0, 1);
                to = new Date(now.getFullYear() - 1, 11, 31, 23, 59, 59);
                break;

            case 'all':
                from = null;
                to = null;
                break;
        }

        window.filters.from = from ? Math.floor(from.getTime() / 1000) : null;
        window.filters.to = to ? Math.floor(to.getTime() / 1000) : null;
        applyFilters();
    }
});

function setupCustomDropdown(dropdownId, hiddenInputId, textSpanId, filterKey, resetLogic = null) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;

    const display = dropdown.querySelector('.selected-display');
    const items = dropdown.querySelectorAll('.dropdown-list li');
    const hiddenInput = document.getElementById(hiddenInputId);
    const textSpan = document.getElementById(textSpanId);

    display.addEventListener('click', e => {
        e.stopPropagation();

        const isOpening = !dropdown.classList.contains('active');

        document.querySelectorAll('.custom-dropdown').forEach(item => {
            if (item !== dropdown) item.classList.remove('active');
        });

        if (isOpening) {
            const rect = dropdown.getBoundingClientRect();
            const listHeight = 250;
            const spaceBelow = window.innerHeight - rect.bottom;

            if (spaceBelow < listHeight && rect.top > listHeight) {
                dropdown.classList.add('drop-up');
            } else {
                dropdown.classList.remove('drop-up');
            }

            dropdown.classList.add('active');
        } else {
            dropdown.classList.remove('active');
        }
    });

    items.forEach(item => {
        item.addEventListener('click', e => {
            e.stopPropagation();

            const value = item.getAttribute('data-value');
            const text = item.innerText;

            textSpan.innerText = text;
            hiddenInput.value = value;
            window.filters[filterKey] = value;

            if (resetLogic) {
                const otherInput = document.getElementById(resetLogic.otherInputId);
                const otherText = document.getElementById(resetLogic.otherTextId);

                if (otherInput) otherInput.value = '';
                if (otherText) otherText.innerText = resetLogic.otherDefaultText;
                window.filters[resetLogic.otherFilterKey] = '';
            }

            dropdown.classList.remove('active');

            if (typeof applyFilters === 'function') applyFilters();
        });
    });
}

function applyFilters() {
    document.querySelectorAll('.invoice-row').forEach(row => {
        let match = true;

        if (filters.code) {
            match = row.dataset.code.includes(filters.code);
        }

        if (match && filters.product) {
            const detail = document.getElementById(`detail-${row.dataset.id}`);
            const text = detail?.innerText.toLowerCase() || '';
            match = text.includes(filters.product);
        }

        if (match && filters.status !== 'all') {
            match = row.dataset.status === filters.status;
        }

        if (match && filters.from && filters.to && row.dataset.time) {
            const timestamp = Number(row.dataset.time);
            match = timestamp >= filters.from && timestamp <= filters.to;
        }

        if (match && filters.payment.length > 0) {
            match = filters.payment.includes(row.dataset.payment);
        }

        if (match && filters.area) {
            match = row.dataset.areaId === filters.area;
        }

        if (match && filters.table) {
            match = row.dataset.tableId === filters.table;
        }

        row.dataset.filtered = match ? '1' : '0';
        row.style.display = match ? '' : 'none';

        const detail = document.getElementById(`detail-${row.dataset.id}`);
        if (detail) detail.style.display = 'none';
    });

    currentPage = 1;
    renderPagination();
}

document.querySelectorAll('.search-input')[0].addEventListener('input', e => {
    filters.code = e.target.value.trim().toLowerCase();
    applyFilters();
});

document.querySelectorAll('.search-input')[1].addEventListener('input', e => {
    filters.product = e.target.value.trim().toLowerCase();
    applyFilters();
});

document.querySelectorAll('input[name="status"]').forEach(radio => {
    radio.addEventListener('change', e => {
        filters.status = e.target.value;
        applyFilters();
    });
});

$(function () {
    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Áp dụng',
            cancelLabel: 'Hủy',
            fromLabel: 'Từ',
            toLabel: 'Đến',
            customRangeLabel: 'Tùy chọn',
            daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            monthNames: [
                'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
                'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
                'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
            ]
        }
    });

    $('#dateRange').on('apply.daterangepicker', function (_event, picker) {
        const from = picker.startDate.startOf('day');
        const to = picker.endDate.endOf('day');

        $(this).val(
            picker.startDate.format('DD/MM/YYYY') +
            ' - ' +
            picker.endDate.format('DD/MM/YYYY')
        );

        filters.from = from.unix();
        filters.to = to.unix();
        currentPage = 1;
        applyFilters();
    });

    $('#dateRange').on('cancel.daterangepicker', function () {
        $(this).val('');
        filters.from = null;
        filters.to = null;
        currentPage = 1;
        applyFilters();
    });
});

function getSelectedPayments() {
    return Array.from(document.querySelectorAll('input[name="payment"]:checked'))
        .map(checkbox => checkbox.value);
}

document.querySelectorAll('input[name="payment"]').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        filters.payment = getSelectedPayments();
        applyFilters();
    });
});

document.querySelectorAll('.box.collapsible').forEach(box => {
    const title = box.querySelector('.box-title');
    if (!title) return;

    title.addEventListener('click', e => {
        if (e.target.closest('.custom-dropdown') || e.target.closest('.time-dropdown')) {
            return;
        }

        box.classList.toggle('collapsed');
    });
});

let sumMoney = 0;
let sumDiscount = 0;
let sumFinal = 0;

document.querySelectorAll('.invoice-row').forEach(row => {
    const money = Number(row.querySelector('.money')?.dataset.value || 0);
    const discount = Number(row.querySelector('.discount')?.dataset.value || 0);
    const finalPay = Number(row.querySelector('.final')?.dataset.value || 0);

    sumMoney += money;
    sumDiscount += discount;
    sumFinal += finalPay;
});

const format = value => value.toLocaleString('vi-VN');

const sumMoneyEl = document.getElementById('sum-money');
const sumDiscountEl = document.getElementById('sum-discount');
const sumFinalEl = document.getElementById('sum-final');

if (sumMoneyEl) sumMoneyEl.textContent = format(sumMoney);
if (sumDiscountEl) sumDiscountEl.textContent = format(sumDiscount);
if (sumFinalEl) sumFinalEl.textContent = format(sumFinal);

document.querySelectorAll('.invoice-row').forEach(row => {
    row.addEventListener('click', () => {
        const id = row.dataset.id;
        const detailRow = document.getElementById(`detail-${id}`);
        const isActive = row.classList.contains('active');

        document.querySelectorAll('.invoice-row').forEach(item => item.classList.remove('active'));
        document.querySelectorAll('.invoice-detail').forEach(detail => {
            detail.style.display = 'none';
        });

        if (!isActive && detailRow) {
            row.classList.add('active');
            detailRow.style.display = 'table-row';
        }
    });
});

document.querySelectorAll('.btn-cancel').forEach(button => {
    button.addEventListener('click', async event => {
        event.stopPropagation();

        const id = button.dataset.id;
        const cancelUrl = button.dataset.cancelUrl || `${BASE_URL}/pos/invoice/${id}/cancel`;

        if (!await openConfirmDialog('Bạn chắc chắn muốn hủy hóa đơn này?')) return;

        button.disabled = true;

        try {
            const response = await fetch(cancelUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const contentType = response.headers.get('content-type') || '';
            const data = contentType.includes('application/json')
                ? await response.json()
                : { success: false, message: 'Phản hồi không hợp lệ từ máy chủ' };

            if (!response.ok || data.success === false) {
                throw new Error(data.message || 'Không thể hủy hóa đơn');
            }

            showToast(data.message || 'Đã hủy hóa đơn', 'success');
            setTimeout(() => {
                location.reload();
            }, 800);
        } catch (error) {
            console.error(error);
            showToast(error.message || 'Lỗi server', 'error');
            button.disabled = false;
        }
    });
});

let currentPage = 1;
const rowsPerPage = 13;

function getInvoiceRows() {
    return Array.from(document.querySelectorAll('.invoice-row'))
        .filter(row => row.dataset.filtered !== '0');
}

function renderPagination() {
    const rows = getInvoiceRows();
    const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;

    if (currentPage > totalPages) currentPage = totalPages;

    rows.forEach((row, index) => {
        const detail = document.getElementById(`detail-${row.dataset.id}`);
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const show = index >= start && index < end;

        row.style.display = show ? '' : 'none';
        if (detail) detail.style.display = 'none';
    });

    document.getElementById('pageInfo').innerText = `Trang ${currentPage} / ${totalPages}`;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;

    calculateSummary(rows);

    const paginationContainer = document.getElementById('pagination');
    if (totalPages <= 1) {
        paginationContainer.classList.add('d-none');
    } else {
        paginationContainer.classList.remove('d-none');
    }
}

document.getElementById('prevPage').onclick = () => {
    if (currentPage > 1) {
        currentPage--;
        renderPagination();
    }
};

document.getElementById('nextPage').onclick = () => {
    currentPage++;
    renderPagination();
};

function calculateSummary(rows) {
    let totalMoney = 0;
    let totalDiscount = 0;
    let totalFinal = 0;

    rows.forEach(row => {
        totalMoney += Number(row.querySelector('.money')?.dataset.value || 0);
        totalDiscount += Number(row.querySelector('.discount')?.dataset.value || 0);
        totalFinal += Number(row.querySelector('.final')?.dataset.value || 0);
    });

    document.getElementById('sum-money').innerText = totalMoney.toLocaleString('vi-VN');
    document.getElementById('sum-discount').innerText = totalDiscount.toLocaleString('vi-VN');
    document.getElementById('sum-final').innerText = totalFinal.toLocaleString('vi-VN');
}

document.querySelectorAll('.invoice-row').forEach(row => {
    row.dataset.filtered = '1';
});

renderPagination();
