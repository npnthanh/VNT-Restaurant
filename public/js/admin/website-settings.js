(() => {
    const form = document.getElementById('settings-form');
    if (!form) return;

    const status = document.getElementById('save-status');
    const saveBar = document.querySelector('.save-bar');
    let dirty = false;
    let submitting = false;
    const objectUrls = new Map();
    const images = new Map();

    // Cache file inputs and preview image elements
    const cacheImages = root => root.querySelectorAll('input[type=file]').forEach(input => {
        const preview = document.getElementById(input.dataset.preview);
        if (preview) {
            preview.dataset.original = preview.getAttribute('src') || '';
            images.set(input.name, { input, preview });
        }
    });
    cacheImages(form);

    const resetChecked = name => form.elements.namedItem(images.get(name)?.input.dataset.resetName || 'reset_' + name)?.checked;
    const showPreview = (preview, source) => {
        preview.hidden = !source;
        if (source) preview.src = source;
        else preview.removeAttribute('src');
        const empty = preview.parentElement.querySelector('.preview-empty');
        if (empty) empty.hidden = Boolean(source);
    };

    const refreshPreviews = () => {
        images.forEach(({ input, preview }, name) => {
            showPreview(preview, objectUrls.get(input) || (resetChecked(name) ? preview.dataset.defaultSrc : preview.dataset.original));
        });

        form.querySelectorAll('[data-banner-panel]').forEach(panel => {
            const desktopName = panel.querySelector('[data-device="desktop"]').name;
            const mobileName = panel.querySelector('[data-device="mobile"]').name;
            const desktop = images.get(desktopName);
            const mobile = images.get(mobileName);

            if (desktop && mobile && !objectUrls.has(mobile.input)) {
                if (resetChecked(mobileName) || objectUrls.has(desktop.input)) {
                    showPreview(mobile.preview, desktop.preview.getAttribute('src') || '');
                } else if (resetChecked(desktopName)) {
                    showPreview(mobile.preview, mobile.preview.dataset.defaultSrc);
                }
            }
        });
    };

    const markDirty = () => {
        if (!dirty) {
            dirty = true;
            if (saveBar) saveBar.classList.add('is-dirty');
        }
        if (status) {
            status.innerHTML = '<span style="color:#d97706;font-weight:600;"><i class="fa-solid fa-pen-nib"></i> Có thay đổi chưa lưu.</span>';
        }
    };

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    // Handle file selection
    const bindUploads = root => root.querySelectorAll('input[type=file]').forEach(input => {
        input.addEventListener('change', () => {
            if (objectUrls.has(input)) {
                URL.revokeObjectURL(objectUrls.get(input));
                objectUrls.delete(input);
            }

            const file = input.files[0];
            if (!file) {
                input.setCustomValidity('');
                refreshPreviews();
                return;
            }

            const isFavicon = input.name === 'favicon';
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (isFavicon) allowedTypes.push('image/x-icon', 'image/vnd.microsoft.icon');
            const validType = allowedTypes.includes(file.type) || (isFavicon && !file.type && /\.ico$/i.test(file.name));
            const invalid = !validType || file.size > 5 * 1024 * 1024;
            input.setCustomValidity(invalid ? (isFavicon ? 'Chọn ảnh ICO, PNG, JPG hoặc WebP không quá 5 MB.' : 'Chọn ảnh JPG, PNG hoặc WebP không quá 5 MB.') : '');

            if (invalid) {
                input.reportValidity();
                refreshPreviews();
                return;
            }

            const url = URL.createObjectURL(file);
            objectUrls.set(input, url);

            const reset = form.elements.namedItem(input.dataset.resetName || 'reset_' + input.name);
            if (reset) reset.checked = false;

            refreshPreviews();
        });

        // Drag & Drop support
        const dropzone = input.closest('.image-upload')?.querySelector('.image-preview');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('drag-over');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('drag-over');
                }, false);
            });

            dropzone.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files.length > 0) {
                    input.files = files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }, false);
        }
    });
    bindUploads(form);

    // Handle reset checkboxes
    const bindResets = root => root.querySelectorAll('input[name^="reset_"], input[data-reset-file]').forEach(reset => {
        reset.addEventListener('change', () => {
            const fieldName = reset.name.replace('reset_', '');
            const item = reset.dataset.resetFile ? images.get(document.getElementById(reset.dataset.resetFile).name) : images.get(fieldName);
            if (item && reset.checked) {
                item.input.value = '';
                item.input.setCustomValidity('');
                if (objectUrls.has(item.input)) {
                    URL.revokeObjectURL(objectUrls.get(item.input));
                    objectUrls.delete(item.input);
                }
            }
            refreshPreviews();
        });
    });
    bindResets(form);

    // Character counter updates
    const bindCounters = root => root.querySelectorAll('.char-counter').forEach(counter => {
        const inputId = counter.dataset.for;
        const targetInput = document.getElementById(inputId);
        if (targetInput) {
            const max = targetInput.maxLength > 0 ? targetInput.maxLength : 200;
            const updateCount = () => {
                const len = targetInput.value ? targetInput.value.length : 0;
                counter.textContent = `${len}/${max}`;
                counter.classList.toggle('near-limit', len > max * 0.9);
            };
            targetInput.addEventListener('input', updateCount);
            updateCount();
        }
    });
    bindCounters(form);

    // Banner toggle switch live reaction
    const bindToggles = root => root.querySelectorAll('.banner-toggle-checkbox').forEach(chk => {
        chk.addEventListener('change', () => {
            const panel = chk.closest('[data-banner-panel]');
            if (panel) {
                const enabled = chk.checked;
                panel.classList.toggle('is-enabled', enabled);
                panel.classList.toggle('is-disabled', !enabled);

                const statusTag = panel.querySelector('.banner-status-tag');
                if (statusTag) {
                    statusTag.className = `banner-status-tag ${enabled ? 'active' : 'inactive'}`;
                    statusTag.innerHTML = `<i class="fa-solid ${enabled ? 'fa-circle-check' : 'fa-circle-pause'}"></i> <span class="status-text">${enabled ? 'Đang bật hiển thị' : 'Đang tạm ẩn'}</span>`;
                }
            }
        });
    });
    bindToggles(form);

    const list = document.getElementById('banner-list');
    const template = document.getElementById('banner-template');
    let nextIndex = Math.max(-1, ...Array.from(list.children, panel => Number(panel.dataset.bannerPanel))) + 1;
    const numberPanels = () => {
        const panels = Array.from(list.children);
        panels.forEach((panel, position) => {
            panel.querySelector('.banner-badge-num').textContent = position + 1;
            panel.querySelector('[data-banner-title]').textContent = `Banner ${position + 1}`;
            panel.querySelector('[data-banner-up]').disabled = position === 0;
            panel.querySelector('[data-banner-down]').disabled = position === panels.length - 1;
            panel.querySelector('[data-banner-remove]').disabled = panels.length === 1;
        });
        document.getElementById('banner-count').textContent = `${panels.length} banner`;
    };
    document.getElementById('add-banner').addEventListener('click', () => {
        const holder = document.createElement('template');
        holder.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        const panel = holder.content.firstElementChild;
        list.append(panel);
        cacheImages(panel);
        bindUploads(panel);
        bindResets(panel);
        bindCounters(panel);
        bindToggles(panel);
        numberPanels();
        refreshPreviews();
        markDirty();
        panel.scrollIntoView({ block: 'start', behavior: 'smooth' });
        panel.querySelector('[data-banner-title]').setAttribute('tabindex', '-1');
        panel.querySelector('[data-banner-title]').focus({ preventScroll: true });
    });
    list.addEventListener('click', event => {
        const button = event.target.closest('[data-banner-up], [data-banner-down], [data-banner-remove]');
        if (!button) return;
        const panel = button.closest('[data-banner-panel]');
        if (button.hasAttribute('data-banner-up') && panel.previousElementSibling) {
            list.insertBefore(panel, panel.previousElementSibling);
        } else if (button.hasAttribute('data-banner-down') && panel.nextElementSibling) {
            list.insertBefore(panel.nextElementSibling, panel);
        } else if (button.hasAttribute('data-banner-remove') && list.children.length > 1) {
            panel.querySelectorAll('input[type=file]').forEach(input => {
                if (objectUrls.has(input)) URL.revokeObjectURL(objectUrls.get(input));
                objectUrls.delete(input);
                images.delete(input.name);
            });
            panel.remove();
        } else return;
        numberPanels();
        markDirty();
    });
    numberPanels();

    refreshPreviews();

    // Form submission state
    form.addEventListener('submit', () => {
        submitting = true;
        if (status) {
            status.innerHTML = '<span style="color:#1b4e30;font-weight:600;"><i class="fa-solid fa-spinner fa-spin"></i> Đang lưu cấu hình…</span>';
        }
        const btn = form.querySelector('button[type=submit]');
        if (btn) btn.disabled = true;
    });

    // Unsaved changes alert
    window.addEventListener('beforeunload', event => {
        if (dirty && !submitting) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    // Smooth sidebar scrollspy
    const links = Array.from(document.querySelectorAll('.sidebar-nav .nav-item'));
    if ('IntersectionObserver' in window && links.length > 0) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    links.forEach(link => {
                        const targetId = link.getAttribute('href');
                        link.classList.toggle('active', targetId === '#' + entry.target.id);
                    });
                }
            });
        }, { rootMargin: '-80px 0px -55% 0px' });

        document.querySelectorAll('.settings-card').forEach(card => observer.observe(card));
    }
})();
