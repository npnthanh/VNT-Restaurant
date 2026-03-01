document.addEventListener('DOMContentLoaded', () => {
    const notifyWrap = document.getElementById('notifyWrap');
    const notifyBtn = document.getElementById('notifyBtn');
    const dropdown = document.getElementById('notifyDropdown');
    const list = document.getElementById('notifyList');
    const badge = document.getElementById('notifyBadge');
    const markAllBtn = document.getElementById('notifyMarkAll');

    if (!notifyWrap || !notifyBtn || !dropdown || !list || !badge) return;

    const tabButtons = dropdown.querySelectorAll('.notify-tab');
    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    const storageKey = 'pos_notify_last_seen_map';
    const legacyStorageKey = 'pos_notify_last_seen_id';

    let lastSeen = { booking: 0, contact: 0 };
    const stored = localStorage.getItem(storageKey);
    if (stored) {
        try {
            const parsed = JSON.parse(stored);
            if (parsed && typeof parsed === 'object') {
                lastSeen = { ...lastSeen, ...parsed };
            }
        } catch (e) {
            lastSeen = { booking: 0, contact: 0 };
        }
    } else {
        const legacyValue = parseInt(localStorage.getItem(legacyStorageKey) || '0', 10);
        if (Number.isFinite(legacyValue) && legacyValue > 0) {
            lastSeen.booking = legacyValue;
        }
        localStorage.setItem(storageKey, JSON.stringify(lastSeen));
    }

    lastSeen.booking = Number.isFinite(parseInt(lastSeen.booking, 10)) ? parseInt(lastSeen.booking, 10) : 0;
    lastSeen.contact = Number.isFinite(parseInt(lastSeen.contact, 10)) ? parseInt(lastSeen.contact, 10) : 0;

    let lastFetched = { booking: 0, contact: 0 };
    let latestIds = { booking: 0, contact: 0 };
    let currentItems = [];
    let currentFilter = 'all';
    let suppressSound = true;
    let audioCtx = null;

    function normalizeType(type) {
        return type === 'contact' ? 'contact' : 'booking';
    }

    function getLastSeen(type) {
        const normalized = normalizeType(type);
        return lastSeen[normalized] || 0;
    }

    function saveLastSeen() {
        localStorage.setItem(storageKey, JSON.stringify(lastSeen));
    }

    function getMaxId(type, items) {
        const normalized = normalizeType(type);
        return items.reduce((max, item) => {
            if (normalizeType(item.type) !== normalized) return max;
            const id = Number(item.id) || 0;
            return id > max ? id : max;
        }, 0);
    }

    function getUnreadCount(items) {
        return items.reduce((total, item) => {
            const id = Number(item.id) || 0;
            const unread = id > getLastSeen(item.type);
            return total + (unread ? 1 : 0);
        }, 0);
    }

    function setBadge(count) {
        if (!badge) return;
        if (!count) {
            badge.classList.add('is-hidden');
            badge.textContent = '0';
            return;
        }
        badge.classList.remove('is-hidden');
        badge.textContent = count > 99 ? '99+' : String(count);
    }

    function formatStatus(status) {
        if (!status) return '';
        switch (status) {
            case 'waiting':
                return '• Chờ xác nhận';
            case 'assigned':
                return '• Đã xếp bàn';
            case 'received':
                return '• Đã nhận bàn';
            case 'cancel':
                return '• Đã hủy';
            default:
                return '';
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getVisibleItems(items) {
        if (currentFilter === 'all') {
            return items;
        }
        return items.filter(item => normalizeType(item.type) === currentFilter);
    }

    function renderList(items) {
        if (!list) return;
        const visibleItems = getVisibleItems(items);
        if (!visibleItems.length) {
            list.innerHTML = '<div class="notify-empty">Chưa có thông báo.</div>';
            return;
        }

        list.innerHTML = visibleItems.map(item => {
            const type = normalizeType(item.type);
            const isRead = Number(item.id) <= getLastSeen(type);
            const statusText = type === 'booking' ? formatStatus(item.status) : '';
            const safeTitle = escapeHtml(item.title || 'Thông báo');
            const safeMessage = escapeHtml(item.message || '');
            const safeTime = escapeHtml(item.time || '');
            const iconClass = type === 'contact' ? 'fa-regular fa-envelope' : 'fa-regular fa-bell';
            const iconWrapClass = type === 'contact' ? 'notify-icon-wrap contact' : 'notify-icon-wrap';
            const messageLine = statusText ? `${safeMessage} ${statusText}` : safeMessage;
            return `
                <div class="notify-item ${isRead ? 'read' : ''}" data-id="${item.id}" data-url="${item.url || ''}" data-type="${type}">
                    <div class="${iconWrapClass}">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="notify-content">
                        <div class="notify-item-title">${safeTitle}</div>
                        <div class="notify-item-message">${messageLine}</div>
                        <div class="notify-item-time">${safeTime}</div>
                    </div>
                    <span class="notify-dot"></span>
                </div>
            `;
        }).join('');
    }

    function refreshUI() {
        renderList(currentItems);
        setBadge(getUnreadCount(currentItems));
    }

    function markRead(type, id) {
        const normalized = normalizeType(type);
        const nextId = Number.isFinite(id) ? id : 0;
        if (!nextId) return;
        if (nextId > getLastSeen(normalized)) {
            lastSeen[normalized] = nextId;
            saveLastSeen();
            refreshUI();
        }
    }

    function markReadAll() {
        const nextBooking = latestIds.booking || getMaxId('booking', currentItems);
        const nextContact = latestIds.contact || getMaxId('contact', currentItems);
        if (nextBooking > lastSeen.booking) {
            lastSeen.booking = nextBooking;
        }
        if (nextContact > lastSeen.contact) {
            lastSeen.contact = nextContact;
        }
        saveLastSeen();
        refreshUI();
    }

    function playSound() {
        try {
            if (!window.AudioContext) return;
            if (!audioCtx) {
                audioCtx = new AudioContext();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }

            const now = audioCtx.currentTime;
            const volume = 0.2;
            const tones = [
                { freq: 784, start: 0.0, dur: 0.12 },
                { freq: 988, start: 0.12, dur: 0.14 },
                { freq: 1318, start: 0.27, dur: 0.18 }
            ];

            const scheduleTone = (frequency, startTime, duration) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(frequency, startTime);

                gain.gain.setValueAtTime(0.0001, startTime);
                gain.gain.linearRampToValueAtTime(volume, startTime + 0.01);
                gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.start(startTime);
                osc.stop(startTime + duration + 0.02);
            };

            tones.forEach(tone => {
                scheduleTone(tone.freq, now + tone.start, tone.dur);
            });
        } catch (e) {
            console.warn('Không thể phát âm thanh thông báo', e);
        }
    }

    function unlockAudio() {
        if (!window.AudioContext) return;
        if (!audioCtx) {
            audioCtx = new AudioContext();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    async function fetchNotifications() {
        try {
            const res = await fetch(`${baseUrl}/pos/notifications?limit=20`, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();

            currentItems = Array.isArray(data.items) ? data.items : [];

            const serverLatest = data.latest_ids || {};
            latestIds = {
                booking: Number(serverLatest.booking) || getMaxId('booking', currentItems),
                contact: Number(serverLatest.contact) || getMaxId('contact', currentItems)
            };

            refreshUI();

            if (!suppressSound) {
                const newBookingItems = currentItems.filter(item => {
                    return normalizeType(item.type) === 'booking' && Number(item.id) > lastFetched.booking;
                });
                const newContactItems = currentItems.filter(item => {
                    return normalizeType(item.type) === 'contact' && Number(item.id) > lastFetched.contact;
                });

                if (newBookingItems.length || newContactItems.length) {
                    playSound();
                    if (typeof showToast === 'function') {
                        if (newBookingItems.length) {
                            if (newBookingItems.length === 1) {
                                showToast(`Có đặt bàn mới: ${newBookingItems[0].message || ''}`, 'info');
                            } else {
                                showToast(`Có ${newBookingItems.length} đặt bàn mới`, 'info');
                            }
                        }
                        if (newContactItems.length) {
                            if (newContactItems.length === 1) {
                                showToast(`Bạn có liên hệ mới: ${newContactItems[0].message || ''}`, 'info');
                            } else {
                                showToast(`Có ${newContactItems.length} liên hệ mới`, 'info');
                            }
                        }
                    }
                }
            }

            lastFetched.booking = Math.max(lastFetched.booking, latestIds.booking || 0);
            lastFetched.contact = Math.max(lastFetched.contact, latestIds.contact || 0);

            if (suppressSound) {
                suppressSound = false;
            }
        } catch (e) {
            console.warn('Không thể tải thông báo', e);
        }
    }

    function toggleDropdown(forceOpen = null) {
        const shouldOpen = forceOpen === null ? !dropdown.classList.contains('open') : forceOpen;
        if (shouldOpen) {
            dropdown.classList.add('open');
            dropdown.setAttribute('aria-hidden', 'false');
            notifyBtn.setAttribute('aria-expanded', 'true');
        } else {
            dropdown.classList.remove('open');
            dropdown.setAttribute('aria-hidden', 'true');
            notifyBtn.setAttribute('aria-expanded', 'false');
        }
    }

    notifyBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const willOpen = !dropdown.classList.contains('open');
        toggleDropdown();
        if (willOpen) {
            fetchNotifications();
        }
    });

    document.addEventListener('click', (e) => {
        if (!notifyWrap.contains(e.target)) {
            toggleDropdown(false);
        }
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            markReadAll();
        });
    }

    if (tabButtons.length) {
        tabButtons.forEach(tab => {
            tab.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tab.classList.add('active');
                currentFilter = tab.dataset.filter || 'all';
                renderList(currentItems);
            });
        });
    }

    list.addEventListener('click', (e) => {
        const item = e.target.closest('.notify-item');
        if (!item) return;
        const id = parseInt(item.dataset.id || '0', 10);
        const type = item.dataset.type || 'booking';
        const url = item.dataset.url;
        markRead(type, id);
        if (url) {
            window.location.href = url;
        }
    });

    document.addEventListener('click', unlockAudio, { once: true });

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
});
