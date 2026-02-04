/* ========================================
   CUSTOMER SERVICE SHOW PAGE JS
   Ticket detail page functionality
   ======================================== */

const CustomerServiceShow = {
    ticketId: null,
    emailjsInitialized: false,
    
    init(ticketId) {
        this.ticketId = ticketId;
        this.initEmailJS();
        this.initReplyForm();
        this.scrollToLatestMessage();
    },
    
    // Initialize EmailJS
    initEmailJS() {
        if (typeof emailjs !== 'undefined' && !this.emailjsInitialized) {
            emailjs.init(window.EMAILJS_PUBLIC_KEY || 'YOUR_PUBLIC_KEY');
            this.emailjsInitialized = true;
        }
    },
    
    // Initialize reply form
    initReplyForm() {
        const form = document.getElementById('reply-form');
        if (!form) return;
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.submitReply(form);
        });
    },
    
    // Submit reply
    async submitReply(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn?.innerHTML;
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        }
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success('Reply sent successfully');
                
                // Send email notification if EmailJS is configured
                if (window.EMAILJS_ENABLED) {
                    await this.sendEmailNotification(formData.get('message'));
                }
                
                // Reload to show new reply
                setTimeout(() => window.location.reload(), 1000);
            } else {
                Toast?.error(data.message || 'Failed to send reply');
            }
        } catch (error) {
            console.error('Reply error:', error);
            Toast?.error('Failed to send reply. Please try again.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    },
    
    // Send email notification
    async sendEmailNotification(message) {
        if (typeof emailjs === 'undefined') return;
        
        try {
            await emailjs.send(
                window.EMAILJS_SERVICE_ID,
                window.EMAILJS_TEMPLATE_ID,
                {
                    to_email: window.TICKET_EMAIL,
                    to_name: window.TICKET_NAME,
                    ticket_id: this.ticketId,
                    message: message,
                    subject: window.TICKET_SUBJECT
                }
            );
            console.log('Email notification sent');
        } catch (error) {
            console.error('EmailJS error:', error);
        }
    },
    
    // Scroll to latest message
    scrollToLatestMessage() {
        const conversation = document.querySelector('.conversation-thread');
        if (conversation) {
            conversation.scrollTop = conversation.scrollHeight;
        }
    },
    
    // Update ticket status
    async updateStatus(status) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(`/admin/customer-service/${this.ticketId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success(`Status updated to ${status}`);
                window.location.reload();
            } else {
                Toast?.error(data.message || 'Failed to update status');
            }
        } catch (error) {
            console.error('Status update error:', error);
            Toast?.error('Failed to update status');
        }
    },
    
    // Toggle flag
    async toggleFlag() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(`/admin/customer-service/${this.ticketId}/toggle-flag`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success(data.flagged ? 'Ticket flagged' : 'Flag removed');
                window.location.reload();
            } else {
                Toast?.error(data.message || 'Failed to toggle flag');
            }
        } catch (error) {
            console.error('Flag toggle error:', error);
            Toast?.error('Failed to toggle flag');
        }
    },
    
    // Archive ticket
    async archive() {
        if (typeof Modal !== 'undefined') {
            Modal.confirm({
                title: 'Archive Ticket',
                message: 'Are you sure you want to archive this ticket?',
                confirmText: 'Yes, archive',
                confirmClass: 'btn-warning',
                onConfirm: () => this.doArchive()
            });
        } else if (confirm('Are you sure you want to archive this ticket?')) {
            this.doArchive();
        }
    },
    
    async doArchive() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(`/admin/customer-service/${this.ticketId}/archive`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success('Ticket archived');
                window.location.href = '/admin/customer-service';
            } else {
                Toast?.error(data.message || 'Failed to archive ticket');
            }
        } catch (error) {
            console.error('Archive error:', error);
            Toast?.error('Failed to archive ticket');
        }
    },
    
    // Delete ticket
    async deleteTicket() {
        if (typeof Modal !== 'undefined') {
            Modal.confirm({
                title: 'Delete Ticket',
                message: 'Are you sure you want to delete this ticket? This action cannot be undone.',
                confirmText: 'Yes, delete',
                confirmClass: 'btn-danger',
                onConfirm: () => this.doDelete()
            });
        } else if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
            this.doDelete();
        }
    },
    
    async doDelete() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        try {
            const response = await fetch(`/admin/customer-service/${this.ticketId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast?.success('Ticket deleted');
                window.location.href = '/admin/customer-service';
            } else {
                Toast?.error(data.message || 'Failed to delete ticket');
            }
        } catch (error) {
            console.error('Delete error:', error);
            Toast?.error('Failed to delete ticket');
        }
    }
};

// Export for use
window.CustomerServiceShow = CustomerServiceShow;
