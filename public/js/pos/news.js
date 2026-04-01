document.addEventListener('DOMContentLoaded', () => {
  const baseUrl =
    document.querySelector('meta[name="base-url"]')?.getAttribute('content') ||
    window.location.origin;
  const posBaseUrl = `${baseUrl}/pos`;

  const rows = Array.from(document.querySelectorAll('.news-row'));
  const searchInput = document.getElementById('newsSearchInput');
  const categoryFilter = document.getElementById('newsCategoryFilter');
  const categoryDropdown = document.getElementById('newsCategoryDropdown');
  const categoryDisplay = categoryDropdown?.querySelector('.selected-display');
  const categoryItems = categoryDropdown?.querySelectorAll('.dropdown-list li') || [];
  const currentCategoryText = document.getElementById('currentNewsCategoryText');
  const statusRadios = document.querySelectorAll('input[name="news_status"]');
  const prevBtn = document.getElementById('newsPrevPage');
  const nextBtn = document.getElementById('newsNextPage');
  const pageInfo = document.getElementById('newsPageInfo');
  const pagination = document.getElementById('newsPagination');

  const modal = document.getElementById('newsModal');
  const modalTitle = document.getElementById('newsModalTitle');
  const openModalBtn = document.getElementById('openNewsModalBtn');
  const closeModalBtn = document.getElementById('closeNewsModalBtn');
  const cancelBtn = document.getElementById('cancelNewsBtn');
  const deleteBtn = document.getElementById('deleteNewsBtn');
  const form = document.getElementById('newsForm');

  const titleInput = document.getElementById('news_title');
  const slugInput = document.getElementById('news_slug');
  const categoryInput = document.getElementById('news_category');
  const statusInput = document.getElementById('news_status');
  const publishedAtInput = document.getElementById('news_published_at');
  const featuredInput = document.getElementById('news_is_featured');
  const summaryInput = document.getElementById('news_summary');
  const contentInput = document.getElementById('news_content');
  const imageInput = document.getElementById('news_image');
  const imageTrigger = document.getElementById('newsFileTrigger');
  const fileNameLabel = document.getElementById('newsFileName');
  const previewWrap = document.getElementById('newsImagePreview');
  const previewImage = document.getElementById('newsPreviewImage');

  const formCategoryDropdown = document.getElementById('newsCategoryFormDropdown');
  const formCategoryDisplay = formCategoryDropdown?.querySelector('.selected-display');
  const formCategoryItems = formCategoryDropdown?.querySelectorAll('.dropdown-list li') || [];
  const currentFormCategoryText = document.getElementById('currentNewsFormCategoryText');
  const formStatusDropdown = document.getElementById('newsStatusDropdown');
  const formStatusDisplay = formStatusDropdown?.querySelector('.selected-display');
  const formStatusItems = formStatusDropdown?.querySelectorAll('.dropdown-list li') || [];
  const currentFormStatusText = document.getElementById('currentNewsStatusText');

  const filters = {
    search: '',
    category: '',
    status: 'all'
  };

  let currentPage = 1;
  const pageSize = 10;
  let slugTouched = false;

  function jsonHeaders() {
    return {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    };
  }

  function getErrorMessage(payload, fallback) {
    if (payload && payload.errors) {
      const keys = Object.keys(payload.errors);
      if (keys.length && payload.errors[keys[0]] && payload.errors[keys[0]][0]) {
        return payload.errors[keys[0]][0];
      }
    }

    if (payload && payload.message) return payload.message;
    return fallback || 'Request failed.';
  }

  function readJsonResponse(res) {
    const contentType = res.headers.get('content-type') || '';

    if (!contentType.includes('application/json')) {
      return res.text().then((text) => {
        const error = new Error('Non-JSON response.');
        error.responseText = text;
        throw error;
      });
    }

    return res.json();
  }

  function slugify(text) {
    return text
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function normalizeDateTimeForInput(value) {
    if (!value) return '';

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return String(value).replace(' ', 'T').slice(0, 16);
    }

    const year = date.getFullYear();
    const month = `${date.getMonth() + 1}`.padStart(2, '0');
    const day = `${date.getDate()}`.padStart(2, '0');
    const hours = `${date.getHours()}`.padStart(2, '0');
    const minutes = `${date.getMinutes()}`.padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
  }

  function assetUrl(path) {
    if (!path) return '';
    if (/^https?:\/\//i.test(path)) return path;

    return `${baseUrl.replace(/\/$/, '')}/${String(path).replace(/^\//, '')}`;
  }

  function updatePreview(path) {
    if (!previewWrap || !previewImage) return;

    if (!path) {
      previewWrap.hidden = true;
      previewImage.removeAttribute('src');
      return;
    }

    previewImage.src = assetUrl(path);
    previewWrap.hidden = false;
  }

  function getBasename(path) {
    if (!path) return '';
    const normalized = String(path).replace(/\\/g, '/');
    return normalized.split('/').pop() || normalized;
  }

  function updateFileNameLabel(text) {
    if (!fileNameLabel) return;
    fileNameLabel.textContent = text || 'Chưa chọn ảnh nào';
  }

  function applyFilters() {
    const filtered = rows.filter((row) => {
      const searchMatch = row.dataset.search.includes(filters.search);
      const categoryMatch = !filters.category || row.dataset.category === filters.category;
      const statusMatch = filters.status === 'all' || row.dataset.status === filters.status;

      return searchMatch && categoryMatch && statusMatch;
    });

    const totalPages = Math.max(1, Math.ceil(filtered.length / pageSize));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * pageSize;
    const pageRows = filtered.slice(start, start + pageSize);

    rows.forEach((row) => {
      row.style.display = 'none';
    });

    pageRows.forEach((row) => {
      row.style.display = '';
    });

    if (pageInfo) {
      pageInfo.textContent = `Trang ${currentPage}/${totalPages}`;
    }

    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;
    if (pagination) pagination.style.display = filtered.length > pageSize ? 'flex' : 'none';
  }

  function openModal() {
    if (modal) modal.style.display = 'flex';
  }

  function closeDropdown(dropdown) {
    if (!dropdown) return;

    dropdown.classList.remove('active');
    dropdown.querySelector('.selected-display')?.setAttribute('aria-expanded', 'false');
  }

  function closeAllDropdowns(except = null) {
    [categoryDropdown, formCategoryDropdown, formStatusDropdown].forEach((dropdown) => {
      if (dropdown && dropdown !== except) {
        closeDropdown(dropdown);
      }
    });
  }

  function toggleDropdown(dropdown) {
    if (!dropdown) return;

    const willOpen = !dropdown.classList.contains('active');
    closeAllDropdowns(willOpen ? dropdown : null);

    if (!willOpen) {
      closeDropdown(dropdown);
      return;
    }

    dropdown.classList.add('active');
    dropdown.querySelector('.selected-display')?.setAttribute('aria-expanded', 'true');
  }

  function closeModal() {
    closeAllDropdowns();
    if (modal) modal.style.display = 'none';
  }

  function syncDropdownSelection(input, textNode, items, value, fallbackValue = '') {
    const entries = Array.from(items || []);
    if (!entries.length) return fallbackValue;

    const matchedItem =
      entries.find((item) => (item.getAttribute('data-value') || '') === value) ||
      entries.find((item) => (item.getAttribute('data-value') || '') === fallbackValue) ||
      entries[0];

    const nextValue = matchedItem?.getAttribute('data-value') || fallbackValue;
    const nextText = matchedItem?.textContent.trim() || '';

    if (input) input.value = nextValue;
    if (textNode) textNode.textContent = nextText;

    entries.forEach((item) => {
      item.classList.toggle('is-selected', item === matchedItem);
    });

    return nextValue;
  }

  function setupDropdown({ dropdown, display, items, input, textNode, defaultValue = '', onChange }) {
    if (!dropdown || !display) return;

    display.addEventListener('click', (event) => {
      event.stopPropagation();
      toggleDropdown(dropdown);
    });

    display.addEventListener('keydown', (event) => {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        toggleDropdown(dropdown);
      }

      if (event.key === 'Escape') {
        event.preventDefault();
        closeDropdown(dropdown);
      }
    });

    items.forEach((item) => {
      item.addEventListener('click', (event) => {
        event.stopPropagation();

        const value = item.getAttribute('data-value') || '';
        const nextValue = syncDropdownSelection(input, textNode, items, value, defaultValue);

        if (typeof onChange === 'function') {
          onChange(nextValue, item.textContent.trim());
        }

        closeDropdown(dropdown);
      });
    });

    syncDropdownSelection(input, textNode, items, input?.value || defaultValue, defaultValue);
  }

  function resetForm() {
    if (!form) return;

    form.reset();
    form.action = `${posBaseUrl}/news`;
    form.method = 'POST';
    modalTitle.textContent = 'Tạo bài viết';
    slugTouched = false;
    updatePreview('');
    updateFileNameLabel('Chưa chọn ảnh nào');
    closeAllDropdowns();

    const methodInput = form.querySelector('input[name="_method"]');
    if (methodInput) methodInput.remove();

    if (deleteBtn) deleteBtn.style.display = 'none';
    syncDropdownSelection(statusInput, currentFormStatusText, formStatusItems, 'draft', 'draft');
    syncDropdownSelection(categoryInput, currentFormCategoryText, formCategoryItems, 'Ưu đãi', 'Ưu đãi');
  }

  function ensureMethod(method) {
    let methodInput = form.querySelector('input[name="_method"]');

    if (!methodInput) {
      methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      form.appendChild(methodInput);
    }

    methodInput.value = method;
  }

  async function loadNewsIntoForm(id) {
    const response = await fetch(`${posBaseUrl}/news/${id}`, {
      headers: jsonHeaders()
    });
    const payload = await readJsonResponse(response);

    if (!response.ok || payload.success === false) {
      throw new Error(getErrorMessage(payload, 'Không thể tải bài viết.'));
    }

    const news = payload.data;
    modalTitle.textContent = 'Cập nhật bài viết';

    titleInput.value = news.title || '';
    slugInput.value = news.slug || '';
    syncDropdownSelection(
      categoryInput,
      currentFormCategoryText,
      formCategoryItems,
      news.category || 'Ưu đãi',
      'Ưu đãi'
    );
    syncDropdownSelection(
      statusInput,
      currentFormStatusText,
      formStatusItems,
      news.status || 'draft',
      'draft'
    );
    publishedAtInput.value = normalizeDateTimeForInput(news.published_at);
    featuredInput.checked = Boolean(news.is_featured);
    summaryInput.value = news.summary || '';
    contentInput.value = news.content || '';
    updatePreview(news.image || '');
    updateFileNameLabel(
      news.image ? `Ảnh hiện tại: ${getBasename(news.image)}` : 'Chưa chọn ảnh nào'
    );

    form.action = `${posBaseUrl}/news/${id}`;
    form.method = 'POST';
    ensureMethod('PUT');
    slugTouched = true;

    if (deleteBtn) deleteBtn.style.display = 'inline-flex';
  }

  setupDropdown({
    dropdown: categoryDropdown,
    display: categoryDisplay,
    items: categoryItems,
    input: categoryFilter,
    textNode: currentCategoryText,
    defaultValue: '',
    onChange: (value) => {
      filters.category = value;
      currentPage = 1;
      applyFilters();
    }
  });

  setupDropdown({
    dropdown: formCategoryDropdown,
    display: formCategoryDisplay,
    items: formCategoryItems,
    input: categoryInput,
    textNode: currentFormCategoryText,
    defaultValue: 'Ưu đãi'
  });

  setupDropdown({
    dropdown: formStatusDropdown,
    display: formStatusDisplay,
    items: formStatusItems,
    input: statusInput,
    textNode: currentFormStatusText,
    defaultValue: 'draft'
  });

  searchInput?.addEventListener('input', (event) => {
    filters.search = event.target.value.trim().toLowerCase();
    currentPage = 1;
    applyFilters();
  });

  statusRadios.forEach((radio) => {
    radio.addEventListener('change', (event) => {
      filters.status = event.target.value;
      currentPage = 1;
      applyFilters();
    });
  });

  prevBtn?.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage -= 1;
      applyFilters();
    }
  });

  nextBtn?.addEventListener('click', () => {
    currentPage += 1;
    applyFilters();
  });

  openModalBtn?.addEventListener('click', () => {
    resetForm();
    openModal();
  });

  rows.forEach((row) => {
    row.addEventListener('click', async () => {
      try {
        resetForm();
        await loadNewsIntoForm(row.dataset.id);
        openModal();
      } catch (error) {
        console.error(error);
        showToast(error.message || 'Không thể mở bài viết.', 'error');
      }
    });
  });

  closeModalBtn?.addEventListener('click', closeModal);
  cancelBtn?.addEventListener('click', closeModal);

  window.addEventListener('click', (event) => {
    if (!event.target.closest('.custom-dropdown')) {
      closeAllDropdowns();
    }

    if (event.target === modal) {
      closeModal();
    }
  });

  titleInput?.addEventListener('input', () => {
    if (!slugTouched) {
      slugInput.value = slugify(titleInput.value);
    }
  });

  slugInput?.addEventListener('input', () => {
    slugTouched = slugInput.value.trim() !== '';
  });

  imageTrigger?.addEventListener('click', () => {
    imageInput?.click();
  });

  imageInput?.addEventListener('change', () => {
    const [file] = imageInput.files || [];

    if (!file) {
      updateFileNameLabel('Chưa chọn ảnh nào');
      updatePreview('');
      return;
    }

    updateFileNameLabel(file.name);

    if (previewImage && previewWrap) {
      previewImage.src = URL.createObjectURL(file);
      previewWrap.hidden = false;
    }
  });

  form?.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
      const formData = new FormData(form);
      const response = await fetch(form.action, {
        method: form.method,
        body: formData,
        headers: jsonHeaders()
      });
      const payload = await readJsonResponse(response);

      if (!response.ok || payload.success === false) {
        throw new Error(getErrorMessage(payload, 'Không thể lưu bài viết.'));
      }

      showToast(payload.message || 'Đã lưu bài viết.', 'success');
      closeModal();
      setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      console.error(error);
      showToast(error.message || 'Lỗi server.', 'error');
    }
  });

  deleteBtn?.addEventListener('click', async () => {
    const id = form.action.split('/').pop();
    if (!id) return;

    if (!(await openConfirmDialog('Bạn có chắc chắn muốn xóa bài viết này?'))) {
      return;
    }

    try {
      const formData = new FormData(form);
      formData.append('_method', 'DELETE');

      const response = await fetch(`${posBaseUrl}/news/${id}`, {
        method: 'POST',
        body: formData,
        headers: jsonHeaders()
      });
      const payload = await readJsonResponse(response);

      if (!response.ok || payload.success === false) {
        throw new Error(getErrorMessage(payload, 'Không thể xóa bài viết.'));
      }

      showToast(payload.message || 'Đã xóa bài viết.', 'success');
      closeModal();
      setTimeout(() => window.location.reload(), 700);
    } catch (error) {
      console.error(error);
      showToast(error.message || 'Lỗi server.', 'error');
    }
  });

  applyFilters();
});
