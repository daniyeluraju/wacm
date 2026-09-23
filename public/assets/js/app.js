/**
 * WACM - WhatsApp Assistant & Contact Manager
 * Main UI & Client Utility Library
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Toast Notification Manager
    window.Toast = {
        container: null,
        init() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        },
        show(type, message, duration = 4000) {
            this.init();
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 0.85rem; margin-bottom: 2px; text-transform: capitalize;">${type}</div>
                    <div style="font-size: 0.85rem; color: #cbd5e1;">${message}</div>
                </div>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #64748b; cursor: pointer; font-size: 1.1rem; line-height: 1;">&times;</button>
            `;
            this.container.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    };

    // 2. Copy to Clipboard Utility
    window.copyToClipboard = async (text, buttonElement) => {
        try {
            await navigator.clipboard.writeText(text);
            Toast.show('success', 'Copied to clipboard!');
            if (buttonElement) {
                const originalText = buttonElement.innerHTML;
                buttonElement.innerHTML = '<span>&#10003; Copied</span>';
                buttonElement.disabled = true;
                setTimeout(() => {
                    buttonElement.innerHTML = originalText;
                    buttonElement.disabled = false;
                }, 2000);
            }
        } catch (err) {
            console.error('Failed to copy: ', err);
            Toast.show('error', 'Could not copy to clipboard.');
        }
    };

    // 3. Mobile Sidebar Toggle
    const mobileToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    }

    // 4. AJAX API Request Helper with automatic CSRF token inclusion
    window.apiFetch = async (url, options = {}) => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        options.headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            ...(options.headers || {})
        };

        const res = await fetch(url, options);
        if (res.status === 419) {
            Toast.show('warning', 'Session expired. Reloading...');
            setTimeout(() => window.location.reload(), 1500);
            return null;
        }
        return res;
    };
});
