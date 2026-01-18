/**
 * Admin JavaScript - Dashboard Layout
 * Lejeepney Admin Panel - Enhanced UX
 */

document.addEventListener('DOMContentLoaded', function() {
    initSidebarToggle();
    initAlertDismiss();
    initDeleteConfirmation();
    initSmoothScrolling();
    initCardHoverEffects();
    initTableRowSelection();
    initCountUpAnimation();
    initSearchFocus();
});

/**
 * Sidebar Toggle for Mobile
 */
function initSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (!sidebar || !sidebarToggle) return;
    
    // Create overlay element
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
    });
    
    // Close sidebar on overlay click
    overlay.addEventListener('click', function() {
        closeSidebar();
    });
    
    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
    
    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            closeSidebar();
        }
    });
    
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }
}

/**
 * Auto-dismiss Alerts with smooth animation
 */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Add close button if not exists
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            closeBtn.style.cssText = 'margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.7; transition: opacity 0.2s;';
            closeBtn.addEventListener('click', () => dismissAlert(alert));
            closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
            closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
            alert.appendChild(closeBtn);
        }
        
        // Auto dismiss after 5 seconds
        setTimeout(() => dismissAlert(alert), 5000);
    });
    
    function dismissAlert(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(-20px)';
        setTimeout(() => alert.remove(), 300);
    }
}

/**
 * Delete Confirmation with custom modal
 */
function initDeleteConfirmation() {
    const deleteForms = document.querySelectorAll('form[data-confirm]');
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.dataset.confirm || 'Are you sure you want to delete this item?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Smooth Scrolling
 */
function initSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Card Hover Effects
 */
function initCardHoverEffects() {
    const statCards = document.querySelectorAll('.stat-card');
    
    statCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

/**
 * Table Row Selection
 */
function initTableRowSelection() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            row.style.cursor = 'pointer';
            
            row.addEventListener('click', function(e) {
                // Don't trigger if clicking on action buttons
                if (e.target.closest('.action-btns') || e.target.closest('a')) return;
                
                // Toggle selection
                rows.forEach(r => r.classList.remove('selected'));
                this.classList.toggle('selected');
            });
        });
    });
}

/**
 * Count Up Animation for Stats
 */
function initCountUpAnimation() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target;
                const endValue = parseInt(target.textContent.replace(/,/g, '')) || 0;
                
                if (endValue > 0 && !target.dataset.animated) {
                    target.dataset.animated = 'true';
                    animateValue(target, 0, endValue, 1000);
                }
                
                observer.unobserve(target);
            }
        });
    }, { threshold: 0.5 });
    
    statNumbers.forEach(num => observer.observe(num));
}

/**
 * Animate numeric values
 */
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function (ease-out)
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const current = Math.floor(start + (end - start) * easeOut);
        
        element.textContent = formatNumber(current);
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    
    requestAnimationFrame(update);
}

/**
 * Format Number with Commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Search Focus Enhancement
 */
function initSearchFocus() {
    const searchInputs = document.querySelectorAll('.search-box input');
    
    searchInputs.forEach(input => {
        const searchBox = input.closest('.search-box');
        
        input.addEventListener('focus', function() {
            searchBox.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            searchBox.classList.remove('focused');
        });
        
        // Keyboard shortcut (Ctrl/Cmd + K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                input.focus();
            }
        });
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'info', duration = 3000) {
    // Remove existing toasts
    document.querySelectorAll('.toast').forEach(t => t.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : type === 'warning' ? '#F59E0B' : '#3B82F6'};
        color: white;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 9999;
        transform: translateX(120%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 500;
        font-size: 0.9375rem;
    `;
    toast.innerHTML = `
        <i class="fa-solid ${getToastIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
    });
    
    // Remove after duration
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
    
    return toast;
}

function getToastIcon(type) {
    const icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-exclamation',
        warning: 'fa-triangle-exclamation',
        info: 'fa-circle-info'
    };
    return icons[type] || icons.info;
}

/**
 * Debounce Function for Search
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Search Table Rows with highlight
 */
function initTableSearch(searchInput, table) {
    if (!searchInput || !table) return;
    
    const debouncedSearch = debounce(function(query) {
        const rows = table.querySelectorAll('tbody tr');
        const lowerQuery = query.toLowerCase().trim();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const match = !lowerQuery || text.includes(lowerQuery);
            
            row.style.display = match ? '' : 'none';
            row.style.opacity = match ? '1' : '0';
            
            if (match && lowerQuery) {
                row.style.background = 'linear-gradient(90deg, rgba(235, 175, 62, 0.1), transparent)';
            } else {
                row.style.background = '';
            }
        });
        
        // Show empty state if no results
        const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');
        let emptyMessage = table.querySelector('.search-empty-message');
        
        if (visibleRows.length === 0 && lowerQuery) {
            if (!emptyMessage) {
                emptyMessage = document.createElement('tr');
                emptyMessage.className = 'search-empty-message';
                emptyMessage.innerHTML = `<td colspan="100%" style="text-align: center; padding: 2rem; color: #64748B;">No results found for "${query}"</td>`;
                table.querySelector('tbody').appendChild(emptyMessage);
            }
        } else if (emptyMessage) {
            emptyMessage.remove();
        }
    }, 300);
    
    searchInput.addEventListener('input', function() {
        debouncedSearch(this.value);
    });
}

/**
 * Loading State for Buttons
 */
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

// Expose utilities globally
window.showToast = showToast;
window.setButtonLoading = setButtonLoading;
window.initTableSearch = initTableSearch;
