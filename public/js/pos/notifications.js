document.addEventListener('DOMContentLoaded', () => {
    const notifyWrap = document.getElementById('notifyWrap');
    const notifyBtn = document.getElementById('notifyBtn');
    const dropdown = document.getElementById('notifyDropdown');
    const list = document.getElementById('notifyList');
    const badge = document.getElementById('notifyBadge');
    const markAllBtn = document.getElementById('notifyMarkAll');

    if (!notifyWrap || !notifyBtn || !dropdown || !list || !badge) return;

    const baseUrl = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
    const storageKey = 'pos_notify_last_seen_id';

    let lastSeenId = parseInt(localStorage.getItem(storageKey) || '0', 10);
    let lastFetchedId = 0;
    let latestId = 0;
    let currentItems = [];
    let suppressSound = true;
    let audioCtx = null;

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

    function renderList(items) {
        if (!list) return;
        if (!items.length) {
            list.innerHTML = '<div class="notify-empty">Chưa có thông báo.</div>';
            return;
        }

        list.innerHTML = items.map(item => {
            const isRead = item.id <= lastSeenId;
            const statusText = formatStatus(item.status);
            const safeTitle = escapeHtml(item.title || 'Thông báo');
            const safeMessage = escapeHtml(item.message || '');
            const safeTime = escapeHtml(item.time || '');
            return `
                <div class="notify-item ${isRead ? 'read' : ''}" data-id="${item.id}" data-url="${item.url || ''}">
                    <div class="notify-icon-wrap">
                        <i class="fa-regular fa-bell"></i>
                    </div>
                    <div class="notify-content">
                        <div class="notify-item-title">${safeTitle}</div>
                        <div class="notify-item-message">${safeMessage} ${statusText}</div>
                        <div class="notify-item-time">${safeTime}</div>
                    </div>
                    <span class="notify-dot"></span>
                </div>
            `;
        }).join('');
    }

    function markReadUpTo(id) {
        const nextId = Number.isFinite(id) ? id : latestId;
        if (!nextId) return;
        if (nextId > lastSeenId) {
            lastSeenId = nextId;
            localStorage.setItem(storageKey, String(lastSeenId));
            renderList(currentItems);
            const unreadCount = currentItems.filter(item => item.id > lastSeenId).length;
            setBadge(unreadCount);
        }
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
            latestId = Number(data.latest_id) || (currentItems[0]?.id || 0);

            renderList(currentItems);

            const unreadCount = currentItems.filter(item => item.id > lastSeenId).length;
            setBadge(unreadCount);

            if (!suppressSound && lastFetchedId && latestId > lastFetchedId) {
                const newItems = currentItems.filter(item => item.id > lastFetchedId);
                if (newItems.length) {
                    playSound();
                    if (typeof showToast === 'function') {
                        if (newItems.length === 1) {
                            showToast(`Có đặt bàn mới: ${newItems[0].message || ''}`, 'info');
                        } else {
                            showToast(`Có ${newItems.length} đặt bàn mới`, 'info');
                        }
                    }
                }
            }

            if (latestId > lastFetchedId) {
                lastFetchedId = latestId;
            }
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
            markReadUpTo(latestId);
        });
    }

    list.addEventListener('click', (e) => {
        const item = e.target.closest('.notify-item');
        if (!item) return;
        const id = parseInt(item.dataset.id || '0', 10);
        const url = item.dataset.url;
        markReadUpTo(id);
        if (url) {
            window.location.href = url;
        }
    });

    document.addEventListener('click', unlockAudio, { once: true });

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
});
