/**
 * Routes Index JavaScript
 * Handles table filtering and search for routes list
 * Lejeepney Admin Panel
 */

(function() {
    'use strict';

    // DOM Elements
    const elements = {
        searchInput: document.getElementById('searchInput'),
        statusFilter: document.getElementById('statusFilter'),
        table: document.getElementById('routesTable'),
        emptyRow: document.getElementById('emptyRow')
    };

    /**
     * Initialize the routes index page
     */
    function init() {
        if (elements.searchInput) {
            elements.searchInput.addEventListener('input', filterTable);
        }

        if (elements.statusFilter) {
            elements.statusFilter.addEventListener('change', filterTable);
        }
    }

    /**
     * Filter table based on search and status
     */
    function filterTable() {
        const searchTerm = elements.searchInput ? elements.searchInput.value.toLowerCase().trim() : '';
        const statusFilter = elements.statusFilter ? elements.statusFilter.value : '';
        
        if (!elements.table) return;

        const tbody = elements.table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr:not(#emptyRow)');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.dataset.status || '';

            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
                
                // Highlight matching text
                if (searchTerm) {
                    highlightText(row, searchTerm);
                } else {
                    removeHighlight(row);
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Show/hide empty state
        updateEmptyState(visibleCount, rows.length);
    }

    /**
     * Highlight matching text in row
     */
    function highlightText(row, term) {
        // Skip action column (last column)
        const cells = row.querySelectorAll('td:not(:last-child)');
        
        cells.forEach(cell => {
            // Skip cells with special content (like color boxes)
            if (cell.querySelector('div, span.badge')) return;
            
            const originalText = cell.textContent;
            if (originalText.toLowerCase().includes(term)) {
                const regex = new RegExp(`(${escapeRegex(term)})`, 'gi');
                const highlightedText = originalText.replace(regex, '<mark>$1</mark>');
                
                // Only update if there's actual text content
                if (cell.childNodes.length === 1 && cell.childNodes[0].nodeType === 3) {
                    cell.innerHTML = highlightedText;
                }
            }
        });
    }

    /**
     * Remove highlight from row
     */
    function removeHighlight(row) {
        const marks = row.querySelectorAll('mark');
        marks.forEach(mark => {
            const parent = mark.parentNode;
            parent.replaceChild(document.createTextNode(mark.textContent), mark);
            parent.normalize();
        });
    }

    /**
     * Escape special regex characters
     */
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /**
     * Update empty state visibility
     */
    function updateEmptyState(visibleCount, totalRows) {
        // Check if there's a no-results row
        let noResultsRow = elements.table.querySelector('#noResultsRow');
        
        if (visibleCount === 0 && totalRows > 0) {
            // No matching results - show message
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="9">
                        <div class="empty-state" style="padding: 2rem;">
                            <i class="fa-solid fa-search" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <h3 style="margin-bottom: 0.5rem;">No Matching Routes</h3>
                            <p style="color: var(--text-muted);">Try adjusting your search or filter criteria.</p>
                        </div>
                    </td>
                `;
                elements.table.querySelector('tbody').appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }

    /**
     * Add keyboard shortcut for search
     */
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F focuses search
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                if (elements.searchInput) {
                    e.preventDefault();
                    elements.searchInput.focus();
                    elements.searchInput.select();
                }
            }
            
            // Escape clears search
            if (e.key === 'Escape' && document.activeElement === elements.searchInput) {
                elements.searchInput.value = '';
                filterTable();
                elements.searchInput.blur();
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            init();
            initKeyboardShortcuts();
        });
    } else {
        init();
        initKeyboardShortcuts();
    }

})();
