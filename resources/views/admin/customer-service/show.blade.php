@extends('layouts.admin')

@section('title', 'Ticket #' . $ticket->id)
@section('page-title', 'Ticket Details')

@section('content')
<!-- Toast Container -->
<div id="toastContainer" style="position: fixed; top: 1rem; right: 1rem; z-index: 99999; display: flex; flex-direction: column; gap: 0.75rem; max-width: 400px;"></div>

<!-- Archived Warning Banner -->
@if($ticket->is_archived)
<div style="background: #FEF3C7; border-left: 4px solid #D97706; color: #92400E; padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <i class="fa-solid fa-archive" style="font-size: 1.25rem; color: #D97706;"></i>
        <span style="font-weight: 500;">This ticket has been archived on {{ $ticket->archived_at?->format('M d, Y') }}</span>
    </div>
    <form action="{{ route('admin.customer-service.restore', $ticket->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm" style="background: #D97706; color: white; border: none;">
            <i class="fa-solid fa-rotate-left"></i> Restore Ticket
        </button>
    </form>
</div>
@endif

<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                @if($ticket->is_flagged)
                <i class="fa-solid fa-flag" style="color: #DC2626;" title="Flagged as Important"></i>
                @endif
                Ticket #{{ $ticket->id }}
                @php $sStyle = $ticket->statusStyles; @endphp
                <span style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }}; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; margin-left: 0.5rem;">
                    <i class="fa-solid {{ $sStyle['icon'] }}"></i>
                    {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                </span>
            </h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">{{ $ticket->subject }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            @if(!$ticket->is_archived)
            <button class="btn btn-primary" onclick="document.getElementById('replyModal').style.display='flex'">
                <i class="fa-solid fa-reply"></i>
                Reply to Customer
            </button>
            @endif
            <a href="{{ route('admin.customer-service.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Tickets
            </a>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Left Column - Ticket Details & Conversation -->
    <div>
        <!-- Ticket Information -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3>Ticket Information</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
                    <!-- Subject -->
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Subject</label>
                        <p style="margin: 0; font-weight: 500; color: #1E293B;">{{ $ticket->subject }}</p>
                    </div>

                    <!-- Type -->
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Type</label>
                        @php $tStyle = $ticket->typeStyles; @endphp
                        <span style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }}; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Priority</label>
                        @php $pStyle = $ticket->priorityStyles; @endphp
                        <span style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['color'] }}; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid {{ $pStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </div>

                    <!-- Created Date -->
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Created</label>
                        <p style="margin: 0; color: #1E293B;">
                            {{ $ticket->created_at->format('F d, Y') }}
                            <span style="color: #64748B; font-size: 0.875rem;">at {{ $ticket->created_at->format('h:i A') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversation Thread -->
        <div class="card">
            <div class="card-header">
                <h3>Conversation</h3>
                <span style="color: #64748B; font-size: 0.875rem;">{{ count($replies ?? []) + 1 }} {{ count($replies ?? []) == 0 ? 'Message' : 'Messages' }}</span>
            </div>
            <div style="padding: 1.5rem;">
                <!-- Initial Customer Message -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem; flex-shrink: 0;">
                            {{ substr($ticket->name, 0, 1) }}
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <span style="font-weight: 600; color: #1E293B;">{{ $ticket->name }}</span>
                                    <span style="background: #E0E7FF; color: #6366F1; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">Customer</span>
                                </div>
                                <span style="font-size: 0.875rem; color: #94A3B8;">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="background: #F8FAFC; border-left: 3px solid #667eea; padding: 1rem; border-radius: 0.5rem;">
                                <p style="margin: 0; color: #475569; line-height: 1.6; white-space: pre-wrap;">{{ $ticket->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Replies -->
                @forelse($replies ?? [] as $reply)
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #EBAF3E 0%, #D4941E 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem; flex-shrink: 0;">
                            {{ substr($reply->admin_name ?? 'A', 0, 1) }}
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <span style="font-weight: 600; color: #1E293B;">{{ $reply->admin_name ?? 'Admin Support' }}</span>
                                    <span style="background: #FEF3C7; color: #D97706; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">Admin</span>
                                    @if($reply->email_sent)
                                    <span style="background: #D1FAE5; color: #059669; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.25rem;" title="Email sent to customer">
                                        <i class="fa-solid fa-envelope-circle-check"></i> Emailed
                                    </span>
                                    @endif
                                </div>
                                <span style="font-size: 0.875rem; color: #94A3B8;">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="background: #FFFBEB; border-left: 3px solid #EBAF3E; padding: 1rem; border-radius: 0.5rem;">
                                <p style="margin: 0; color: #475569; line-height: 1.6; white-space: pre-wrap;">{{ $reply->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <!-- No replies yet -->
                <div style="text-align: center; padding: 2rem; background: #F8FAFC; border-radius: 0.5rem; border: 2px dashed #CBD5E1;">
                    <i class="fa-solid fa-comments" style="font-size: 2rem; color: #CBD5E1; margin-bottom: 0.5rem;"></i>
                    <p style="color: #64748B; margin: 0; font-size: 0.875rem;">No replies yet. Be the first to respond!</p>
                </div>
                @endforelse

                <!-- Quick Reply Section -->
                @if(!$ticket->is_archived)
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #E2E8F0;">
                    <button class="btn btn-primary" style="width: 100%;" onclick="document.getElementById('replyModal').style.display='flex'">
                        <i class="fa-solid fa-reply"></i>
                        Add Reply
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column - Customer Info & Actions -->
    <div>
        <!-- Customer Information -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header">
                <h3>Customer Details</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 2rem;">
                        {{ substr($ticket->name, 0, 1) }}
                    </div>
                    <h4 style="margin: 0 0 0.25rem 0; color: #1E293B;">{{ $ticket->name }}</h4>
                    <p style="margin: 0; color: #64748B; font-size: 0.875rem;">{{ $ticket->email }}</p>
                </div>

                <div style="border-top: 1px solid #E2E8F0; padding-top: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Contact</label>
                        <a href="mailto:{{ $ticket->email }}" style="color: var(--secondary-blue); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                            <i class="fa-solid fa-envelope"></i>
                            {{ $ticket->email }}
                        </a>
                    </div>
                    @if($ticket->admin)
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Assigned To</label>
                        <p style="margin: 0; color: #1E293B; font-weight: 500;">{{ $ticket->admin->name }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if(!$ticket->is_archived)
        <div class="card">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div style="padding: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @if($ticket->status !== 'resolved')
                    <form action="{{ route('admin.customer-service.updateStatus', $ticket->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-check-circle"></i>
                            Mark as Resolved
                        </button>
                    </form>
                    @endif
                    
                    @if($ticket->status === 'pending')
                    <form action="{{ route('admin.customer-service.updateStatus', $ticket->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="in-progress">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                            <i class="fa-solid fa-spinner"></i>
                            Mark In Progress
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('admin.customer-service.toggleFlag', $ticket->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; {{ $ticket->is_flagged ? 'color: #DC2626; border-color: #DC2626;' : '' }}">
                            <i class="fa-solid fa-flag"></i>
                            {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag as Important' }}
                        </button>
                    </form>

                    <form action="{{ route('admin.customer-service.archive', $ticket->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this ticket?')">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; color: #EF4444; border-color: #EF4444;">
                            <i class="fa-solid fa-archive"></i>
                            Archive Ticket
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; color: #1E293B; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-reply" style="color: var(--secondary-blue);"></i>
                Reply to Ticket #{{ $ticket->id }}
            </h3>
            <button onclick="document.getElementById('replyModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer; padding: 0; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: all 0.2s;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form id="replyForm" action="{{ route('admin.customer-service.reply', $ticket->id) }}" method="POST">
            @csrf
            <div style="padding: 1.5rem;">
                @if($errors->any())
                <div style="background: #FEE2E2; border-left: 4px solid #EF4444; color: #991B1B; padding: 0.875rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <div style="display: flex; align-items: start; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-exclamation" style="margin-top: 0.125rem;"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p style="margin: 0; font-size: 0.875rem;">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.875rem; font-weight: 600; color: #1E293B; display: block; margin-bottom: 0.5rem;">Your Reply</label>
                    <textarea id="replyMessage" name="message" rows="6" style="width: 100%; padding: 0.75rem; font-size: 0.9375rem; border: 1px solid #CBD5E1; border-radius: 8px; font-family: inherit; resize: vertical; outline: none; transition: all 0.2s;" placeholder="Type your response to the customer..." required>{{ old('message') }}</textarea>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-size: 0.875rem; font-weight: 600; color: #1E293B; display: block; margin-bottom: 0.5rem;">Update Status</label>
                    <select name="status" style="width: 100%; padding: 0.625rem 0.75rem; font-size: 0.9375rem; border: 1px solid #CBD5E1; border-radius: 8px; font-family: inherit; outline: none; cursor: pointer; background: white;">
                        <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in-progress" {{ $ticket->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>

                <div style="background: #F8FAFC; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="sendEmailCheckbox" name="send_email" checked style="width: 16px; height: 16px; cursor: pointer;">
                        <span style="font-size: 0.875rem; color: #475569;">Send email notification to customer</span>
                    </label>
                    <p style="margin: 0.5rem 0 0 1.5rem; font-size: 0.75rem; color: #94A3B8;">
                        Email will be sent to: {{ $ticket->email }}
                    </p>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('replyModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="submitReplyBtn">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

<!-- EmailJS Integration for Client-Side Email Sending -->
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
<script>
// Initialize EmailJS with your public key
// Get your keys from https://www.emailjs.com/
const EMAILJS_PUBLIC_KEY = '{{ env("EMAILJS_PUBLIC_KEY", "") }}';
const EMAILJS_SERVICE_ID = '{{ env("EMAILJS_SERVICE_ID", "") }}';
const EMAILJS_TEMPLATE_ID = '{{ env("EMAILJS_TEMPLATE_ID", "") }}';

if (EMAILJS_PUBLIC_KEY) {
    emailjs.init(EMAILJS_PUBLIC_KEY);
}

// Check if there's email data to send (from session)
@if(session('emailData'))
(function() {
    const emailData = @json(session('emailData'));
    
    if (EMAILJS_PUBLIC_KEY && EMAILJS_SERVICE_ID && EMAILJS_TEMPLATE_ID) {
        sendEmail(emailData);
    } else {
        console.log('EmailJS not configured. Email would be sent to:', emailData.to_email);
    }
})();
@endif

function sendEmail(data) {
    const templateParams = {
        to_email: data.to_email,
        to_name: data.to_name,
        from_name: data.admin_name || 'LeJeepney Support',
        subject: data.subject,
        message: data.message,
        ticket_id: data.ticket_id,
        reply_to: 'support@lejeepney.com' // Change this to your support email
    };
    
    emailjs.send(EMAILJS_SERVICE_ID, EMAILJS_TEMPLATE_ID, templateParams)
        .then(function(response) {
            console.log('Email sent successfully!', response.status, response.text);
            showNotification('Email notification sent to customer!', 'success');
        }, function(error) {
            console.error('Failed to send email:', error);
            showNotification('Failed to send email notification. The reply was saved.', 'warning');
        });
}

function showNotification(message, type) {
    const colors = {
        success: { bg: '#D1FAE5', border: '#10B981', text: '#065F46' },
        warning: { bg: '#FEF3C7', border: '#D97706', text: '#92400E' },
        error: { bg: '#FEE2E2', border: '#EF4444', text: '#991B1B' }
    };
    const color = colors[type] || colors.success;
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; top: 1rem; right: 1rem; z-index: 99999;
        background: ${color.bg}; border-left: 4px solid ${color.border}; color: ${color.text};
        padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex; align-items: center; gap: 0.75rem; animation: slideIn 0.3s ease;
    `;
    notification.innerHTML = `<i class="fa-solid fa-${type === 'success' ? 'circle-check' : 'exclamation-triangle'}"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Close modal when clicking outside
document.getElementById('replyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('replyModal').style.display = 'none';
    }
});

// Auto-open modal if there are validation errors
@if($errors->any())
document.getElementById('replyModal').style.display = 'flex';
@endif

// Hover effects for modal close button
document.querySelector('#replyModal button[onclick*="replyModal"]').addEventListener('mouseenter', function() {
    this.style.background = '#F1F5F9';
    this.style.color = '#1E293B';
});
document.querySelector('#replyModal button[onclick*="replyModal"]').addEventListener('mouseleave', function() {
    this.style.background = 'none';
    this.style.color = '#94A3B8';
});
</script>

<style>
@keyframes toastSlideIn {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
@keyframes toastSlideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(120%); opacity: 0; }
}
@keyframes toastProgress {
    from { width: 100%; }
    to { width: 0%; }
}
</style>

<script>
// Toast notification system
function showToast(message, type = 'success', duration = 5000) {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastConfig = {
        success: { bg: '#D1FAE5', border: '#10B981', text: '#065F46', icon: 'fa-circle-check' },
        error: { bg: '#FEE2E2', border: '#EF4444', text: '#991B1B', icon: 'fa-circle-exclamation' },
        warning: { bg: '#FEF3C7', border: '#F59E0B', text: '#92400E', icon: 'fa-exclamation-triangle' },
        info: { bg: '#DBEAFE', border: '#3B82F6', text: '#1E3A8A', icon: 'fa-circle-info' }
    };

    const config = toastConfig[type] || toastConfig.success;
    
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${config.bg};
        border-left: 4px solid ${config.border};
        color: ${config.text};
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        animation: toastSlideIn 0.3s ease;
        position: relative;
        overflow: hidden;
        min-width: 320px;
        max-width: 400px;
    `;

    toast.innerHTML = `
        <i class="fa-solid ${config.icon}" style="font-size: 1.25rem; color: ${config.border}; margin-top: 0.125rem;"></i>
        <div style="flex: 1; font-weight: 500; line-height: 1.5;">${message}</div>
        <button onclick="dismissToast(this)" style="background: none; border: none; color: ${config.text}; opacity: 0.6; cursor: pointer; padding: 0; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;" onmouseenter="this.style.opacity='1'; this.style.background='rgba(0,0,0,0.1)'" onmouseleave="this.style.opacity='0.6'; this.style.background='none'">
            <i class="fa-solid fa-times"></i>
        </button>
        <div style="position: absolute; bottom: 0; left: 0; height: 3px; background: ${config.border}; animation: toastProgress ${duration}ms linear;"></div>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'toastSlideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function dismissToast(button) {
    const toast = button.closest('div[style*="toastSlideIn"]');
    if (toast) {
        toast.style.animation = 'toastSlideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }
}

// Show session flash messages as toasts
@if(session('success'))
showToast('{{ session('success') }}', 'success');
@endif

@if(session('error'))
showToast('{{ session('error') }}', 'error');
@endif

@if(session('warning'))
showToast('{{ session('warning') }}', 'warning');
@endif

@if(session('info'))
showToast('{{ session('info') }}', 'info');
@endif

// Update the EmailJS notification to use toast
function showNotification(message, type) {
    showToast(message, type);
}
</script>
@endsection
