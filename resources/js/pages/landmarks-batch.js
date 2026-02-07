/**
 * Landmarks Batch Delete Functionality
 * Handles bulk delete operations with double confirmation
 */

// Global state
let selectedItems = new Set();

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCheckboxes();
});

/**
 * Initialize checkbox event listeners
 */
function initializeCheckboxes() {
    const selectAll = document.getElementById('selectAllLandmarks');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    // Select/deselect all functionality
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                updateSelectedItems(checkbox);
            });
        });
    }

    // Individual checkbox change
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedItems(this);
            updateSelectAllState();
        });
    });
}

/**
 * Update selected items set
 */
function updateSelectedItems(checkbox) {
    const id = checkbox.value;
    const name = checkbox.dataset.name;

    if (checkbox.checked) {
        selectedItems.add({ id, name });
    } else {
        selectedItems = new Set([...selectedItems].filter(item => item.id !== id));
    }

    updateBulkActionsBar();
}

/**
 * Update select all checkbox state
 */
function updateSelectAllState() {
    const selectAll = document.getElementById('selectAllLandmarks');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;

    if (selectAll) {
        selectAll.checked = checkedCount === rowCheckboxes.length && rowCheckboxes.length > 0;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < rowCheckboxes.length;
    }
}

/**
 * Update bulk actions bar visibility and count
 */
function updateBulkActionsBar() {
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    const count = selectedItems.size;

    if (count > 0) {
        bulkActionsContainer.classList.add('active');
    } else {
        bulkActionsContainer.classList.remove('active');
    }
}

/**
 * Clear all selections
 */
function clearSelection() {
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAllLandmarks');

    rowCheckboxes.forEach(checkbox => checkbox.checked = false);
    if (selectAll) selectAll.checked = false;

    selectedItems.clear();
    updateBulkActionsBar();
}

/**
 * Show first confirmation modal
 */
function showBatchDeleteModal() {
    if (selectedItems.size === 0) {
        return;
    }

    const modal = document.getElementById('batchDeleteModal');
    const deleteCount = document.getElementById('deleteCount');
    const deleteList = document.getElementById('deleteList');

    // Update count
    deleteCount.textContent = selectedItems.size;

    // Build list of items to delete
    deleteList.innerHTML = '';
    selectedItems.forEach(item => {
        const li = document.createElement('li');
        li.textContent = item.name;
        deleteList.appendChild(li);
    });

    // Show modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Close first confirmation modal
 */
function closeBatchDeleteModal() {
    const modal = document.getElementById('batchDeleteModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Show final confirmation modal (second confirmation)
 */
function showFinalConfirmation() {
    closeBatchDeleteModal();
    
    const modal = document.getElementById('finalConfirmModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/**
 * Close final confirmation modal
 */
function closeFinalConfirmation() {
    const modal = document.getElementById('finalConfirmModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

/**
 * Confirm and execute batch delete
 */
async function confirmBatchDelete() {
    const ids = [...selectedItems].map(item => item.id);
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const batchDeleteUrl = bulkActionsBar.dataset.batchDeleteUrl;

    try {
        const response = await fetch(batchDeleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Close modal
            closeFinalConfirmation();

            // Show success message
            if (typeof Toast !== 'undefined') {
                Toast.success(data.message || 'Landmarks deleted successfully');
            }

            // Reload page to update list
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Failed to delete landmarks');
        }
    } catch (error) {
        console.error('Batch delete error:', error);
        
        closeFinalConfirmation();
        
        if (typeof Toast !== 'undefined') {
            Toast.error(error.message || 'An error occurred while deleting landmarks');
        } else {
            alert('Error: ' + (error.message || 'Failed to delete landmarks'));
        }
    }
}

// Close modals when clicking backdrop
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-backdrop')) {
        closeBatchDeleteModal();
        closeFinalConfirmation();
    }
});

// Close modals on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeBatchDeleteModal();
        closeFinalConfirmation();
    }
});

// Expose to global scope for inline onclick handlers
window.clearSelection = clearSelection;
window.showBatchDeleteModal = showBatchDeleteModal;
window.closeBatchDeleteModal = closeBatchDeleteModal;
window.showFinalConfirmation = showFinalConfirmation;
window.closeFinalConfirmation = closeFinalConfirmation;
window.confirmBatchDelete = confirmBatchDelete;
