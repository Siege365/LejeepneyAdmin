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

/* ========================================
   CUSTOMER SERVICE INDEX - DOUBLE CONFIRMATION MODALS
   Flag, Archive, Restore modal functions
   ======================================== */

let pendingFlagId = null;
let pendingArchiveId = null;
let pendingRestoreId = null;

// Flag modals
function showTicketFlagModal(id, subject, isFlagged) {
    pendingFlagId = id;
    const action = isFlagged ? 'Remove Flag' : 'Flag as Important';
    const msg = isFlagged 
        ? 'Are you sure you want to remove the flag from "' + subject + '"?' 
        : 'Are you sure you want to flag "' + subject + '" as important?';
    document.getElementById('flagModalTitle').textContent = action;
    document.getElementById('flagModalMessage').textContent = msg;
    document.getElementById('flagConfirmMessage').textContent = isFlagged 
        ? 'The flag will be removed from this ticket.' 
        : 'This ticket will be flagged as important.';
    document.getElementById('ticketFlagModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeTicketFlagModal() {
    document.getElementById('ticketFlagModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showTicketFlagConfirm() {
    closeTicketFlagModal();
    document.getElementById('ticketFlagForm').action = '/customer-service/' + pendingFlagId + '/flag';
    document.getElementById('ticketFlagConfirmModal').style.display = 'flex';
}
function closeTicketFlagConfirm() {
    document.getElementById('ticketFlagConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Archive modals
function showTicketArchiveModal(id, subject) {
    pendingArchiveId = id;
    document.getElementById('archiveTicketSubject').textContent = subject;
    document.getElementById('ticketArchiveModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeTicketArchiveModal() {
    document.getElementById('ticketArchiveModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showTicketArchiveConfirm() {
    closeTicketArchiveModal();
    document.getElementById('ticketArchiveForm').action = '/customer-service/' + pendingArchiveId + '/archive';
    document.getElementById('ticketArchiveConfirmModal').style.display = 'flex';
}
function closeTicketArchiveConfirm() {
    document.getElementById('ticketArchiveConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Restore modals
function showTicketRestoreModal(id, subject) {
    pendingRestoreId = id;
    document.getElementById('restoreTicketSubject').textContent = subject;
    document.getElementById('ticketRestoreModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeTicketRestoreModal() {
    document.getElementById('ticketRestoreModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showTicketRestoreConfirm() {
    closeTicketRestoreModal();
    document.getElementById('ticketRestoreForm').action = '/customer-service/' + pendingRestoreId + '/restore';
    document.getElementById('ticketRestoreConfirmModal').style.display = 'flex';
}
function closeTicketRestoreConfirm() {
    document.getElementById('ticketRestoreConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Bulk selection (used by inline checkboxes)
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.ticketCheckbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelection();
        });
    }
    
    document.querySelectorAll('.ticketCheckbox').forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });
});

function updateSelection() {
    const checkboxes = document.querySelectorAll('.ticketCheckbox:checked');
    const bulkContainer = document.getElementById('bulkActionsContainer');
    const selectedIdsInput = document.getElementById('selectedTicketIds');
    
    if (checkboxes.length > 0) {
        bulkContainer.classList.add('active');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        selectedIdsInput.value = ids.join(',');
    } else {
        bulkContainer.classList.remove('active');
        selectedIdsInput.value = '';
    }
}

function clearSelection() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.ticketCheckbox').forEach(cb => cb.checked = false);
    document.getElementById('bulkActionsContainer').classList.remove('active');
}

// Expose to global scope for inline onclick handlers
window.showTicketFlagModal = showTicketFlagModal;
window.closeTicketFlagModal = closeTicketFlagModal;
window.showTicketFlagConfirm = showTicketFlagConfirm;
window.closeTicketFlagConfirm = closeTicketFlagConfirm;
window.showTicketArchiveModal = showTicketArchiveModal;
window.closeTicketArchiveModal = closeTicketArchiveModal;
window.showTicketArchiveConfirm = showTicketArchiveConfirm;
window.closeTicketArchiveConfirm = closeTicketArchiveConfirm;
window.showTicketRestoreModal = showTicketRestoreModal;
window.closeTicketRestoreModal = closeTicketRestoreModal;
window.showTicketRestoreConfirm = showTicketRestoreConfirm;
window.closeTicketRestoreConfirm = closeTicketRestoreConfirm;
window.updateSelection = updateSelection;
window.clearSelection = clearSelection;
