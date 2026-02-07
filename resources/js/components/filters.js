/* ========================================
   FILTERS COMPONENT
   Table filtering functionality
   ======================================== */

const Filters = {
    form: null,
    
    init(formSelector = '.filters-form') {
        this.form = document.querySelector(formSelector);
        
        if (!this.form) return;
        
        this.setupAutoSubmit();
    },
    
    setupAutoSubmit() {
        // Auto submit on select change
        this.form.querySelectorAll('select.filter-select').forEach(select => {
            select.addEventListener('change', () => {
                this.submit();
            });
        });
        
        // Debounced submit on search input
        const searchInput = this.form.querySelector('.filter-search');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    this.submit();
                }, 500);
            });
        }
    },
    
    submit() {
        if (this.form) {
            this.form.submit();
        }
    },
    
    reset() {
        if (this.form) {
            this.form.reset();
            this.submit();
        }
    },
    
    getValue(name) {
        const input = this.form?.querySelector(`[name="${name}"]`);
        return input?.value || '';
    },
    
    setValue(name, value) {
        const input = this.form?.querySelector(`[name="${name}"]`);
        if (input) {
            input.value = value;
        }
    },
    
    getParams() {
        if (!this.form) return {};
        
        const formData = new FormData(this.form);
        const params = {};
        
        for (const [key, value] of formData.entries()) {
            if (value) params[key] = value;
        }
        
        return params;
    },
    
    buildUrl(baseUrl = window.location.pathname) {
        const params = this.getParams();
        const queryString = new URLSearchParams(params).toString();
        return queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }
};

// Export for use
window.Filters = Filters;
