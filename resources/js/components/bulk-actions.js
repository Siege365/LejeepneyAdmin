/* ========================================
   BULK ACTIONS COMPONENT
   Table bulk selection and actions
   ======================================== */

const BulkActions = {
    selectedItems: new Set(),
    container: null,
    
    init(tableSelector = '.data-table', containerSelector = '.bulk-actions-container') {
        this.table = document.querySelector(tableSelector);
        this.container = document.querySelector(containerSelector);
        
        if (!this.table) return;
        
        this.setupCheckboxes();
        this.updateUI();
    },
    
    setupCheckboxes() {
        // Select all checkbox
        const selectAll = this.table.querySelector('.select-all');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                this.toggleAll(e.target.checked);
            });
        }
        
        // Individual checkboxes
        this.table.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                this.toggle(e.target.value, e.target.checked);
            });
        });
    },
    
    toggle(id, checked) {
        if (checked) {
            this.selectedItems.add(id);
        } else {
            this.selectedItems.delete(id);
        }
        this.updateUI();
    },
    
    toggleAll(checked) {
        this.table.querySelectorAll('.row-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
            if (checked) {
                this.selectedItems.add(checkbox.value);
            } else {
                this.selectedItems.delete(checkbox.value);
            }
        });
        this.updateUI();
    },
    
    updateUI() {
        const count = this.selectedItems.size;
        const countDisplay = document.querySelector('.selected-count');
        
        if (countDisplay) {
            countDisplay.textContent = count;
        }
        
        // Show/hide bulk actions
        if (this.container) {
            if (count > 0) {
                this.container.classList.add('active');
            } else {
                this.container.classList.remove('active');
            }
        }
        
        // Update select all checkbox state
        const selectAll = this.table?.querySelector('.select-all');
        const checkboxes = this.table?.querySelectorAll('.row-checkbox');
        
        if (selectAll && checkboxes) {
            const allChecked = checkboxes.length > 0 && 
                Array.from(checkboxes).every(cb => cb.checked);
            const someChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = someChecked && !allChecked;
        }
    },
    
    getSelected() {
        return Array.from(this.selectedItems);
    },
    
    clear() {
        this.selectedItems.clear();
        this.table?.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.checked = false;
        });
        const selectAll = this.table?.querySelector('.select-all');
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        this.updateUI();
    },
    
    async submitAction(url, action, method = 'POST') {
        if (this.selectedItems.size === 0) {
            Toast?.warning('No items selected');
            return;
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    ids: this.getSelected(),
                    action: action
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success(data.message || 'Action completed successfully');
                this.clear();
                // Reload page or update UI
                if (data.reload) {
                    window.location.reload();
                }
            } else {
                Toast?.error(data.message || 'Action failed');
            }
            
            return data;
        } catch (error) {
            console.error('Bulk action error:', error);
            Toast?.error('An error occurred. Please try again.');
            throw error;
        }
    }
};

// Export for use
window.BulkActions = BulkActions;
