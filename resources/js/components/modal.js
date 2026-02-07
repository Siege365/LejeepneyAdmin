/* ========================================
   MODAL COMPONENT
   Reusable modal dialog functionality
   ======================================== */

const Modal = {
    activeModals: [],
    
    open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        this.activeModals.push(modalId);
        
        // Focus first focusable element
        const focusable = modal.querySelector('input, button, select, textarea, [tabindex]:not([tabindex="-1"])');
        if (focusable) focusable.focus();
        
        return modal;
    },
    
    close(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('active');
        this.activeModals = this.activeModals.filter(id => id !== modalId);
        
        if (this.activeModals.length === 0) {
            document.body.style.overflow = '';
        }
    },
    
    closeAll() {
        this.activeModals.forEach(id => {
            const modal = document.getElementById(id);
            if (modal) modal.classList.remove('active');
        });
        this.activeModals = [];
        document.body.style.overflow = '';
    },
    
    confirm(options = {}) {
        const {
            title = 'Confirm',
            message = 'Are you sure?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            confirmClass = 'btn-danger',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;
        
        // Create modal element
        const modalId = 'confirm-modal-' + Date.now();
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal-backdrop';
        modal.innerHTML = `
            <div class="modal-container modal-sm">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button class="modal-close-btn" onclick="Modal.close('${modalId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="Modal.close('${modalId}')">${cancelText}</button>
                    <button class="btn ${confirmClass}" id="${modalId}-confirm">${confirmText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Setup confirm button
        document.getElementById(`${modalId}-confirm`).addEventListener('click', () => {
            onConfirm();
            Modal.close(modalId);
            setTimeout(() => modal.remove(), 300);
        });
        
        // Setup cancel on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                onCancel();
                Modal.close(modalId);
                setTimeout(() => modal.remove(), 300);
            }
        });
        
        // Open the modal
        this.open(modalId);
        
        return modalId;
    },
    
    init() {
        // Close modals on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModals.length > 0) {
                const lastModalId = this.activeModals[this.activeModals.length - 1];
                this.close(lastModalId);
            }
        });
        
        // Close modal on backdrop click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-backdrop')) {
                const modalId = e.target.id;
                if (modalId) this.close(modalId);
            }
        });
        
        // Setup close buttons
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal-backdrop');
                if (modal && modal.id) {
                    this.close(modal.id);
                }
            });
        });
        
        // Setup open triggers
        document.querySelectorAll('[data-modal-open]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.getAttribute('data-modal-open');
                this.open(modalId);
            });
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => Modal.init());

// Export for use
window.Modal = Modal;
