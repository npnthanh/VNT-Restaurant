document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');
    const errorBox = document.getElementById('loginError');

    if (!form) return;

    const showError = (message) => {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.style.display = 'flex';
    };

    window.addEventListener('pageshow', (event) => {
        const navEntry = performance.getEntriesByType('navigation')[0];
        const restoredFromHistory = event.persisted || navEntry?.type === 'back_forward';

        // A restored login page may still contain an old hidden CSRF token.
        if (restoredFromHistory) {
            window.location.reload();
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (errorBox) {
            errorBox.textContent = '';
            errorBox.style.display = 'none';
        }

        const formData = new FormData(form);
        if (e.submitter && e.submitter.name) {
            formData.set(e.submitter.name, e.submitter.value);
        }

        try {
            const actionUrl = form.getAttribute('action');
            const res = await fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (res.status === 419) {
                showError('Phiên đăng nhập đã thay đổi. Trang sẽ được làm mới để bạn tiếp tục.');
                setTimeout(() => window.location.reload(), 800);
                return;
            }

            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                throw new Error('Unexpected response type');
            }

            const data = await res.json();
            if (data.ok && data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            const message = data.message || 'Tên đăng nhập hoặc mật khẩu chưa đúng.';
            showError(message);
        } catch (err) {
            showError('Có lỗi xảy ra, vui lòng thử lại.');
        }
    });
});
