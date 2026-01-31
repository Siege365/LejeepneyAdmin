@extends('layouts.admin')

@section('title', 'Customer Service')
@section('page-title', 'Customer Service')

@section('content')
<!-- Toast Container -->
<div id="toastContainer" style="position: fixed; top: 1rem; right: 1rem; z-index: 99999; display: flex; flex-direction: column; gap: 0.75rem; max-width: 400px;"></div>

<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Customer Service</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Manage customer inquiries and support tickets.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.customer-service.index', ['archived' => 'archived']) }}" class="btn btn-outline btn-sm">
                <i class="fa-solid fa-archive"></i>
                Archived ({{ $stats['archived'] ?? 0 }})
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
    <a href="{{ route('admin.customer-service.index') }}" style="text-decoration: none;">
        <div style="background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ !request('status') && !request('flagged') ? 'border: 2px solid var(--secondary-blue);' : '' }}">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.75rem; color: #64748B; margin: 0; text-transform: uppercase; font-weight: 600;">Total</p>
                    <p style="font-size: 1.75rem; font-weight: 700; color: #1E293B; margin: 0;">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #E0E7FF; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-ticket" style="color: #6366F1;"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.customer-service.index', ['status' => 'pending']) }}" style="text-decoration: none;">
        <div style="background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ request('status') === 'pending' ? 'border: 2px solid #D97706;' : '' }}">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.75rem; color: #64748B; margin: 0; text-transform: uppercase; font-weight: 600;">Pending</p>
                    <p style="font-size: 1.75rem; font-weight: 700; color: #D97706; margin: 0;">{{ $stats['pending'] ?? 0 }}</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEF3C7; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-clock" style="color: #D97706;"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.customer-service.index', ['status' => 'in-progress']) }}" style="text-decoration: none;">
        <div style="background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ request('status') === 'in-progress' ? 'border: 2px solid #2563EB;' : '' }}">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.75rem; color: #64748B; margin: 0; text-transform: uppercase; font-weight: 600;">In Progress</p>
                    <p style="font-size: 1.75rem; font-weight: 700; color: #2563EB; margin: 0;">{{ $stats['in_progress'] ?? 0 }}</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #DBEAFE; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-spinner" style="color: #2563EB;"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.customer-service.index', ['status' => 'resolved']) }}" style="text-decoration: none;">
        <div style="background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ request('status') === 'resolved' ? 'border: 2px solid #059669;' : '' }}">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.75rem; color: #64748B; margin: 0; text-transform: uppercase; font-weight: 600;">Resolved</p>
                    <p style="font-size: 1.75rem; font-weight: 700; color: #059669; margin: 0;">{{ $stats['resolved'] ?? 0 }}</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #D1FAE5; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-circle-check" style="color: #059669;"></i>
                </div>
            </div>
        </div>
    </a>
    <a href="{{ route('admin.customer-service.index', ['flagged' => '1']) }}" style="text-decoration: none;">
        <div style="background: white; border-radius: 10px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); {{ request('flagged') ? 'border: 2px solid #DC2626;' : '' }}">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <p style="font-size: 0.75rem; color: #64748B; margin: 0; text-transform: uppercase; font-weight: 600;">Flagged</p>
                    <p style="font-size: 1.75rem; font-weight: 700; color: #DC2626; margin: 0;">{{ $stats['flagged'] ?? 0 }}</p>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEE2E2; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-flag" style="color: #DC2626;"></i>
                </div>
            </div>
        </div>
    </a>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <h3>Support Tickets</h3>
        <form method="GET" action="{{ route('admin.customer-service.index') }}" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <!-- Search -->
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets...">
            </div>
            
            <!-- Type Filter -->
            <select name="type" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.875rem; background: white; cursor: pointer;">
                <option value="all">All Types</option>
                <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                <option value="technical" {{ request('type') === 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="billing" {{ request('type') === 'billing' ? 'selected' : '' }}>Billing</option>
                <option value="feedback" {{ request('type') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            
            <!-- Priority Filter -->
            <select name="priority" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.875rem; background: white; cursor: pointer;">
                <option value="all">All Priorities</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>
            
            @if(request()->hasAny(['search', 'status', 'type', 'priority', 'flagged', 'archived']))
                <a href="{{ route('admin.customer-service.index') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" style="width: 16px; height: 16px; cursor: pointer;">
                    </th>
                    <th style="width: 80px;">#</th>
                    <th>Subject</th>
                    <th style="width: 180px;">Customer</th>
                    <th style="width: 110px;">Type</th>
                    <th style="width: 120px;">Date</th>
                    <th style="width: 100px;">Priority</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets ?? [] as $ticket)
                <tr style="{{ $ticket->is_flagged ? 'background: #FEF2F2;' : '' }}">
                    <td>
                        <input type="checkbox" class="ticketCheckbox" value="{{ $ticket->id }}" onchange="updateSelection()" style="width: 16px; height: 16px; cursor: pointer;">
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.375rem;">
                            @if($ticket->is_flagged)
                            <i class="fa-solid fa-flag" style="color: #DC2626; font-size: 0.75rem;" title="Flagged"></i>
                            @endif
                            <span style="font-weight: 600; color: var(--secondary-blue);">#{{ $ticket->id }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight: 500; color: #1E293B;">{{ Str::limit($ticket->subject, 40) }}</div>
                        <div style="font-size: 0.8125rem; color: #64748B; margin-top: 0.125rem;">{{ $ticket->email }}</div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.875rem;">
                                {{ substr($ticket->name, 0, 1) }}
                            </div>
                            <span style="font-weight: 500;">{{ Str::limit($ticket->name, 15) }}</span>
                        </div>
                    </td>
                    <td>
                        @php $tStyle = $ticket->typeStyles; @endphp
                        <span style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem; white-space: nowrap;">
                            <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </td>
                    <td style="font-size: 0.875rem; color: #64748B;">
                        {{ $ticket->created_at->format('M d, Y') }}
                        <div style="font-size: 0.75rem; color: #94A3B8;">{{ $ticket->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        @php $pStyle = $ticket->priorityStyles; @endphp
                        <span style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem;">
                            <i class="fa-solid {{ $pStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>
                        @php $sStyle = $ticket->statusStyles; @endphp
                        <span style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }}; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.375rem;">
                            <i class="fa-solid {{ $sStyle['icon'] }}"></i>
                            {{ ucfirst(str_replace('-', ' ', $ticket->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="kebab-menu">
                            <button type="button" class="kebab-trigger" onclick="toggleKebabMenu(this)">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="kebab-dropdown">
                                <a href="{{ route('admin.customer-service.show', $ticket->id) }}" class="kebab-item">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                                <a href="{{ route('admin.customer-service.show', $ticket->id) }}" class="kebab-item">
                                    <i class="fa-solid fa-reply"></i> Reply
                                </a>
                                <div class="kebab-divider"></div>
                                <form action="{{ route('admin.customer-service.toggleFlag', $ticket->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="kebab-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer;">
                                        <i class="fa-solid fa-flag" style="{{ $ticket->is_flagged ? 'color: #DC2626;' : '' }}"></i>
                                        {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag Important' }}
                                    </button>
                                </form>
                                @if(!$ticket->is_archived)
                                <form action="{{ route('admin.customer-service.archive', $ticket->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to archive this ticket?')">
                                    @csrf
                                    <button type="submit" class="kebab-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: #EF4444;">
                                        <i class="fa-solid fa-archive"></i> Archive
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.customer-service.restore', $ticket->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="kebab-item" style="width: 100%; background: none; border: none; text-align: left; cursor: pointer; color: #059669;">
                                        <i class="fa-solid fa-rotate-left"></i> Restore
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem;">
                        <i class="fa-solid fa-headset" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 1rem;"></i>
                        <p style="color: #64748B; margin: 0; font-size: 1rem; font-weight: 500;">No Support Tickets</p>
                        <p style="color: #94A3B8; margin-top: 0.5rem; font-size: 0.875rem;">
                            @if(request()->hasAny(['search', 'status', 'type', 'priority', 'flagged', 'archived']))
                                No tickets match your filters. <a href="{{ route('admin.customer-service.index') }}" style="color: var(--secondary-blue);">Clear filters</a>
                            @else
                                All caught up! There are no customer inquiries at the moment.
                            @endif
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Bulk Actions and Pagination Row -->
    <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
        <!-- Left Side - Bulk Actions -->
        <div id="bulkActionsContainer" style="display: none;">
            <form id="bulkActionForm" method="POST" action="{{ route('admin.customer-service.bulk-action') }}">
                @csrf
                <input type="hidden" name="ticket_ids" id="selectedTicketIds">
                <select name="action" id="bulkAction" class="form-control form-control-sm" style="display: inline-block; width: auto; margin-right: 0.5rem;" required>
                    <option value="">Select Action</option>
                    <option value="flag">Flag as Important</option>
                    <option value="unflag">Remove Flag</option>
                    <option value="mark_resolved">Mark as Resolved</option>
                    <option value="archive">Archive</option>
                    @if(request('archived') === 'archived')
                    <option value="restore">Restore</option>
                    @endif
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                <button type="button" onclick="clearSelection()" class="btn btn-outline btn-sm">Cancel</button>
            </form>
        </div>

        <!-- Right Side - Pagination -->
        @if($tickets->hasPages())
        <nav style="display: flex; gap: 0.25rem; align-items: center; margin-left: auto;">
            {{-- First Page --}}
            @if($tickets->onFirstPage())
                <span style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem; font-weight: 500;">
                    « First
                </span>
            @else
                <a href="{{ $tickets->url(1) }}" style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    « First
                </a>
            @endif

            {{-- Previous Page --}}
            @if($tickets->onFirstPage())
                <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem;">
                    «
                </span>
            @else
                <a href="{{ $tickets->previousPageUrl() }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    «
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach(range(1, $tickets->lastPage()) as $page)
                @if($page == $tickets->currentPage())
                    <span style="min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0 0.5rem; border: 1px solid #3B82F6; background: #3B82F6; color: white; border-radius: 4px; font-size: 0.875rem; font-weight: 500;">
                        {{ $page }}
                    </span>
                @elseif($page == 1 || $page == $tickets->lastPage() || abs($page - $tickets->currentPage()) <= 2)
                    <a href="{{ $tickets->url($page) }}" style="min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0 0.5rem; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                        {{ $page }}
                    </a>
                @elseif($page == 2 || $page == $tickets->lastPage() - 1)
                    <span style="padding: 0 0.25rem; color: #94A3B8; font-size: 0.875rem;">...</span>
                @endif
            @endforeach

            {{-- Next Page --}}
            @if($tickets->hasMorePages())
                <a href="{{ $tickets->nextPageUrl() }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    »
                </a>
            @else
                <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem;">
                    »
                </span>
            @endif

            {{-- Last Page --}}
            @if($tickets->hasMorePages())
                <a href="{{ $tickets->url($tickets->lastPage()) }}" style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    Last »
                </a>
            @else
                <span style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem; font-weight: 500;">
                    Last »
                </span>
            @endif
        </nav>
        @endif
    </div>
</div>

<script>
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ticketCheckbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelection();
}

function updateSelection() {
    const checkboxes = document.querySelectorAll('.ticketCheckbox:checked');
    const bulkContainer = document.getElementById('bulkActionsContainer');
    const selectedIdsInput = document.getElementById('selectedTicketIds');
    
    if (checkboxes.length > 0) {
        bulkContainer.style.display = 'block';
        const ids = Array.from(checkboxes).map(cb => cb.value);
        selectedIdsInput.value = ids.join(',');
    } else {
        bulkContainer.style.display = 'none';
        selectedIdsInput.value = '';
    }
}

function clearSelection() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.ticketCheckbox').forEach(cb => cb.checked = false);
    document.getElementById('bulkActionsContainer').style.display = 'none';
}

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
@endsection
