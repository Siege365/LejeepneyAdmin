/* ========================================
   CUSTOMER SERVICE INDEX PAGE JS
   Ticket listing page functionality
   ======================================== */

const CustomerServiceIndex = {
    emailjsInitialized: false,
    
    init() {
        this.initBulkActions();
        this.initFilters();
        this.initEmailJS();
        this.setupEventListeners();
    },
    
    // Initialize bulk actions
    initBulkActions() {
        if (typeof BulkActions !== 'undefined') {
            BulkActions.init('.data-table', '.bulk-actions-container');
        }
    },
    
    // Initialize filters
    initFilters() {
        if (typeof Filters !== 'undefined') {
            Filters.init('.filters-form');
        }
    },
    
    // Initialize EmailJS
    initEmailJS() {
        if (typeof emailjs !== 'undefined' && !this.emailjsInitialized) {
            // Initialize with your EmailJS public key
            emailjs.init(window.EMAILJS_PUBLIC_KEY || 'YOUR_PUBLIC_KEY');
            this.emailjsInitialized = true;
        }
    },
    
    setupEventListeners() {
        // Bulk action buttons
        document.querySelectorAll('[data-bulk-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.bulkAction;
                this.handleBulkAction(action);
            });
        });
    },
    
    // Handle bulk actions
    async handleBulkAction(action) {
        const selected = BulkActions?.getSelected() || [];
        
        if (selected.length === 0) {
            Toast?.warning('Please select at least one ticket');
            return;
        }
        
        const actionConfig = {
            'resolve': {
                url: '/admin/customer-service/bulk-resolve',
                confirm: `Are you sure you want to mark ${selected.length} ticket(s) as resolved?`,
                successMsg: 'Tickets marked as resolved'
            },
            'archive': {
                url: '/admin/customer-service/bulk-archive',
                confirm: `Are you sure you want to archive ${selected.length} ticket(s)?`,
                successMsg: 'Tickets archived successfully'
            },
            'delete': {
                url: '/admin/customer-service/bulk-delete',
                confirm: `Are you sure you want to delete ${selected.length} ticket(s)? This action cannot be undone.`,
                successMsg: 'Tickets deleted successfully'
            }
        };
        
        const config = actionConfig[action];
        if (!config) return;
        
        if (typeof Modal !== 'undefined') {
            Modal.confirm({
                title: 'Confirm Action',
                message: config.confirm,
                confirmText: 'Yes, proceed',
                confirmClass: action === 'delete' ? 'btn-danger' : 'btn-primary',
                onConfirm: async () => {
                    try {
                        await BulkActions.submitAction(config.url, action);
                        Toast?.success(config.successMsg);
                        setTimeout(() => window.location.reload(), 1000);
                    } catch (error) {
                        Toast?.error('Action failed. Please try again.');
                    }
                }
            });
        } else {
            if (confirm(config.confirm)) {
                try {
                    await BulkActions.submitAction(config.url, action);
                    window.location.reload();
                } catch (error) {
                    alert('Action failed. Please try again.');
                }
            }
        }
    },
    
    // Flag ticket
    async flagTicket(ticketId, flag = true) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(`/admin/customer-service/${ticketId}/flag`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ flagged: flag })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success(flag ? 'Ticket flagged' : 'Flag removed');
                window.location.reload();
            } else {
                Toast?.error(data.message || 'Failed to update flag');
            }
        } catch (error) {
            console.error('Flag error:', error);
            Toast?.error('Failed to update flag');
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => CustomerServiceIndex.init());

// Export for use
window.CustomerServiceIndex = CustomerServiceIndex;
