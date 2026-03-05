/* ========================================
   NOTIFICATIONS PAGE JS
   Notification listing page functionality
   ======================================== */

document.addEventListener('DOMContentLoaded', function() {
    // Mark single notification as read
    document.querySelectorAll('.mark-as-read-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const notificationItem = this.closest('.notification-item');
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            }
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    // Remove unread styling
                    notificationItem.classList.remove('unread');
                    notificationItem.querySelector('.notification-unread-badge')?.remove();
                    this.remove();
                    
                    // Update bell count
                    updateBellCount();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Mark as Read';
                }
            }
        });
    });
    
    // Mark all as read
    document.querySelector('.mark-all-read-form')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const markAllBtn = this.querySelector('button[type="submit"]');
        if (markAllBtn) {
            markAllBtn.disabled = true;
            markAllBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        }
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });
            
            if (response.ok) {
                // Remove all unread styling
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                    item.querySelector('.notification-unread-badge')?.remove();
                });
                
                // Remove all individual mark as read buttons
                document.querySelectorAll('.mark-as-read-form').forEach(form => form.remove());
                
                // Hide the mark all button
                this.style.display = 'none';
                
                // Update bell count
                updateBellCount();
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            if (markAllBtn) {
                markAllBtn.disabled = false;
                markAllBtn.innerHTML = 'Mark All as Read';
            }
        }
    });
    
    function updateBellCount() {
        const bellBadge = document.querySelector('.notification-badge');
        if (bellBadge) {
            const currentCount = parseInt(bellBadge.textContent) || 0;
            const newCount = Math.max(0, currentCount - 1);
            
            if (newCount > 0) {
                bellBadge.textContent = newCount;
            } else {
                bellBadge.remove();
            }
        }
    }
});
