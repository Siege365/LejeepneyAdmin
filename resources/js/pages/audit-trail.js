/**
 * Audit Trail Page JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const toggleFiltersBtn = document.getElementById('toggleFilters');
    const filtersPanel = document.getElementById('filtersPanel');

    // Toggle Filters Panel
    if (toggleFiltersBtn && filtersPanel) {
        toggleFiltersBtn.addEventListener('click', function() {
            const isVisible = filtersPanel.style.display !== 'none';
            filtersPanel.style.display = isVisible ? 'none' : 'block';
            
            // Update button icon
            const icon = toggleFiltersBtn.querySelector('i');
            if (icon) {
                icon.className = isVisible ? 'fa-solid fa-filter' : 'fa-solid fa-filter-circle-xmark';
            }
        });
    }
});
