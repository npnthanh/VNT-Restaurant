document.addEventListener('DOMContentLoaded', () => {
  const footer = document.querySelector('footer, .footer');
  const scrollBtn = document.getElementById('scrollButton');
  const mobileNavToggle = document.getElementById('mobileNavToggle');
  const mobileNavClose = document.getElementById('mobileNavClose');
  const mobileNavDrawer = document.getElementById('mobileNavDrawer');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');
  const bookingOverlay = document.getElementById('bookingOverlay');
  const closeBookingBtn = document.getElementById('closeBooking2');
  const bookingSubmitBtn = document.querySelector('#bookingOverlay .submit-btn');
  const bookingOpenButtons = document.querySelectorAll('[data-open-booking]');

  const updateScrollButton = () => {
    if (!footer || !scrollBtn) return;
    const footerRect = footer.getBoundingClientRect();
    const windowHeight = window.innerHeight;

    if (footerRect.top < windowHeight - 100) {
      scrollBtn.style.bottom = `${windowHeight - footerRect.top + 100}px`;
    } else {
      scrollBtn.style.bottom = '120px';
    }
  };

  if (scrollBtn) {
    window.addEventListener('scroll', updateScrollButton);
    updateScrollButton();

    scrollBtn.addEventListener('click', () => {
      const scrollStep = -window.scrollY / 10;
      const scrollInterval = setInterval(() => {
        if (window.scrollY !== 0) {
          window.scrollBy(0, scrollStep);
        } else {
          clearInterval(scrollInterval);
        }
      }, 16);
    });
  }

  const setMobileNavState = (isOpen) => {
    if (!mobileNavDrawer || !mobileNavOverlay || !mobileNavToggle) return;
    mobileNavDrawer.classList.toggle('is-open', isOpen);
    mobileNavOverlay.classList.toggle('is-open', isOpen);
    mobileNavDrawer.setAttribute('aria-hidden', String(!isOpen));
    mobileNavOverlay.setAttribute('aria-hidden', String(!isOpen));
    mobileNavToggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('mobile-nav-open', isOpen);
  };

  if (mobileNavToggle && mobileNavDrawer && mobileNavOverlay) {
    mobileNavToggle.addEventListener('click', () => setMobileNavState(true));
    mobileNavClose?.addEventListener('click', () => setMobileNavState(false));
    mobileNavOverlay.addEventListener('click', () => setMobileNavState(false));
    mobileNavDrawer.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => setMobileNavState(false));
    });
  }

  const bookingDropdowns = bookingOverlay
    ? bookingOverlay.querySelectorAll('.custom-dropdown')
    : [];

  const resetBookingForm = () => {
    if (!bookingOverlay) return;
    const textInputs = bookingOverlay.querySelectorAll('input[type="text"], textarea');
    textInputs.forEach((input) => {
      input.value = '';
    });

    const hiddenInputs = bookingOverlay.querySelectorAll('input[type="hidden"]');
    hiddenInputs.forEach((input) => {
      if (input.id !== 'bookingDateHidden') input.value = '';
    });

    const guestInput = bookingOverlay.querySelector('.guest-input');
    if (guestInput) guestInput.value = 1;

    bookingDropdowns.forEach((dropdown) => {
      const placeholder = dropdown.dataset.placeholder || 'Lựa chọn';
      const selectedText = dropdown.querySelector('.selected-text');
      const list = dropdown.querySelector('.dropdown-list');
      if (selectedText) selectedText.textContent = placeholder;
      list?.classList.remove('show');
      dropdown.classList.remove('open');
    });

    const dateText = document.getElementById('dateText');
    const bookingDateHidden = document.getElementById('bookingDateHidden');
    if (dateText) dateText.textContent = '--/--';
    if (bookingDateHidden) bookingDateHidden.value = '';
  };

  const openBookingOverlay = (prefill = {}) => {
    if (!bookingOverlay) return;
    resetBookingForm();
    bookingOverlay.classList.add('active');
    bookingOverlay.style.removeProperty('display');
    document.body.classList.add('booking-open');
    setMobileNavState(false);

    if (prefill.locationId) {
      const dropdown = bookingOverlay.querySelector('.custom-dropdown[data-placeholder="Lựa chọn cơ sở"]');
      if (dropdown) {
        const items = dropdown.querySelectorAll('.dropdown-list li');
        const selectedText = dropdown.querySelector('.selected-text');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        items.forEach((item) => {
          if (item.getAttribute('value') === String(prefill.locationId)) {
            if (selectedText) selectedText.textContent = item.textContent.trim();
            if (hiddenInput) hiddenInput.value = item.getAttribute('value') || '';
            dropdown.dataset.value = item.getAttribute('value') || '';
          }
        });
      }
    }
  };

  const closeBookingOverlay = () => {
    if (!bookingOverlay) return;
    bookingOverlay.classList.remove('active');
    bookingOverlay.style.removeProperty('display');
    document.body.classList.remove('booking-open');
  };

  window.openUserBookingModal = openBookingOverlay;
  window.closeUserBookingModal = closeBookingOverlay;

  bookingOpenButtons.forEach((button) => {
    button.addEventListener('click', () => {
      const locationId = button.dataset.locationId || '';
      openBookingOverlay(locationId ? { locationId } : {});
    });
  });

  if (closeBookingBtn && bookingOverlay) {
    closeBookingBtn.addEventListener('click', closeBookingOverlay);
    bookingOverlay.addEventListener('click', (event) => {
      if (event.target === bookingOverlay) {
        closeBookingOverlay();
      }
    });
  }

  bookingDropdowns.forEach((dropdown) => {
    const selected = dropdown.querySelector('.dropdown-selected');
    const list = dropdown.querySelector('.dropdown-list');
    const selectedText = dropdown.querySelector('.selected-text');
    const hiddenInput = dropdown.querySelector('input[type="hidden"]');

    selected?.addEventListener('click', () => {
      bookingDropdowns.forEach((item) => {
        if (item !== dropdown) {
          item.classList.remove('open');
          item.querySelector('.dropdown-list')?.classList.remove('show');
        }
      });

      dropdown.classList.toggle('open');
      list?.classList.toggle('show');
    });

    list?.querySelectorAll('li').forEach((entry) => {
      entry.addEventListener('click', () => {
        const value = entry.getAttribute('value') || entry.textContent.trim();
        if (selectedText) selectedText.textContent = entry.textContent.trim();
        if (hiddenInput) hiddenInput.value = value;
        dropdown.dataset.value = value;
        dropdown.classList.remove('open');
        list.classList.remove('show');
      });
    });
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('#bookingOverlay .custom-dropdown')) {
      bookingDropdowns.forEach((dropdown) => {
        dropdown.classList.remove('open');
        dropdown.querySelector('.dropdown-list')?.classList.remove('show');
      });
    }
  });

  const minusBtn = bookingOverlay?.querySelector('.guest .minus');
  const plusBtn = bookingOverlay?.querySelector('.guest .plus');
  const guestInput = bookingOverlay?.querySelector('.guest-input');

  minusBtn?.addEventListener('click', () => {
    if (!guestInput) return;
    const nextValue = Math.max(1, Number(guestInput.value || 1) - 1);
    guestInput.value = nextValue;
  });

  plusBtn?.addEventListener('click', () => {
    if (!guestInput) return;
    guestInput.value = Number(guestInput.value || 1) + 1;
  });

  bookingSubmitBtn?.addEventListener('click', async (event) => {
    event.preventDefault();

    const customerName = bookingOverlay?.querySelector('input[placeholder="Tên của bạn"]')?.value.trim() || '';
    const phone = bookingOverlay?.querySelector('input[placeholder="Số điện thoại"]')?.value.trim() || '';
    const locationDropdown = bookingOverlay?.querySelector('.custom-dropdown[data-placeholder="Lựa chọn cơ sở"]');
    const locationId = locationDropdown?.dataset.value || locationDropdown?.querySelector('input[type="hidden"]')?.value || '';
    const guestCount = Number(guestInput?.value || 0);
    const bookingDate = document.getElementById('bookingDateHidden')?.value || '';
    const timeDropdown = bookingOverlay?.querySelector('.custom-dropdown[data-placeholder="Chọn giờ"]');
    const bookingTime = timeDropdown?.dataset.value || '';
    const promotionDropdown = bookingOverlay?.querySelector('.custom-dropdown[data-placeholder="Chọn ưu đãi"]');
    const promotionId = promotionDropdown?.dataset.value || null;
    const note = bookingOverlay?.querySelector('textarea')?.value.trim() || '';

    if (!customerName || !phone || !locationId || guestCount <= 0 || !bookingDate || !bookingTime) {
      showToast('<i class="fas fa-exclamation-triangle"></i> Vui lòng nhập đầy đủ thông tin bắt buộc', 'warning');
      return;
    }

    const [day, month, year] = bookingDate.split('/');
    const bookingDatetime = `${year}-${month}-${day} ${bookingTime}:00`;

    const payload = {
      customer_name: customerName,
      phone,
      location_id: locationId,
      guest_count: guestCount,
      booking_time: bookingDatetime,
      promotion_id: promotionId,
      note,
      items: window.currentCart || []
    };

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      const response = await fetch(`${APP_URL}/booking/store`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          Accept: 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await response.json();

      if (!data.success) {
        showToast('<i class="fas fa-times-circle"></i> ' + (data.message || 'Đặt bàn thất bại'), 'error');
        return;
      }

      showToast('<i class="fas fa-check-circle"></i> Đặt bàn thành công! Chúng tôi sẽ liên hệ sớm.', 'success');
      closeBookingOverlay();
    } catch (error) {
      console.error(error);
      showToast('<i class="fas fa-times-circle"></i> Có lỗi xảy ra, vui lòng thử lại', 'error');
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      if (mobileNavDrawer?.classList.contains('is-open')) {
        setMobileNavState(false);
      }
      if (bookingOverlay?.classList.contains('active')) {
        closeBookingOverlay();
      }
    }
  });
});

(function () {
  const overlay = document.getElementById('appConfirmOverlay');
  if (!overlay) {
    window.openConfirmDialog = () => Promise.resolve(false);
    return;
  }

  const dialog = document.getElementById('appConfirmDialog');
  const titleEl = document.getElementById('appConfirmTitle');
  const messageEl = document.getElementById('appConfirmMessage');
  const confirmBtn = document.getElementById('appConfirmOk');
  const cancelBtn = document.getElementById('appConfirmCancel');
  const closeBtn = document.getElementById('appConfirmClose');
  const iconEl = overlay.querySelector('.app-confirm-icon i');
  let resolveConfirm = null;
  let keyHandler = null;

  const closeConfirm = (result) => {
    overlay.classList.remove('active');
    overlay.setAttribute('aria-hidden', 'true');

    if (resolveConfirm) {
      resolveConfirm(Boolean(result));
      resolveConfirm = null;
    }

    if (keyHandler) {
      document.removeEventListener('keydown', keyHandler);
      keyHandler = null;
    }
  };

  window.openConfirmDialog = (message, options = {}) => {
    if (titleEl) titleEl.textContent = options.title || 'Xác nhận';
    if (messageEl) messageEl.textContent = message || '';
    if (confirmBtn) confirmBtn.textContent = options.confirmText || 'Đồng ý';
    if (cancelBtn) cancelBtn.textContent = options.cancelText || 'Hủy';
    if (dialog) dialog.dataset.variant = options.variant || '';
    if (iconEl) iconEl.className = `fas ${options.icon || 'fa-triangle-exclamation'}`;

    overlay.classList.add('active');
    overlay.setAttribute('aria-hidden', 'false');

    if (confirmBtn && typeof confirmBtn.focus === 'function') {
      confirmBtn.focus();
    }

    keyHandler = (event) => {
      if (event.key === 'Escape') {
        closeConfirm(false);
      }
    };
    document.addEventListener('keydown', keyHandler);

    return new Promise((resolve) => {
      resolveConfirm = resolve;
    });
  };

  overlay.addEventListener('click', (event) => {
    if (event.target === overlay) closeConfirm(false);
  });
  confirmBtn?.addEventListener('click', () => closeConfirm(true));
  cancelBtn?.addEventListener('click', () => closeConfirm(false));
  closeBtn?.addEventListener('click', () => closeConfirm(false));
})();
