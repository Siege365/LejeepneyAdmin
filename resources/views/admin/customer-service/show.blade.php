@extends('layouts.admin')

@section('title', 'Ticket #' . $ticket->id)
@section('page-title', 'Ticket Details')

@section('content')
<!-- Success Message -->
@if(session('success'))
<div style="background: #D1FAE5; border-left: 4px solid #10B981; color: #065F46; padding: 1rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
    <i class="fa-solid fa-circle-check" style="font-size: 1.25rem; color: #10B981;"></i>
    <span style="font-weight: 500;">{{ session('success') }}</span>
</div>
@endif

<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">
                Ticket #{{ $ticket->id }}
                @php
                    $statusStyles = [
                        'resolved' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-circle-check'],
                        'in-progress' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-spinner'],
                        'pending' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-clock']
                    ];
                    $sStyle = $statusStyles[$ticket->status];
                @endphp
                <span style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }}; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; margin-left: 0.5rem;">
                    <i class="fa-solid {{ $sStyle['icon'] }}"></i>
                    {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                </span>
            </h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">{{ $ticket->subject }}</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button class="btn btn-primary" onclick="document.getElementById('replyModal').style.display='flex'">
                <i class="fa-solid fa-reply"></i>
                Reply to Customer
            </button>
            <a href="{{ route('customer-service.index') }}" class="btn btn-secondary">
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
                        @php
                            $typeStyles = [
                                'complaint' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-exclamation-circle'],
                                'feedback' => ['bg' => '#E0E7FF', 'color' => '#6366F1', 'icon' => 'fa-comment'],
                                'bug' => ['bg' => '#FECACA', 'color' => '#B91C1C', 'icon' => 'fa-bug'],
                                'inquiry' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-question'],
                                'suggestion' => ['bg' => '#D1FAE5', 'color' => '#059669', 'icon' => 'fa-lightbulb'],
                                'report' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-flag']
                            ];
                            $tStyle = $typeStyles[$ticket->type] ?? $typeStyles['inquiry'];
                        @endphp
                        <span style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }}; padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Priority</label>
                        @php
                            $priorityStyles = [
                                'high' => ['bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-circle-exclamation'],
                                'medium' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-circle-pause'],
                                'low' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'icon' => 'fa-circle-info']
                            ];
                            $pStyle = $priorityStyles[$ticket->priority];
                        @endphp
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
                            {{ substr($ticket->customer_name, 0, 1) }}
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <span style="font-weight: 600; color: #1E293B;">{{ $ticket->customer_name }}</span>
                                    <span style="background: #E0E7FF; color: #6366F1; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">Customer</span>
                                </div>
                                <span style="font-size: 0.875rem; color: #94A3B8;">{{ $ticket->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="background: #F8FAFC; border-left: 3px solid #667eea; padding: 1rem; border-radius: 0.5rem;">
                                <p style="margin: 0; color: #475569; line-height: 1.6;">
                                    {{ $ticket->message ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum. Donec in efficitur leo. In hac habitasse platea dictumst.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Replies -->
                @forelse($replies ?? [] as $reply)
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; gap: 1rem; margin-bottom: 0.75rem;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #EBAF3E 0%, #D4941E 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 1rem; flex-shrink: 0;">
                            A
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div>
                                    <span style="font-weight: 600; color: #1E293B;">Admin Support</span>
                                    <span style="background: #FEF3C7; color: #D97706; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; margin-left: 0.5rem;">Admin</span>
                                </div>
                                <span style="font-size: 0.875rem; color: #94A3B8;">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="background: #FFFBEB; border-left: 3px solid #EBAF3E; padding: 1rem; border-radius: 0.5rem;">
                                <p style="margin: 0; color: #475569; line-height: 1.6;">
                                    {{ $reply->message }}
                                </p>
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
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #E2E8F0;">
                    <button class="btn btn-primary" style="width: 100%;" onclick="document.getElementById('replyModal').style.display='flex'">
                        <i class="fa-solid fa-reply"></i>
                        Add Reply
                    </button>
                </div>
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
                        {{ substr($ticket->customer_name, 0, 1) }}
                    </div>
                    <h4 style="margin: 0 0 0.25rem 0; color: #1E293B;">{{ $ticket->customer_name }}</h4>
                    <p style="margin: 0; color: #64748B; font-size: 0.875rem;">{{ $ticket->customer_email }}</p>
                </div>

                <div style="border-top: 1px solid #E2E8F0; padding-top: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Contact</label>
                        <a href="mailto:{{ $ticket->customer_email }}" style="color: var(--secondary-blue); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                            <i class="fa-solid fa-envelope"></i>
                            {{ $ticket->customer_email }}
                        </a>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 0.5rem;">Total Tickets</label>
                        <p style="margin: 0; color: #1E293B; font-weight: 600;">3 tickets submitted</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3>Actions</h3>
            </div>
            <div style="padding: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @if($ticket->status !== 'resolved')
                    <button class="btn btn-success" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-check-circle"></i>
                        Mark as Resolved
                    </button>
                    @endif
                    
                    @if($ticket->status === 'pending')
                    <button class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-spinner"></i>
                        Mark In Progress
                    </button>
                    @endif

                    <button class="btn btn-outline" style="width: 100%; justify-content: center;">
                        <i class="fa-solid fa-flag"></i>
                        Flag as Important
                    </button>

                    <button class="btn btn-outline" style="width: 100%; justify-content: center; color: #EF4444; border-color: #EF4444;">
                        <i class="fa-solid fa-archive"></i>
                        Archive Ticket
                    </button>
                </div>
            </div>
        </div>
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

        <form action="{{ route('customer-service.reply', $ticket->id) }}" method="POST">
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
                    <textarea name="message" rows="6" style="width: 100%; padding: 0.75rem; font-size: 0.9375rem; border: 1px solid #CBD5E1; border-radius: 8px; font-family: inherit; resize: vertical; outline: none; transition: all 0.2s;" placeholder="Type your response to the customer..." required>{{ old('message') }}</textarea>
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
                        <input type="checkbox" name="send_email" checked style="width: 16px; height: 16px; cursor: pointer;">
                        <span style="font-size: 0.875rem; color: #475569;">Send email notification to customer</span>
                    </label>
                </div>
            </div>

            <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; gap: 0.75rem; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('replyModal').style.display='none'">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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
@endsection
