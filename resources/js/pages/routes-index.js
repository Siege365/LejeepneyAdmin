/* ========================================
   ROUTES INDEX PAGE JS
   Route listing page modal functionality
   ======================================== */

// Single route delete - double confirmation
let pendingDeleteRouteId = null;

function showDeleteRouteModal(id, name) {
    pendingDeleteRouteId = id;
    document.getElementById('deleteRouteName').textContent = name;
    document.getElementById('deleteRouteModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeDeleteRouteModal() {
    document.getElementById('deleteRouteModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showDeleteRouteConfirm() {
    closeDeleteRouteModal();
    document.getElementById('deleteRouteForm').action = '/routes/' + pendingDeleteRouteId;
    document.getElementById('deleteRouteConfirmModal').style.display = 'flex';
}
function closeDeleteRouteConfirm() {
    document.getElementById('deleteRouteConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Toggle status - double confirmation
let pendingToggleId = null;

function showToggleStatusModal(id, name, currentStatus) {
    pendingToggleId = id;
    const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
    const action = currentStatus === 'available' ? 'disable' : 'enable';
    document.getElementById('toggleStatusMessage').textContent = 'Are you sure you want to ' + action + ' "' + name + '"?';
    document.getElementById('toggleStatusConfirmMessage').textContent = 'This will ' + action + ' the route "' + name + '".';
    document.getElementById('toggleStatusModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeToggleStatusModal() {
    document.getElementById('toggleStatusModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showToggleStatusConfirm() {
    closeToggleStatusModal();
    document.getElementById('toggleStatusForm').action = '/routes/' + pendingToggleId + '/toggle-status';
    document.getElementById('toggleStatusConfirmModal').style.display = 'flex';
}
function closeToggleStatusConfirm() {
    document.getElementById('toggleStatusConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Expose to global scope for inline onclick handlers
window.showDeleteRouteModal = showDeleteRouteModal;
window.closeDeleteRouteModal = closeDeleteRouteModal;
window.showDeleteRouteConfirm = showDeleteRouteConfirm;
window.closeDeleteRouteConfirm = closeDeleteRouteConfirm;
window.showToggleStatusModal = showToggleStatusModal;
window.closeToggleStatusModal = closeToggleStatusModal;
window.showToggleStatusConfirm = showToggleStatusConfirm;
window.closeToggleStatusConfirm = closeToggleStatusConfirm;
