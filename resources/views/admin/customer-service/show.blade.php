@extends('layouts.admin')

@section('title', 'Ticket #' . $ticket->id)
@section('page-title', 'Ticket Details')

@push('styles')
@vite('resources/css/pages/customer-service.css')
@endpush

@section('content')
<!-- Archived Warning Banner -->
@if($ticket->is_archived)
<div class="archived-banner">
    <div class="archived-banner-content">
        <i class="fa-solid fa-archive"></i>
        <span>This ticket has been archived on {{ $ticket->archived_at?->format('M d, Y') }}</span>
    </div>
    <form action="{{ route('admin.customer-service.restore', $ticket->id) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm archived-restore-btn">
            <i class="fa-solid fa-rotate-left"></i> Restore Ticket
        </button>
    </form>
</div>
@endif

<!-- Page Header -->
<div class="card cs-page-header">
    <div class="card-header">
        <div>
            <h2 class="ticket-header-title">
                @if($ticket->is_flagged)
                <i class="fa-solid fa-flag flag-icon" title="Flagged as Important"></i>
                @endif
                Ticket #{{ $ticket->id }}
                @php $sStyle = $ticket->statusStyles; @endphp
                <span class="ticket-status-badge" style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }};">
                    <i class="fa-solid {{ $sStyle['icon'] }}"></i>
                    {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                </span>
            </h2>
            <p class="ticket-header-subject">{{ $ticket->subject }}</p>
        </div>
        <div class="header-actions">
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

<div class="cs-layout-grid">
    <!-- Left Column - Ticket Details & Conversation -->
    <div>
        <!-- Ticket Information -->
        <div class="card mb-6">
            <div class="card-header">
                <h3>Ticket Information</h3>
            </div>
            <div class="ticket-info-grid">
                <!-- Subject -->
                <div class="ticket-info-item">
                    <label>Subject</label>
                    <p>{{ $ticket->subject }}</p>
                </div>

                <!-- Type -->
                <div class="ticket-info-item">
                    <label>Type</label>
                    @php $tStyle = $ticket->typeStyles; @endphp
                    <span class="ticket-badge-lg" style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }};">
                        <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                        {{ ucfirst($ticket->type) }}
                    </span>
                </div>

                <!-- Priority -->
                <div class="ticket-info-item">
                    <label>Priority</label>
                    @php $pStyle = $ticket->priorityStyles; @endphp
                    <span class="ticket-badge-lg" style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['color'] }};">
                        <i class="fa-solid {{ $pStyle['icon'] }}"></i>
                        {{ ucfirst($ticket->priority) }}
                    </span>
                </div>

                <!-- Created Date -->
                <div class="ticket-info-item">
                    <label>Created</label>
                    <p class="info-date">
                        {{ $ticket->created_at->format('F d, Y') }}
                        <span class="info-time">at {{ $ticket->created_at->format('h:i A') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Conversation Thread -->
        <div class="card">
            <div class="card-header">
                <h3>Conversation</h3>
                <span class="conversation-count">{{ count($replies ?? []) + 1 }} {{ count($replies ?? []) == 0 ? 'Message' : 'Messages' }}</span>
            </div>
            <div class="conversation-thread">
                <!-- Initial Customer Message -->
                <div class="message-block">
                    <div class="message-container">
                        <div class="message-avatar customer">
                            {{ substr($ticket->name, 0, 1) }}
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <div>
                                    <span class="message-author">{{ $ticket->name }}</span>
                                    <span class="message-badge customer">Customer</span>
                                </div>
                                <span class="message-time">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-bubble customer">
                                <p>{{ $ticket->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                @forelse($replies ?? [] as $reply)
                <div class="message-block">
                    <div class="message-container">
                        <div class="message-avatar {{ $reply->sender_type === 'customer' ? 'customer' : 'admin' }}">
                            {{ substr($reply->sender_display_name, 0, 1) }}
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <div>
                                    <span class="message-author">{{ $reply->sender_display_name }}</span>
                                    @if($reply->sender_type === 'customer')
                                        <span class="message-badge customer">Customer</span>
                                    @else
                                        <span class="message-badge admin">Admin</span>
                                        @if($reply->email_sent)
                                        <span class="message-badge emailed" title="Email sent to customer">
                                            <i class="fa-solid fa-envelope-circle-check"></i> Emailed
                                        </span>
                                        @endif
                                    @endif
                                </div>
                                <span class="message-time">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="message-bubble {{ $reply->sender_type === 'customer' ? 'customer' : 'admin' }}">
                                <p>{{ $reply->message }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <!-- No replies yet -->
                <div class="conversation-empty">
                    <i class="fa-solid fa-comments"></i>
                    <p>No replies yet. Be the first to respond!</p>
                </div>
                @endforelse

                <!-- Quick Reply Section -->
                @if(!$ticket->is_archived)
                <div class="conversation-reply-section">
                    <button class="btn btn-primary btn-full" onclick="document.getElementById('replyModal').style.display='flex'">
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
        <div class="card mb-6">
            <div class="card-header">
                <h3>Customer Details</h3>
            </div>
            <div class="customer-details">
                <div class="customer-profile-section">
                    <div class="customer-avatar-lg">
                        {{ substr($ticket->name, 0, 1) }}
                    </div>
                    <h4 class="customer-name">{{ $ticket->name }}</h4>
                    <p class="customer-email">{{ $ticket->email }}</p>
                </div>

                <div class="customer-meta">
                    <div class="customer-meta-item">
                        <label>Contact</label>
                        <a href="mailto:{{ $ticket->email }}">
                            <i class="fa-solid fa-envelope"></i>
                            {{ $ticket->email }}
                        </a>
                    </div>
                    @if($ticket->admin)
                    <div class="customer-meta-item">
                        <label>Assigned To</label>
                        <p>{{ $ticket->admin->name }}</p>
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
            <div class="actions-card">
                <div class="actions-stack">
                    @if($ticket->status !== 'resolved')
                    <button type="button" class="btn btn-success" onclick="showResolveModal()">
                        <i class="fa-solid fa-check-circle"></i>
                        Mark as Resolved
                    </button>
                    @endif
                    
                    @if($ticket->status === 'pending')
                    <button type="button" class="btn btn-primary" onclick="showInProgressModal()">
                        <i class="fa-solid fa-spinner"></i>
                        Mark In Progress
                    </button>
                    @endif

                    <button type="button" class="btn btn-outline {{ $ticket->is_flagged ? 'btn-flagged' : '' }}" onclick="showFlagModal()">
                        <i class="fa-solid fa-flag"></i>
                        {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag as Important' }}
                    </button>

                    <button type="button" class="btn btn-outline btn-danger-outline" onclick="showArchiveModal()">
                        <i class="fa-solid fa-archive"></i>
                        Archive Ticket
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reply Modal -->
<div id="replyModal" class="modal-backdrop">
    <div class="modal-container" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fa-solid fa-reply" style="color: var(--secondary-blue);"></i>
                Reply to Ticket #{{ $ticket->id }}
            </h3>
            <button class="modal-close" onclick="document.getElementById('replyModal').style.display='none'">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <form id="replyForm" action="{{ route('admin.customer-service.reply', $ticket->id) }}" method="POST">
            @csrf
            <div class="modal-body">
                @if($errors->any())
                <div class="alert-error mb-4">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <p class="error-text">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <div class="form-group">
                    <label>Your Reply</label>
                    <textarea id="replyMessage" name="message" rows="6" class="reply-textarea" placeholder="Type your response to the customer..." required>{{ old('message') }}</textarea>
                </div>

                <div class="form-group">
                    <label>Update Status</label>
                    <select name="status" class="reply-select">
                        <option value="pending" {{ $ticket->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in-progress" {{ $ticket->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="email-checkbox-section">
                    <label class="email-checkbox-label">
                        <input type="checkbox" id="sendEmailCheckbox" name="send_email" value="1" checked>
                        <span>Send email notification to customer</span>
                    </label>
                    <p class="email-checkbox-note">
                        Email will be sent to: {{ $ticket->email }}
                    </p>
                </div>
            </div>

            <div class="modal-footer">
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

<!-- Mark as Resolved Modal -->
<div class="modal-backdrop" id="resolveModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-check-circle" style="color: #10B981;"></i> Mark as Resolved</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to mark this ticket as resolved?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeResolveModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="showResolveConfirmModal()">Continue</button>
        </div>
    </div>
</div>

<!-- Resolve Confirmation Modal -->
<div class="modal-backdrop" id="resolveConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p>This will mark ticket #{{ $ticket->id }} as resolved. Continue?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeResolveConfirmModal()">Cancel</button>
            <form id="resolveForm" action="{{ route('admin.customer-service.updateStatus', $ticket->id) }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="status" value="resolved">
                <button type="submit" class="btn btn-success">Confirm Resolve</button>
            </form>
        </div>
    </div>
</div>

<!-- Mark In Progress Modal -->
<div class="modal-backdrop" id="inProgressModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-spinner" style="color: var(--secondary-blue);"></i> Mark In Progress</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to mark this ticket as in progress?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeInProgressModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="showInProgressConfirmModal()">Continue</button>
        </div>
    </div>
</div>

<!-- In Progress Confirmation Modal -->
<div class="modal-backdrop" id="inProgressConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p>This will mark ticket #{{ $ticket->id }} as in progress. Continue?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeInProgressConfirmModal()">Cancel</button>
            <form id="inProgressForm" action="{{ route('admin.customer-service.updateStatus', $ticket->id) }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="status" value="in-progress">
                <button type="submit" class="btn btn-primary">Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- Flag Modal -->
<div class="modal-backdrop" id="flagModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-flag" style="color: #EF4444;"></i> {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag as Important' }}</h3>
        </div>
        <div class="modal-body">
            <p>{{ $ticket->is_flagged ? 'Are you sure you want to remove the flag from this ticket?' : 'Are you sure you want to flag this ticket as important?' }}</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeFlagModal()">Cancel</button>
            <button type="button" class="btn btn-outline" onclick="showFlagConfirmModal()">Continue</button>
        </div>
    </div>
</div>

<!-- Flag Confirmation Modal -->
<div class="modal-backdrop" id="flagConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p>{{ $ticket->is_flagged ? 'This will remove the flag from ticket #' . $ticket->id : 'This will flag ticket #' . $ticket->id . ' as important' }}. Continue?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeFlagConfirmModal()">Cancel</button>
            <form id="flagForm" action="{{ route('admin.customer-service.toggleFlag', $ticket->id) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline">Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal-backdrop" id="archiveModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-archive" style="color: #EF4444;"></i> Archive Ticket</h3>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to archive this ticket?</p>
            <p class="text-muted" style="font-size: 0.875rem; margin-top: 0.5rem;">Archived tickets can be restored later.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeArchiveModal()">Cancel</button>
            <button type="button" class="btn btn-outline btn-danger-outline" onclick="showArchiveConfirmModal()">Continue</button>
        </div>
    </div>
</div>

<!-- Archive Confirmation Modal -->
<div class="modal-backdrop" id="archiveConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #EF4444;"></i> Confirm Archive</h3>
        </div>
        <div class="modal-body">
            <p>This will archive ticket #{{ $ticket->id }}. This action can be reversed. Continue?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeArchiveConfirmModal()">Cancel</button>
            <form id="archiveForm" action="{{ route('admin.customer-service.archive', $ticket->id) }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Confirm Archive</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/customer-service-show.js')
<script>
    // Wait for CustomerServiceShow to be available, then initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Poll for CustomerServiceShow availability (in case Vite loads slowly)
        const initWhenReady = setInterval(() => {
            if (window.CustomerServiceShow) {
                clearInterval(initWhenReady);
                window.CustomerServiceShow.init({{ $ticket->id }}, {
                    email: @json($ticket->email),
                    name: @json($ticket->name),
                    subject: @json($ticket->subject)
                });
            }
        }, 50);
        
        // Timeout after 5 seconds
        setTimeout(() => clearInterval(initWhenReady), 5000);
    });
</script>
@if($errors->any())
<div class="reply-modal-auto-open" style="display:none;"></div>
@endif
@endpush
