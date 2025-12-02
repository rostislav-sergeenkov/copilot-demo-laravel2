/**
 * Expense Tracker - JavaScript
 * Vanilla JS for interactive functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss snackbar after 5 seconds
    const snackbar = document.getElementById('snackbar');
    if (snackbar) {
        setTimeout(function() {
            dismissSnackbar();
        }, 5000);
    }
});

/**
 * Dismiss the snackbar notification
 */
function dismissSnackbar() {
    const snackbar = document.getElementById('snackbar');
    if (snackbar) {
        snackbar.classList.add('hide');
        setTimeout(function() {
            snackbar.remove();
        }, 300);
    }
}

/**
 * Show delete confirmation modal
 * @param {Event} event - Click event
 * @param {number} expenseId - ID of the expense to delete
 * @param {string} description - Description of the expense
 */
function confirmDelete(event, expenseId, description) {
    event.preventDefault();
    
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    const descriptionEl = document.getElementById('deleteExpenseDescription');
    
    if (modal && form) {
        form.action = '/expenses/' + expenseId;
        if (descriptionEl) {
            descriptionEl.textContent = description;
        }
        openModal('deleteModal');
    }
}

/**
 * Open a modal dialog
 * @param {string} modalId - ID of the modal to open
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close a modal dialog
 * @param {string} modalId - ID of the modal to close
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Close modal when clicking outside
 */
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

/**
 * Close modal on Escape key
 */
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const activeModal = document.querySelector('.modal-overlay.active');
        if (activeModal) {
            activeModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
});

/**
 * Format number as currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format date for display
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
}
