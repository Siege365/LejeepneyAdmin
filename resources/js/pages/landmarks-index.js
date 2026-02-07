/* ========================================
   LANDMARKS INDEX PAGE JS
   Landmarks listing page functionality
   ======================================== */

const LandmarksIndex = {
    init() {
        this.initBulkActions();
        this.initFilters();
        this.initDeleteConfirmations();
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
    
    // Setup delete confirmations
    initDeleteConfirmations() {
        document.querySelectorAll('.delete-landmark-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = btn.closest('form');
                const landmarkName = btn.dataset.landmarkName || 'this landmark';
                
                this.confirmDelete(form, landmarkName);
            });
        });
    },
    
    // Confirm delete
    confirmDelete(form, name) {
        if (typeof Modal !== 'undefined') {
            Modal.confirm({
                title: 'Delete Landmark',
                message: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
                confirmText: 'Yes, delete',
                confirmClass: 'btn-danger',
                onConfirm: () => form.submit()
            });
        } else if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            form.submit();
        }
    },
    
    // Handle bulk delete
    async bulkDelete() {
        const selected = BulkActions?.getSelected() || [];
        
        if (selected.length === 0) {
            Toast?.warning('Please select at least one landmark');
            return;
        }
        
        const confirmMsg = `Are you sure you want to delete ${selected.length} landmark(s)? This action cannot be undone.`;
        
        if (typeof Modal !== 'undefined') {
            Modal.confirm({
                title: 'Delete Landmarks',
                message: confirmMsg,
                confirmText: 'Yes, delete all',
                confirmClass: 'btn-danger',
                onConfirm: async () => {
                    try {
                        await BulkActions.submitAction('/admin/landmarks/bulk-delete', 'delete');
                        Toast?.success('Landmarks deleted successfully');
                        setTimeout(() => window.location.reload(), 1000);
                    } catch (error) {
                        Toast?.error('Failed to delete landmarks');
                    }
                }
            });
        } else if (confirm(confirmMsg)) {
            try {
                await BulkActions.submitAction('/admin/landmarks/bulk-delete', 'delete');
                window.location.reload();
            } catch (error) {
                alert('Failed to delete landmarks');
            }
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => LandmarksIndex.init());

// Export for use
window.LandmarksIndex = LandmarksIndex;
