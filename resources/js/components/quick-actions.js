/* ========================================
   QUICK ACTIONS DROPDOWN COMPONENT
   Navbar quick actions functionality
   ======================================== */

const QuickActions = {
    init() {
        const container = document.querySelector('.quick-actions');
        const trigger = document.querySelector('.quick-actions-btn');
        
        if (!trigger || !container) return;
        
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            container.classList.toggle('open');
        });
        
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.quick-actions')) {
                container.classList.remove('open');
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                container.classList.remove('open');
            }
        });
    },
    
    close() {
        const container = document.querySelector('.quick-actions');
        if (container) container.classList.remove('open');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => QuickActions.init());

// Export for use
window.QuickActions = QuickActions;
