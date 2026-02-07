/* ========================================
   DASHBOARD PAGE JS
   Dashboard page functionality
   ======================================== */

const Dashboard = {
    refreshInterval: null,
    
    init() {
        this.initStatsRefresh();
        this.initActivityTable();
    },
    
    // Auto-refresh stats (optional)
    initStatsRefresh() {
        // Can be enabled to auto-refresh stats every X minutes
        // this.refreshInterval = setInterval(() => this.refreshStats(), 60000);
    },
    
    // Initialize activity table interactions
    initActivityTable() {
        const table = document.querySelector('.activity-table');
        if (!table) return;
        
        // Add hover effects or click handlers if needed
        table.querySelectorAll('tbody tr').forEach(row => {
            row.style.cursor = 'pointer';
            row.addEventListener('click', () => {
                const link = row.dataset.link;
                if (link) {
                    window.location.href = link;
                }
            });
        });
    },
    
    // Refresh stats via AJAX
    async refreshStats() {
        try {
            const response = await fetch('/admin/dashboard/stats', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateStatsUI(data.stats);
            }
        } catch (error) {
            console.error('Failed to refresh stats:', error);
        }
    },
    
    // Update stats UI
    updateStatsUI(stats) {
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"] .stat-value`);
            if (element) {
                element.textContent = value;
            }
        });
    },
    
    // Cleanup
    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => Dashboard.init());

// Export for use
window.Dashboard = Dashboard;
