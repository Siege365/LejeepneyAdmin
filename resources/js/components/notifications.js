/* ========================================
   NOTIFICATION DROPDOWN
   Bell icon dropdown toggle & AJAX actions
   ======================================== */

function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const wrapper = document.querySelector('.notification-dropdown-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) dropdown.classList.remove('show');
    }
});

function handleNotificationClick(id, url) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    }).then(function() {
        window.location.href = url;
    }).catch(function() {
        window.location.href = url;
    });
}

function markAllNotificationsRead() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    }).then(function(response) {
        if (response.ok) {
            // Remove all unread dots
            document.querySelectorAll('.notification-unread-dot').forEach(function(dot) {
                dot.remove();
            });
            // Remove unread styling
            document.querySelectorAll('.notification-item.unread').forEach(function(item) {
                item.classList.remove('unread');
            });
            // Remove badge
            var badge = document.querySelector('.notification-btn .notification-badge');
            if (badge) badge.remove();
            // Hide "Mark all read" button
            var markAllBtn = document.querySelector('.notification-mark-all');
            if (markAllBtn) markAllBtn.style.display = 'none';
        }
    }).catch(function(err) {
        console.error('Failed to mark notifications as read:', err);
    });
}

// Expose to global scope for inline onclick handlers
window.toggleNotificationDropdown = toggleNotificationDropdown;
window.handleNotificationClick = handleNotificationClick;
window.markAllNotificationsRead = markAllNotificationsRead;
