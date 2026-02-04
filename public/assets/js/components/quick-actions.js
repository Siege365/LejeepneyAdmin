/* ========================================
   QUICK ACTIONS DROPDOWN COMPONENT
   Navbar quick actions functionality
   ======================================== */

const QuickActions = {
    init() {
        const trigger = document.querySelector('.quick-actions-btn');
        const menu = document.querySelector('.quick-actions-menu');
        
        if (!trigger || !menu) return;
        
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('active');
        });
        
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.quick-actions')) {
                menu.classList.remove('active');
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                menu.classList.remove('active');
            }
        });
    },
    
    close() {
        const menu = document.querySelector('.quick-actions-menu');
        if (menu) menu.classList.remove('active');
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => QuickActions.init());

// Export for use
window.QuickActions = QuickActions;
