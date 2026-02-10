// Settings Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Form validation with confirmation modal
    const form = document.querySelector('.settings-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submission

            const baseFareInput = document.getElementById('base_fare');
            const baseFare = parseFloat(baseFareInput.value);
            const farePerKmInput = document.getElementById('fare_per_km');
            const farePerKm = parseFloat(farePerKmInput.value);

            // Validate base fare
            if (isNaN(baseFare) || baseFare < 0) {
                showToast('Please enter a valid base fare amount', 'error');
                baseFareInput.focus();
                return false;
            }

            if (baseFare > 999999) {
                showToast('Base fare cannot exceed ₱999,999.00', 'error');
                baseFareInput.focus();
                return false;
            }

            // Validate fare per km
            if (isNaN(farePerKm) || farePerKm < 0) {
                showToast('Please enter a valid fare per kilometer amount', 'error');
                farePerKmInput.focus();
                return false;
            }

            if (farePerKm > 999999) {
                showToast('Fare per kilometer cannot exceed ₱999,999.00', 'error');
                farePerKmInput.focus();
                return false;
            }

            // Show confirmation modal
            showConfirmationModal(baseFare, farePerKm);
        });
    }

    // Number input formatting
    const baseFareInput = document.getElementById('base_fare');
    const farePerKmInput = document.getElementById('fare_per_km');
    
    [baseFareInput, farePerKmInput].forEach(input => {
        if (input) {
            input.addEventListener('input', function() {
                // Remove invalid error state when user starts typing
                this.classList.remove('is-invalid');
                const errorMsg = this.parentElement.parentElement.querySelector('.form-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            });

            // Format on blur
            input.addEventListener('blur', function() {
                const value = parseFloat(this.value);
                if (!isNaN(value)) {
                    this.value = value.toFixed(2);
                }
            });
        }
    });
});

function showConfirmationModal(baseFare, farePerKm) {
    const modal = document.getElementById('confirmSettingsModal');
    const baseFareDisplay = document.getElementById('confirmBaseFare');
    const farePerKmDisplay = document.getElementById('confirmFarePerKm');
    
    baseFareDisplay.textContent = '₱' + parseFloat(baseFare).toFixed(2);
    farePerKmDisplay.textContent = '₱' + parseFloat(farePerKm).toFixed(2);
    
    modal.style.display = 'flex';
    
    // Add fade-in animation
    requestAnimationFrame(() => {
        modal.classList.add('active');
    });
}

function closeConfirmationModal() {
    const modal = document.getElementById('confirmSettingsModal');
    modal.classList.remove('active');
    
    setTimeout(() => {
        modal.style.display = 'none';
    }, 200);
}

function confirmSaveSettings() {
    const form = document.querySelector('.settings-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Close modal
    closeConfirmationModal();
    
    // Show loading state
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }
    
    // Submit the form
    form.submit();
}

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
    
    const icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-exclamation',
        warning: 'fa-triangle-exclamation',
        info: 'fa-circle-info'
    };
    
    toast.innerHTML = `
        <i class="fa-solid ${icons[type] || icons.info}"></i>
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
}

// Make functions globally accessible
window.showConfirmationModal = showConfirmationModal;
window.closeConfirmationModal = closeConfirmationModal;
window.confirmSaveSettings = confirmSaveSettings;
