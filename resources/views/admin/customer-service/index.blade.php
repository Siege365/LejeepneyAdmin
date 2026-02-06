@extends('layouts.admin')

@section('title', 'Customer Service')
@section('page-title', 'Customer Service')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/customer-service.css') }}?v={{ time() }}">
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
            
            <!-- Type Filter -->
            <select name="type" onchange="this.form.submit()" class="filter-select">
                <option value="all">All Types</option>
                <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                <option value="technical" {{ request('type') === 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="billing" {{ request('type') === 'billing' ? 'selected' : '' }}>Billing</option>
                <option value="feedback" {{ request('type') === 'feedback' ? 'selected' : '' }}>Feedback</option>
                <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            
            <!-- Priority Filter -->
            <select name="priority" onchange="this.form.submit()" class="filter-select">
                <option value="all">All Priorities</option>
                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>
            
            <!-- Sort Filter -->
            <select name="sort" onchange="this.form.submit()" class="filter-select">
                <option value="id_desc" {{ request('sort', 'id_desc') === 'id_desc' ? 'selected' : '' }}>ID: High to Low</option>
                <option value="id_asc" {{ request('sort') === 'id_asc' ? 'selected' : '' }}>ID: Low to High</option>
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
            </select>
            
            @if(request()->hasAny(['search', 'status', 'type', 'priority', 'flagged', 'archived', 'sort']))
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
                                <form action="{{ route('admin.customer-service.toggleFlag', $ticket->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="kebab-item kebab-btn {{ $ticket->is_flagged ? 'flag-active' : '' }}">
                                        <i class="fa-solid fa-flag"></i>
                                        {{ $ticket->is_flagged ? 'Remove Flag' : 'Flag Important' }}
                                    </button>
                                </form>
                                @if(!$ticket->is_archived)
                                <form action="{{ route('admin.customer-service.archive', $ticket->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this ticket?')">
                                    @csrf
                                    <button type="submit" class="kebab-item kebab-btn kebab-danger">
                                        <i class="fa-solid fa-archive"></i> Archive
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('admin.customer-service.restore', $ticket->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="kebab-item kebab-btn kebab-success">
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
                    <td colspan="9" class="empty-state">
                        <i class="fa-solid fa-headset empty-icon"></i>
                        <p class="empty-title">No Support Tickets</p>
                        <p class="empty-subtitle">
                            @if(request()->hasAny(['search', 'status', 'type', 'priority', 'flagged', 'archived']))
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

@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/customer-service-index.js') }}?v={{ time() }}"></script>
<script>
// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bulk selection
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.ticketCheckbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateSelection();
        });
    }
    
    document.querySelectorAll('.ticketCheckbox').forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });
});

function updateSelection() {
    const checkboxes = document.querySelectorAll('.ticketCheckbox:checked');
    const bulkContainer = document.getElementById('bulkActionsContainer');
    const selectedIdsInput = document.getElementById('selectedTicketIds');
    
    if (checkboxes.length > 0) {
        bulkContainer.classList.add('active');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        selectedIdsInput.value = ids.join(',');
    } else {
        bulkContainer.classList.remove('active');
        selectedIdsInput.value = '';
    }
}

function clearSelection() {
    document.getElementById('selectAll').checked = false;
    document.querySelectorAll('.ticketCheckbox').forEach(cb => cb.checked = false);
    document.getElementById('bulkActionsContainer').classList.remove('active');
}
</script>
@endpush
