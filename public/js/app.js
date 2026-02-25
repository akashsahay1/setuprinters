/**
 * Setu Printers - Main Application JavaScript
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initApp();
    });

    /**
     * Initialize application
     */
    function initApp() {
        initFormHandlers();
        initTableActions();
    }

    /**
     * Initialize form handlers
     */
    function initFormHandlers() {
        const forms = document.querySelectorAll('.settings-form');

        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const button = form.querySelector('button[type="submit"]');
                const originalText = button.textContent;

                button.disabled = true;
                button.textContent = 'Saving...';

                // Simulate save action
                setTimeout(function() {
                    button.disabled = false;
                    button.textContent = originalText;
                    showNotification('Settings saved successfully!', 'success');
                }, 500);
            });
        });
    }

    /**
     * Initialize table action buttons
     */
    function initTableActions() {
        document.addEventListener('click', function(e) {
            if (e.target.matches('.btn-danger')) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this item?')) {
                    showNotification('Item deleted successfully!', 'success');
                }
            }

            if (e.target.matches('.btn-primary') && e.target.textContent === 'Edit') {
                e.preventDefault();
                showNotification('Edit functionality will be available soon.', 'info');
            }
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        type = type || 'info';

        // Remove existing notification
        const existing = document.querySelector('.notification');
        if (existing) {
            existing.remove();
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'notification notification-' + type;
        notification.textContent = message;

        // Add styles
        notification.style.cssText = [
            'position: fixed',
            'top: 20px',
            'right: 20px',
            'padding: 1rem 1.5rem',
            'border-radius: 8px',
            'color: white',
            'font-size: 0.875rem',
            'font-weight: 500',
            'z-index: 1000',
            'animation: slideIn 0.3s ease',
            'background-color: ' + (type === 'success' ? '#22c55e' : type === 'error' ? '#ef4444' : '#3b82f6')
        ].join(';');

        document.body.appendChild(notification);

        // Auto remove
        setTimeout(function() {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            notification.style.transition = 'all 0.3s ease';
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Add keyframe animation
    var style = document.createElement('style');
    style.textContent = '@keyframes slideIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }';
    document.head.appendChild(style);

})();
