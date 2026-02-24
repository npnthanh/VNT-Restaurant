window.inventoryFilters = {
    code: '',
    ingredient: '',
    status: 'all',
    from: null,
    to: null,
};

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".inventory-row").forEach(row => {
        row.addEventListener("click", () => {
            const id = row.dataset.id;
            const detailRow = document.getElementById("detail-" + id);
            const isOpen = detailRow && detailRow.style.display === "table-row";

            document.querySelectorAll(".detail-row").forEach(r => {
                r.style.display = "none";
            });

            document.querySelectorAll(".inventory-row").forEach(r => {
                r.classList.remove("active");
            });

            if (!isOpen && detailRow) {
                detailRow.style.display = "table-row";
                row.classList.add("active");
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('timeBtn');
    const menu = document.getElementById('timeMenu');

    if (btn && menu) {
        btn.addEventListener('click', () => {
            btn.parentElement.classList.toggle('open');
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
    }

    function applyPreset(preset) {
        const now = new Date();
        let from = null, to = null;

        const startOfDay = d => new Date(d.setHours(0,0,0,0));
        const endOfDay   = d => new Date(d.setHours(23,59,59,999));

        switch (preset) {
            case 'today':
                from = startOfDay(new Date());
                to = endOfDay(new Date());
                break;
            case 'yesterday':
                const y = new Date();
                y.setDate(y.getDate() - 1);
                from = startOfDay(new Date(y));
                to = endOfDay(new Date(y));
                break;
            case 'this_week':
                const w1 = new Date();
                w1.setDate(w1.getDate() - w1.getDay() + 1);
                from = startOfDay(w1);
                to = new Date();
                break;
            case 'last_week':
                const lw = new Date();
                lw.setDate(lw.getDate() - lw.getDay() - 6);
                from = startOfDay(lw);
                const lwEnd = new Date(lw);
                lwEnd.setDate(lwEnd.getDate() + 6);
                to = endOfDay(lwEnd);
                break;
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
                to = new Date(now.getFullYear(), now.getMonth(), 0, 23,59,59);
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
                to = new Date(now.getFullYear() - 1, 11, 31, 23,59,59);
                break;
            case 'all':
                from = null;
                to = null;
                break;
        }
        window.inventoryFilters.from = from ? Math.floor(from.getTime() / 1000) : null;
        window.inventoryFilters.to   = to   ? Math.floor(to.getTime() / 1000) : null;

        applyInventoryFilters();
    }

    let currentPage = 1;
    const rowsPerPage = 10;

    function applyInventoryFilters() {
        document.querySelectorAll('.inventory-row').forEach(row => {
            let match = true;

            if (inventoryFilters.code) {
                match = row.dataset.code?.includes(inventoryFilters.code);
            }

            if (match && inventoryFilters.ingredient) {
                const text = row.dataset.ingredients || '';
                match = text.includes(inventoryFilters.ingredient);
            }

            if (match && inventoryFilters.status !== 'all') {
                match = row.dataset.status === inventoryFilters.status;
            }

            if (match && inventoryFilters.from && inventoryFilters.to && row.dataset.time) {
                const t = Number(row.dataset.time);
                match = t >= inventoryFilters.from && t <= inventoryFilters.to;
            }

            row.dataset.filtered = match ? '1' : '0';
            row.style.display = match ? '' : 'none';

            const detail = document.getElementById(`detail-${row.dataset.id}`);
            if (detail) detail.style.display = 'none';
        });
        currentPage = 1;
        renderPagination();
    }

    function getRows() {
        return Array.from(document.querySelectorAll('.inventory-row'))
            .filter(r => r.dataset.filtered !== '0');
    }

    function renderPagination() {
        const rows = getRows();
        const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;

        if (currentPage > totalPages) currentPage = totalPages;

        document.querySelectorAll('.inventory-row').forEach(r => {
            r.style.display = 'none';
            const d = document.getElementById(`detail-${r.dataset.id}`);
            if (d) d.style.display = 'none';
        });

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        rows.slice(start, end).forEach(r => r.style.display = '');

        const paginationContainer = document.getElementById('pagination');
        if (paginationContainer) {
            if (totalPages <= 1) {
                paginationContainer.style.setProperty('display', 'none', 'important');
            } else {
                paginationContainer.style.setProperty('display', 'flex', 'important');
            }
        }

        const pageInfo = document.getElementById('pageInfo');
        if (pageInfo) {
            pageInfo.innerText = `Trang ${currentPage} / ${totalPages}`;
        }
        const prevBtn = document.getElementById('prevPage');
        const nextBtn = document.getElementById('nextPage');
        if (prevBtn) prevBtn.disabled = (currentPage === 1);
        if (nextBtn) nextBtn.disabled = (currentPage === totalPages);
    }

    const prevPage = document.getElementById('prevPage');
    if (prevPage) {
        prevPage.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderPagination();
            }
        });
    }

    const nextPage = document.getElementById('nextPage');
    if (nextPage) {
        nextPage.addEventListener('click', () => {
            const rows = getRows();
            const totalPages = Math.ceil(rows.length / rowsPerPage) || 1;
            if (currentPage < totalPages) {
                currentPage++;
                renderPagination();
            }
        });
    }

    const searchCode = document.getElementById('searchCode');
    if (searchCode) {
        searchCode.addEventListener('input', e => {
            inventoryFilters.code = e.target.value.toLowerCase();
            applyInventoryFilters();
        });
    }

    const searchIngredient = document.getElementById('searchIngredient');
    if (searchIngredient) {
        searchIngredient.addEventListener('input', e => {
            inventoryFilters.ingredient = e.target.value.toLowerCase();
            applyInventoryFilters();
        });
    }

    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.addEventListener('change', e => {
            inventoryFilters.status = e.target.value;
            applyInventoryFilters();
        });
    });

    if (window.jQuery) {
        $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
            const from = picker.startDate.startOf('day');
            const to   = picker.endDate.endOf('day');

            $(this).val(
                picker.startDate.format('DD/MM/YYYY') +
                ' - ' +
                picker.endDate.format('DD/MM/YYYY')
            );

            inventoryFilters.from = from.unix();
            inventoryFilters.to   = to.unix();

            applyInventoryFilters();
        });

        $('#dateRange').on('cancel.daterangepicker', function () {
            $(this).val('');
            inventoryFilters.from = null;
            inventoryFilters.to   = null;
            applyInventoryFilters();
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
                    daysOfWeek: ['CN','T2','T3','T4','T5','T6','T7'],
                    monthNames: [
                        'Tháng 1','Tháng 2','Tháng 3','Tháng 4',
                        'Tháng 5','Tháng 6','Tháng 7','Tháng 8',
                        'Tháng 9','Tháng 10','Tháng 11','Tháng 12'
                    ]
                }
            });
        });
    }

    document.querySelectorAll('.inventory-row')
        .forEach(r => r.dataset.filtered = '1');

    renderPagination();
});

document.querySelectorAll('.box.collapsible').forEach(box => {
    const title = box.querySelector('.box-title');
    if (!title) return;

    title.addEventListener('click', () => {
        box.classList.toggle('collapsed');
    });
});
