// ===== DOM Ready =====
document.addEventListener('DOMContentLoaded', function() {
    initSidebar();
    initToasts();
    initModals();
    initPhotoDragDrop();
});

// ===== Sidebar =====
function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggleBtns = document.querySelectorAll('[data-sidebar-toggle]');
    const mainContent = document.querySelector('[data-main-content]');

    if (!sidebar) return;

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', toggleSidebar);
    });

    overlay.addEventListener('click', toggleSidebar);
}

// ===== Toasts =====
function initToasts() {
    const toasts = document.querySelectorAll('[data-toast]');
    
    toasts.forEach(toast => {
        // Auto-remove after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 5000);
    });
}

// ===== Modals =====
function initModals() {
    const modals = document.querySelectorAll('[data-modal]');
    
    modals.forEach(modal => {
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
}

// Close confirmation dialog
function closeConfirmDialog() {
    document.getElementById('confirm-dialog').classList.remove('active');
}

// Show confirmation dialog
function showConfirmDialog(title, message, onConfirm) {
    const dialog = document.getElementById('confirm-dialog');
    document.getElementById('confirm-title').textContent = title;
    document.getElementById('confirm-message').textContent = message;
    
    document.getElementById('confirm-btn').onclick = function() {
        onConfirm();
        closeConfirmDialog();
    };
    
    dialog.classList.add('active');
}

// ===== Photo Drag & Drop =====
function initPhotoDragDrop() {
    const uploadArea = document.getElementById('photo-upload-area');
    const previews = document.getElementById('photo-previews');
    const fileInput = document.getElementById('photo-upload');
    
    if (!uploadArea || !previews || !fileInput) return;

    let draggedItem = null;

    // Drag over
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('drag-over');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('drag-over');
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            fileInput.dispatchEvent(new Event('change'));
        }
    });

    // Photo item drag and drop
    previews.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('photo-item')) {
            draggedItem = e.target;
            e.target.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }
    });

    previews.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('photo-item')) {
            e.target.classList.remove('dragging');
            draggedItem = null;
        }
    });

    previews.addEventListener('dragover', function(e) {
        e.preventDefault();
        const targetItem = e.target.closest('.photo-item');
        if (targetItem && targetItem !== draggedItem) {
            previews.classList.add('drag-over');
            const rect = targetItem.getBoundingClientRect();
            const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5;
            
            if (next) {
                targetItem.parentNode.insertBefore(draggedItem, targetItem.nextSibling);
            } else {
                targetItem.parentNode.insertBefore(draggedItem, targetItem);
            }
        }
    });

    previews.addEventListener('dragleave', function(e) {
        previews.classList.remove('drag-over');
    });

    previews.addEventListener('drop', function(e) {
        e.preventDefault();
        previews.classList.remove('drag-over');
        
        // Save new order
        savePhotoOrder();
    });
}

// Save photo order via AJAX
function savePhotoOrder() {
    const previews = document.getElementById('photo-previews');
    const propertyId = window.location.pathname.split('/').pop();
    
    if (!previews || !propertyId) return;

    const photoIds = [];
    previews.querySelectorAll('.photo-item').forEach(item => {
        const id = item.dataset.photoId;
        if (id) photoIds.push(parseInt(id));
    });

    if (photoIds.length === 0) return;

    fetch('/properties/' + propertyId + '/photos/reorder', {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ photo_ids: photoIds })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Failed to save order');
        }
    })
    .catch(error => {
        console.error('Error saving order:', error);
    });
}

// ===== AJAX Helpers =====
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]').content;
}

function showLoading() {
    document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

// ===== Form Helpers =====
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('has-error');
            isValid = false;
        } else {
            field.classList.remove('has-error');
        }
    });
    
    return isValid;
}

// ===== Number Formatting =====
function formatNumber(num) {
    return new Intl.NumberFormat().format(num);
}

function formatCurrency(num, currency = 'EUR') {
    return new Intl.NumberFormatter(undefined, {
        style: 'currency',
        currency: currency
    }).format(num);
}

// ===== Date Formatting =====
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// ===== Confirmation Helpers =====
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ===== URL Helpers =====
function route(name, params = {}) {
    // This would need to be implemented based on your routing system
    // For now, just return a simple URL
    let url = '/' + name.replace('.', '/');
    for (const [key, value] of Object.entries(params)) {
        url = url.replace(`{${key}}`, value);
    }
    return url;
}

// ===== Auto-hide Alerts =====
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert.auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

// Initialize auto-hide alerts
document.addEventListener('DOMContentLoaded', initAutoHideAlerts);
