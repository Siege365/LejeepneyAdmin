/* ========================================
   ACCOUNT SETTINGS - Page Scripts
   Password toggle, password change modals,
   delete account modals
   ======================================== */

/**
 * Toggle password field visibility
 */
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = input.nextElementSibling?.querySelector('i') || input.parentElement.querySelector('.password-toggle i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/* ========================================
   PASSWORD CHANGE - Double Confirmation
   ======================================== */

function showPasswordChangeModal() {
    // Validate form fields before opening modal
    var form = document.getElementById('passwordChangeForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    document.getElementById('passwordChangeModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePasswordChangeModal() {
    document.getElementById('passwordChangeModal').style.display = 'none';
    document.body.style.overflow = '';
}

function showPasswordChangeConfirm() {
    closePasswordChangeModal();
    document.getElementById('passwordChangeConfirmModal').style.display = 'flex';
}

function closePasswordChangeConfirm() {
    document.getElementById('passwordChangeConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

function confirmPasswordChange() {
    var btn = document.querySelector('#passwordChangeConfirmModal .btn-primary');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';
    }
    document.getElementById('passwordChangeForm').submit();
}

/* ========================================
   DELETE ACCOUNT - Double Confirmation
   ======================================== */

function showDeleteAccountModal() {
    document.getElementById('deleteAccountModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeDeleteAccountModal() {
    document.getElementById('deleteAccountModal').style.display = 'none';
    document.body.style.overflow = '';
}

function showDeleteAccountConfirm() {
    closeDeleteAccountModal();
    document.getElementById('deleteAccountConfirmModal').style.display = 'flex';
}

function closeDeleteAccountConfirm() {
    document.getElementById('deleteAccountConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('deleteAccountForm').reset();
}

function confirmDeleteAccount() {
    var form = document.getElementById('deleteAccountForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    var btn = document.querySelector('#deleteAccountConfirmModal .btn-danger');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
    }
    form.submit();
}

// Expose to global scope for inline onclick handlers
window.togglePassword = togglePassword;
window.showPasswordChangeModal = showPasswordChangeModal;
window.closePasswordChangeModal = closePasswordChangeModal;
window.showPasswordChangeConfirm = showPasswordChangeConfirm;
window.closePasswordChangeConfirm = closePasswordChangeConfirm;
window.confirmPasswordChange = confirmPasswordChange;
window.showDeleteAccountModal = showDeleteAccountModal;
window.closeDeleteAccountModal = closeDeleteAccountModal;
window.showDeleteAccountConfirm = showDeleteAccountConfirm;
window.closeDeleteAccountConfirm = closeDeleteAccountConfirm;
window.confirmDeleteAccount = confirmDeleteAccount;
