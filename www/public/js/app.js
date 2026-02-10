/**
 * App.js - Global Utilities
 */

// Toast notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// AJAX helper
async function apiRequest(url, options = {}) {
    try {
        const defaultOpts = {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        };

        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            defaultOpts.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        const response = await fetch(url, Object.assign({}, defaultOpts, options));
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        return { success: false, message: 'خطأ في الاتصال' };
    }
}

// Format currency
function formatCurrency(amount) {
    return parseFloat(amount || 0).toFixed(2);
}

// Format number
function formatNumber(num) {
    return parseFloat(num || 0).toLocaleString('en');
}

// Debounce
function debounce(func, wait = 300) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Confirm dialog
function confirmAction(message) {
    return confirm(message || 'هل أنت متأكد؟');
}

// Close modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

// Open modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}

// Delete with confirmation
async function deleteItem(url, confirmMsg) {
    if (!confirmAction(confirmMsg || 'هل تريد الحذف؟')) return;

    const result = await apiRequest(url, { method: 'DELETE' });
    if (result.success) {
        showToast(result.message || 'تم الحذف بنجاح');
        setTimeout(() => location.reload(), 500);
    } else {
        showToast(result.message || 'فشل الحذف', 'error');
    }
}

// Keyboard shortcut handler
document.addEventListener('keydown', function (e) {
    // F2 - Focus search
    if (e.key === 'F2') {
        e.preventDefault();
        const search = document.querySelector('.pos-barcode-input') || document.querySelector('[data-search]');
        if (search) search.focus();
    }
    // ESC - Close modals
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});
