document.addEventListener('DOMContentLoaded', () => {
  const scrollContainer = document.getElementById('menuScroll');
  const btnLeft = document.getElementById('scrollLeft');
  const btnRight = document.getElementById('scrollRight');
  const categoryLinks = Array.from(document.querySelectorAll('.category-item'));
  const productContent = document.getElementById('productContent');
  const baseFilterUrl = document.querySelector('meta[name="filter-url"]')?.content;
  const searchInput = document.getElementById('menuSearchInput');
  const floatingCart = document.getElementById('openCart');
  const floatingCount = document.getElementById('floatingCount');
  const floatingTotal = document.getElementById('floatingTotal');
  const modalItems = document.getElementById('modalItems');
  const modalTotal = document.getElementById('modalTotal');
  const cartOverlay = document.getElementById('cartOverlay');
  const captureArea = document.getElementById('captureArea');
  const saveImgBtn = document.getElementById('saveImg');
  const clearCartBtn = document.getElementById('clearCart');
  const closeCartBtn = document.getElementById('closeCart');
  const submitOrderBtn = document.querySelector('.btn-submit-order');
  const mobileQuery = window.matchMedia('(max-width: 991px)');

  let cart = [];
  let selectedCategory = 'all';
  let menuState = { sections: [] };

  const isMobileLayout = () => mobileQuery.matches;

  const resolveImage = (path) => {
    if (!path) return `${APP_URL}/images/product/default-product.png`;
    if (/^https?:\/\//i.test(path)) return path;
    return `${APP_URL}/${String(path).replace(/^\/+/, '')}`;
  };

  const escapeHtml = (value) => String(value || '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const formatPrice = (value) => Number(value || 0).toLocaleString('vi-VN');

  const getSectionVariant = (name, index) => {
    const normalized = String(name || '').toLowerCase();
    if (normalized.includes('combo') || normalized.includes('set') || index === 0) {
      return 'featured';
    }
    return 'list';
  };

  const setupChipScroll = () => {
    if (!scrollContainer) return;

    const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;

    if (btnLeft && btnRight) {
      btnLeft.classList.toggle('hidden', scrollContainer.scrollLeft <= 0);
      btnRight.classList.toggle('hidden', scrollContainer.scrollLeft >= maxScrollLeft - 1);
    }

    const leftBoundary = scrollContainer.scrollLeft + 32;
    const rightBoundary = scrollContainer.scrollLeft + scrollContainer.clientWidth - 32;

    scrollContainer.querySelectorAll('a').forEach((link) => {
      const rect = link.getBoundingClientRect();
      const parentRect = scrollContainer.getBoundingClientRect();
      const linkLeft = rect.left - parentRect.left + scrollContainer.scrollLeft;
      const linkRight = rect.right - parentRect.left + scrollContainer.scrollLeft;
      link.classList.toggle('dimmed', linkRight < leftBoundary || linkLeft > rightBoundary);
    });
  };

  if (scrollContainer) {
    btnLeft?.addEventListener('click', () => {
      scrollContainer.scrollBy({ left: -180, behavior: 'smooth' });
    });

    btnRight?.addEventListener('click', () => {
      scrollContainer.scrollBy({ left: 180, behavior: 'smooth' });
    });

    scrollContainer.addEventListener('scroll', setupChipScroll);
    window.addEventListener('resize', setupChipScroll);
    setupChipScroll();
  }

  const buildSections = (payload, categoryId) => {
    if (categoryId === 'all') {
      return (payload.categories || []).map((category, index) => ({
        id: category.id,
        name: category.name,
        variant: getSectionVariant(category.name, index),
        products: (category.products || []).map((product) => ({
          ...product,
          img: resolveImage(product.img)
        }))
      }));
    }

    return [{
      id: categoryId,
      name: payload.title || 'Danh mục',
      variant: getSectionVariant(payload.title, 0),
      products: (payload.products || []).map((product) => ({
        ...product,
        img: resolveImage(product.img)
      }))
    }];
  };

  const filterSections = (keyword) => {
    if (!keyword) return menuState.sections;

    const normalized = keyword.toLowerCase();
    return menuState.sections
      .map((section) => ({
        ...section,
        products: section.products.filter((product) => String(product.name || '').toLowerCase().includes(normalized))
      }))
      .filter((section) => section.products.length > 0);
  };

  const renderDesktopCard = (product) => {
    const name = escapeHtml(product.name);
    const price = formatPrice(product.price);
    const image = escapeHtml(product.img);
    const rawPrice = Number(product.price || 0);

    return `
      <div class="product-item">
        <img src="${image}" class="detail-img" alt="${name}">
        <h3>${name}</h3>
        <p>${price} đ</p>
        <button
          class="btn-add"
          data-id="${product.id}"
          data-name="${name}"
          data-price="${rawPrice}">
          + Đặt
        </button>
      </div>
    `;
  };

  const renderMobileCard = (product, variant) => {
    const name = escapeHtml(product.name);
    const price = formatPrice(product.price);
    const image = escapeHtml(product.img);
    const rawPrice = Number(product.price || 0);

    if (variant === 'featured') {
      return `
        <article class="product-item product-item--featured">
          <div class="product-thumb">
            <img src="${image}" alt="${name}">
          </div>
          <div class="product-copy">
            <h3>${name}</h3>
            <p class="product-price">${price} đ</p>
            <button
              class="btn-add"
              data-id="${product.id}"
              data-name="${name}"
              data-price="${rawPrice}">
              + Đặt
            </button>
          </div>
        </article>
      `;
    }

    return `
      <article class="product-item product-item--list">
        <div class="product-thumb">
          <img src="${image}" alt="${name}">
        </div>
        <div class="product-copy">
          <h3>${name}</h3>
          <p class="product-price">${price} đ</p>
        </div>
        <div class="product-action">
          <button
            class="btn-add"
            data-id="${product.id}"
            data-name="${name}"
            data-price="${rawPrice}">
            + Đặt
          </button>
        </div>
      </article>
    `;
  };

  const renderDesktopSections = (sections) => sections.map((section) => `
    <h2 class="category-title">${escapeHtml(section.name)}</h2>
    <div class="product-grid">
      ${section.products.map((product) => renderDesktopCard(product)).join('')}
    </div>
  `).join('');

  const renderMobileSections = (sections) => sections.map((section) => `
    <section class="menu-section menu-section--featured">
      <div class="menu-section-head">
        <h2 class="category-title">${escapeHtml(section.name)}</h2>
        <span class="category-count">${section.products.length} món</span>
      </div>
      <div class="product-grid product-grid--featured">
        ${section.products.map((product) => renderMobileCard(product, 'featured')).join('')}
      </div>
    </section>
  `).join('');

  const renderSections = () => {
    if (!productContent) return;

    const sections = filterSections(searchInput?.value.trim() || '');

    if (!sections.length) {
      productContent.innerHTML = `
        <div class="menu-empty-state">
          <h3>Không tìm thấy món phù hợp</h3>
          <p>Thử đổi từ khóa hoặc chọn nhóm món khác.</p>
        </div>
      `;
      return;
    }

    productContent.innerHTML = isMobileLayout()
      ? renderMobileSections(sections)
      : renderDesktopSections(sections);
  };

  const loadCategory = async (categoryId) => {
    if (!baseFilterUrl || !productContent) return;

    try {
      productContent.innerHTML = '<div class="menu-loading-state">Đang tải thực đơn...</div>';
      const response = await fetch(`${baseFilterUrl}/${categoryId}`);
      const payload = await response.json();
      menuState = { sections: buildSections(payload, categoryId) };
      renderSections();
    } catch (error) {
      console.error(error);
      productContent.innerHTML = `
        <div class="menu-empty-state">
          <h3>Không thể tải thực đơn</h3>
          <p>Vui lòng thử lại sau.</p>
        </div>
      `;
    }
  };

  categoryLinks.forEach((link) => {
    link.addEventListener('click', async (event) => {
      event.preventDefault();
      selectedCategory = link.dataset.category || 'all';
      categoryLinks.forEach((item) => item.classList.remove('active'));
      link.classList.add('active');
      await loadCategory(selectedCategory);
    });
  });

  searchInput?.addEventListener('input', renderSections);

  if (typeof mobileQuery.addEventListener === 'function') {
    mobileQuery.addEventListener('change', renderSections);
  } else if (typeof mobileQuery.addListener === 'function') {
    mobileQuery.addListener(renderSections);
  }

  const updateCartUI = () => {
    let total = 0;
    let count = 0;

    if (modalItems) modalItems.innerHTML = '';

    cart.forEach((item) => {
      const itemTotal = item.price * item.quantity;
      total += itemTotal;
      count += item.quantity;

      if (modalItems) {
        modalItems.innerHTML += `
          <div class="cart-item-row">
            <div class="item-details">
              <h4>${escapeHtml(item.name)}</h4>
              <span>${formatPrice(item.price)} đ</span>
            </div>
            <div class="item-controls">
              <div class="qty-box">
                <button onclick="changeQty('${item.id}', -1, event)">−</button>
                <span>${item.quantity}</span>
                <button onclick="changeQty('${item.id}', 1, event)">+</button>
              </div>
              <span class="item-price-total">${formatPrice(itemTotal)} đ</span>
              <button class="btn-remove-item" onclick="changeQty('${item.id}', -${item.quantity}, event)">&times;</button>
            </div>
          </div>
        `;
      }
    });

    if (floatingCount) floatingCount.innerText = count;
    if (floatingTotal) floatingTotal.innerText = formatPrice(total);
    if (modalTotal) modalTotal.innerText = formatPrice(total);
    if (floatingCart) floatingCart.classList.toggle('show', count > 0);

    if (!count && cartOverlay) {
      cartOverlay.classList.remove('active');
    }

    window.currentCart = cart;
  };

  window.changeQty = (id, delta, event) => {
    event?.stopPropagation();
    const item = cart.find((entry) => entry.id === id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) {
      cart = cart.filter((entry) => entry.id !== id);
    }
    updateCartUI();
  };

  document.addEventListener('click', (event) => {
    const addBtn = event.target.closest('.btn-add');
    if (!addBtn) return;

    const product = {
      id: addBtn.getAttribute('data-id'),
      name: addBtn.getAttribute('data-name'),
      price: parseInt(addBtn.getAttribute('data-price'), 10),
      quantity: 1
    };

    const existingItem = cart.find((item) => item.id === product.id);
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.push(product);
    }

    updateCartUI();
  });

  floatingCart?.addEventListener('click', (event) => {
    event.preventDefault();
    cartOverlay?.classList.add('active');
  });

  closeCartBtn?.addEventListener('click', (event) => {
    event.stopPropagation();
    cartOverlay?.classList.remove('active');
  });

  cartOverlay?.addEventListener('click', (event) => {
    if (event.target === cartOverlay) {
      cartOverlay.classList.remove('active');
    }
  });

  captureArea?.addEventListener('click', (event) => {
    event.stopPropagation();
  });

  clearCartBtn?.addEventListener('click', async () => {
    if (typeof window.openConfirmDialog === 'function') {
      const confirmed = await window.openConfirmDialog('Bạn có muốn xóa hết danh sách món ăn?', {
        title: 'Xác nhận xóa'
      });
      if (!confirmed) return;
    }

    cart = [];
    updateCartUI();
  });

  saveImgBtn?.addEventListener('click', () => {
    if (!captureArea) return;

    const scale = Math.max(3, window.devicePixelRatio || 1);
    const rect = captureArea.getBoundingClientRect();

    html2canvas(captureArea, {
      scale,
      useCORS: true,
      backgroundColor: '#ffffff',
      width: Math.ceil(rect.width),
      height: Math.ceil(rect.height),
      onclone: (doc) => {
        const cloneArea = doc.getElementById('captureArea');
        if (cloneArea) {
          cloneArea.style.transform = 'none';
          cloneArea.style.animation = 'none';
          cloneArea.style.boxShadow = 'none';
        }

        const cloneHeaderRight = doc.querySelector('.modal-header .header-right');
        if (cloneHeaderRight) cloneHeaderRight.style.display = 'none';
      }
    }).then((canvas) => {
      const link = document.createElement('a');
      link.download = 'thuc-don-tam-tinh.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
    });
  });

  submitOrderBtn?.addEventListener('click', () => {
    if (!cart.length) {
      showToast('<i class="fas fa-exclamation-circle"></i> Vui lòng chọn món trước khi đặt bàn', 'warning');
      return;
    }

    cartOverlay?.classList.remove('active');

    if (typeof window.openUserBookingModal === 'function') {
      window.openUserBookingModal();
    }
  });

  updateCartUI();
  loadCategory(selectedCategory);
});
