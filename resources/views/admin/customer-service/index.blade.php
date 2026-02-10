@extends('layouts.admin')

@section('title', 'Customer Service')
@section('page-title', 'Customer Service')

@push('styles')
@vite('resources/css/pages/customer-service.css')
@endpush

@section('content')
<!-- Page Header -->
<div class="card cs-page-header">
    <div class="card-header">
        <div>
            <h2 class="cs-page-title">Customer Service</h2>
            <p class="cs-page-subtitle">Manage customer inquiries and support tickets.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.customer-service.index', ['archived' => 'archived']) }}" class="btn btn-primary">
                <i class="fa-solid fa-archive"></i>
                Archived ({{ $stats['archived'] ?? 0 }})
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid stats-grid-5">
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Total</p>
            <p class="stat-value">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-blue">
            <i class="fa-solid fa-ticket"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Pending</p>
            <p class="stat-value stat-value-amber">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-amber">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">In Progress</p>
            <p class="stat-value stat-value-blue">{{ $stats['in_progress'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-blue">
            <i class="fa-solid fa-spinner"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Resolved</p>
            <p class="stat-value stat-value-green">{{ $stats['resolved'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Flagged</p>
            <p class="stat-value stat-value-red">{{ $stats['flagged'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-red">
            <i class="fa-solid fa-flag"></i>
        </div>
    </div>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-header filters-header">
        <h3>Support Tickets</h3>
        <form method="GET" action="{{ route('admin.customer-service.index') }}" class="filters-form">
            <!-- Search -->
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tickets...">
            </div>
            
            <!-- Combined Filter -->
            <select name="filter" onchange="this.form.submit()" class="filter-select">
                <option value="all" {{ request('filter', 'all') === 'all' ? 'selected' : '' }}>All Tickets</option>
                <optgroup label="Status">
                    <option value="pending" {{ request('filter') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in-progress" {{ request('filter') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="resolved" {{ request('filter') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="cancelled" {{ request('filter') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </optgroup>
                <optgroup label="Type">
                    <option value="general" {{ request('filter') === 'general' ? 'selected' : '' }}>General</option>
                    <option value="technical" {{ request('filter') === 'technical' ? 'selected' : '' }}>Technical</option>
                    <option value="billing" {{ request('filter') === 'billing' ? 'selected' : '' }}>Billing</option>
                    <option value="feedback" {{ request('filter') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                    <option value="other" {{ request('filter') === 'other' ? 'selected' : '' }}>Other</option>
                </optgroup>
                <optgroup label="Priority">
                    <option value="high" {{ request('filter') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('filter') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('filter') === 'low' ? 'selected' : '' }}>Low</option>
                </optgroup>
                <optgroup label="Other">
                    <option value="flagged" {{ request('filter') === 'flagged' ? 'selected' : '' }}>Flagged</option>
                    <option value="newest" {{ request('filter') === 'newest' ? 'selected' : '' }}>Newest First</option>
                </optgroup>
            </select>
            
            @if(request()->hasAny(['search', 'filter', 'archived']))
                <a href="{{ route('admin.customer-service.index') }}" class="btn btn-outline btn-sm">
                    <i class="fa-solid fa-times"></i> Clear
                </a>
            @endif
        </form>
    </div>
    
    <div class="table-container">
        <table class="table data-table">
            <thead>
                <tr>
                    <th class="th-checkbox">
                        <input type="checkbox" class="select-all" id="selectAll">
                    </th>
                    <th class="th-id">#</th>
                    <th>Subject</th>
                    <th class="th-customer">Customer</th>
                    <th class="th-type">Type</th>
                    <th class="th-date">Date</th>
                    <th class="th-priority">Priority</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets ?? [] as $ticket)
                <tr class="{{ $ticket->is_flagged ? 'ticket-row-flagged' : '' }}">
                    <td>
                        <input type="checkbox" class="row-checkbox ticketCheckbox" value="{{ $ticket->id }}">
                    </td>
                    <td>
                        <div class="ticket-id-cell">
                            @if($ticket->is_flagged)
                            <i class="fa-solid fa-flag flag-icon" title="Flagged"></i>
                            @endif
                            <span class="ticket-id">#{{ $ticket->id }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="ticket-subject">{{ Str::limit($ticket->subject, 40) }}</div>
                        <div class="ticket-email">{{ $ticket->email }}</div>
                    </td>
                    <td>
                        <div class="ticket-customer">
                            <div class="ticket-customer-avatar">
                                {{ substr($ticket->name, 0, 1) }}
                            </div>
                            <span class="ticket-customer-name">{{ Str::limit($ticket->name, 15) }}</span>
                        </div>
                    </td>
                    <td>
                        @php $tStyle = $ticket->typeStyles; @endphp
                        <span class="badge" style="background: {{ $tStyle['bg'] }}; color: {{ $tStyle['color'] }};">
                            <i class="fa-solid {{ $tStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </td>
                    <td>
                        <div class="ticket-date">{{ $ticket->created_at->format('M d, Y') }}</div>
                        <div class="ticket-time">{{ $ticket->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        @php $pStyle = $ticket->priorityStyles; @endphp
                        <span class="badge" style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['color'] }};">
                            <i class="fa-solid {{ $pStyle['icon'] }}"></i>
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>
                        @php $sStyle = $ticket->statusStyles; @endphp
                        <span class="badge" style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['color'] }};">
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
                                <button type="button" class="kebab-item kebab-btn {{ $ticket->is_flagged ? 'flag-active' : '' }}" onclick="showTicketFlagModal({{ $ticket->id }}, '{{ addslashes($ticket->subject) }}', {{ $ticket->is_flagged ? 'true' : 'false' }})">
                                    <i class="fa-solid fa-flag"></i>
                                    {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag Important' }}
                                </button>
                                @if(!$ticket->is_archived)
                                <button type="button" class="kebab-item kebab-btn kebab-danger" onclick="showTicketArchiveModal({{ $ticket->id }}, '{{ addslashes($ticket->subject) }}')">
                                    <i class="fa-solid fa-archive"></i> Archive
                                </button>
                                @else
                                <button type="button" class="kebab-item kebab-btn kebab-success" onclick="showTicketRestoreModal({{ $ticket->id }}, '{{ addslashes($ticket->subject) }}')">
                                    <i class="fa-solid fa-rotate-left"></i> Restore
                                </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="empty-state">
                        <i class="fa-solid fa-headset empty-icon"></i>
                        <p class="empty-title">No Support Tickets</p>
                        <p class="empty-subtitle">
                            @if(request()->hasAny(['search', 'filter', 'archived']))
                                No tickets match your filters. <a href="{{ route('admin.customer-service.index') }}">Clear filters</a>
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
    <div class="table-footer">
        <!-- Left Side - Bulk Actions -->
        <div id="bulkActionsContainer" class="bulk-actions-container">
            <form id="bulkActionForm" method="POST" action="{{ route('admin.customer-service.bulk-action') }}">
                @csrf
                <input type="hidden" name="ticket_ids" id="selectedTicketIds">
                <select name="action" id="bulkAction" class="filter-select" required>
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
        @include('components.admin.pagination', ['paginator' => $tickets])
    </div>
</div>

<!-- Flag Modal -->
<div class="modal-backdrop" id="ticketFlagModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-flag" style="color: #EF4444;"></i> <span id="flagModalTitle">Flag Ticket</span></h3>
            <button class="modal-close-btn" onclick="closeTicketFlagModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="flagModalMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketFlagModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="showTicketFlagConfirm()">Continue</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="ticketFlagConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="flagConfirmMessage" style="text-align: center; font-weight: 600;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketFlagConfirm()">Cancel</button>
            <form id="ticketFlagForm" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal-backdrop" id="ticketArchiveModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-archive" style="color: #EF4444;"></i> Archive Ticket</h3>
            <button class="modal-close-btn" onclick="closeTicketArchiveModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to archive <strong id="archiveTicketSubject"></strong>?</p>
            <p style="font-size: 0.875rem; color: #64748B; margin-top: 0.5rem;">Archived tickets can be restored later.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketArchiveModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="showTicketArchiveConfirm()">Continue</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="ticketArchiveConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #EF4444;"></i> Confirm Archive</h3>
        </div>
        <div class="modal-body">
            <p style="text-align: center; font-weight: 600;">This ticket will be archived. You can restore it later.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketArchiveConfirm()">Cancel</button>
            <form id="ticketArchiveForm" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-danger">Confirm Archive</button>
            </form>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal-backdrop" id="ticketRestoreModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-rotate-left" style="color: #10B981;"></i> Restore Ticket</h3>
            <button class="modal-close-btn" onclick="closeTicketRestoreModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to restore <strong id="restoreTicketSubject"></strong>?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketRestoreModal()">Cancel</button>
            <button type="button" class="btn btn-success" onclick="showTicketRestoreConfirm()">Continue</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="ticketRestoreConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Restore</h3>
        </div>
        <div class="modal-body">
            <p style="text-align: center; font-weight: 600;">This ticket will be restored to active tickets.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeTicketRestoreConfirm()">Cancel</button>
            <form id="ticketRestoreForm" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-success">Confirm Restore</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/pages/customer-service-index.js')
@endpush
