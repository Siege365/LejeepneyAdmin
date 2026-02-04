/* ========================================
   TOAST NOTIFICATION COMPONENT
   Reusable toast notification system
   ======================================== */

const Toast = {
    container: null,
    
    init() {
        // Create container if doesn't exist
        if (!document.querySelector('.toast-container')) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.querySelector('.toast-container');
        }
    },
    
    show(message, type = 'info', duration = 4000) {
        if (!this.container) this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const iconMap = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        toast.innerHTML = `
            <i class="fas ${iconMap[type] || iconMap.info}"></i>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="Toast.dismiss(this.parentElement)">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        this.container.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this.dismiss(toast), duration);
        }
        
        return toast;
    },
    
    dismiss(toast) {
        if (!toast || !toast.parentElement) return;
        
        toast.classList.remove('show');
        toast.classList.add('hide');
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.parentElement.removeChild(toast);
            }
        }, 300);
    },
    
    success(message, duration) {
        return this.show(message, 'success', duration);
    },
    
    error(message, duration) {
        return this.show(message, 'error', duration);
    },
    
    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },
    
    info(message, duration) {
        return this.show(message, 'info', duration);
    },
    
    clear() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => Toast.init());

// Export for use
window.Toast = Toast;
