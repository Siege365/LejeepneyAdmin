@extends('layouts.admin')

@section('title', 'Audit Trail')
@section('page-title', 'Audit Trail')
@push('styles')
    @vite('resources/css/pages/audit-trail.css')
@endpush

@section('content')
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="card cs-page-header">
        <div class="card-header">
            <div>
                <h2 class="cs-page-title">
                    Audit Trail
                </h2>
                <p class="cs-page-subtitle">Complete history of all system activities and changes</p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-outline" id="toggleFilters">
                    <i class="fa-solid fa-filter"></i>
                    Filters
                </button>
                <form action="{{ route('admin.audit-trail.export') }}" method="GET" id="exportForm" style="display: inline;">
                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                    <input type="hidden" name="action" value="{{ request('action') }}">
                    <input type="hidden" name="model_type" value="{{ request('model_type') }}">
                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-download"></i>
                        Export CSV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="filters-panel" id="filtersPanel" style="display: {{ request()->hasAny(['user_id', 'action', 'model_type', 'date_from', 'date_to', 'search']) ? 'block' : 'none' }};">
        <div class="card">
            <form action="{{ route('admin.audit-trail.index') }}" method="GET" id="filterForm">
                <div class="filters-grid">
                    <!-- Search -->
                    <div class="filter-group">
                        <label for="search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Search
                        </label>
                        <input type="text" 
                               id="search" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search by name, description, IP..."
                               value="{{ request('search') }}">
                    </div>

                    <!-- User Filter -->
                    <div class="filter-group">
                        <label for="user_id">
                            <i class="fa-solid fa-user"></i>
                            User
                        </label>
                        <select id="user_id" name="user_id" class="form-control">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->user_id }}" {{ request('user_id') == $user->user_id ? 'selected' : '' }}>
                                    {{ $user->user_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Filter -->
                    <div class="filter-group">
                        <label for="action">
                            <i class="fa-solid fa-bolt"></i>
                            Action
                        </label>
                        <select id="action" name="action" class="form-control">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Model Type Filter -->
                    <div class="filter-group">
                        <label for="model_type">
                            <i class="fa-solid fa-cube"></i>
                            Model Type
                        </label>
                        <select id="model_type" name="model_type" class="form-control">
                            <option value="">All Types</option>
                            @foreach($modelTypes as $type)
                                <option value="{{ $type }}" {{ request('model_type') == $type ? 'selected' : '' }}>
                                    {{ class_basename($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div class="filter-group">
                        <label for="date_from">
                            <i class="fa-solid fa-calendar"></i>
                            Date From
                        </label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               class="form-control"
                               value="{{ request('date_from') }}">
                    </div>

                    <!-- Date To -->
                    <div class="filter-group">
                        <label for="date_to">
                            <i class="fa-solid fa-calendar"></i>
                            Date To
                        </label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               class="form-control"
                               value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-filter"></i>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.audit-trail.index') }}" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i>
                        Clear All
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request()->hasAny(['user_id', 'action', 'model_type', 'date_from', 'date_to', 'search']))
    <div class="active-filters">
        <span class="active-filters-label">Active Filters:</span>
        @if(request('search'))
            <span class="filter-tag">
                Search: {{ request('search') }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('search'))) }}">×</a>
            </span>
        @endif
        @if(request('user_id'))
            <span class="filter-tag">
                User: {{ $users->firstWhere('user_id', request('user_id'))?->user_name }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('user_id'))) }}">×</a>
            </span>
        @endif
        @if(request('action'))
            <span class="filter-tag">
                Action: {{ ucfirst(str_replace('_', ' ', request('action'))) }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('action'))) }}">×</a>
            </span>
        @endif
        @if(request('model_type'))
            <span class="filter-tag">
                Type: {{ class_basename(request('model_type')) }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('model_type'))) }}">×</a>
            </span>
        @endif
        @if(request('date_from'))
            <span class="filter-tag">
                From: {{ request('date_from') }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('date_from'))) }}">×</a>
            </span>
        @endif
        @if(request('date_to'))
            <span class="filter-tag">
                To: {{ request('date_to') }}
                <a href="{{ route('admin.audit-trail.index', array_merge(request()->except('date_to'))) }}">×</a>
            </span>
        @endif
    </div>
    @endif

    <!-- Audit Trail Table -->
    <div class="card">
        <div class="table-container">
            <table class="table activity-table">
                <thead>
                    <tr>
                        <th class="th-icon"></th>
                        <th>Activity</th>
                        <th class="th-user">User</th>
                        <th class="th-date">Date & Time</th>
                        <th class="th-status">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td class="activity-icon-cell">
                                <i class="fa-solid {{ $activity->icon }} activity-icon {{ $activity->action }}"></i>
                            </td>
                            <td>
                                <div class="activity-description">{{ $activity->description }}</div>
                                <div class="activity-meta">
                                    {{ class_basename($activity->model_type) }}{{ $activity->model_name ? ' • ' . $activity->model_name : '' }}
                                </div>
                            </td>
                            <td>
                                <div class="activity-user">
                                    <div class="activity-user-avatar">
                                        {{ substr($activity->user_name, 0, 1) }}
                                    </div>
                                    <span class="activity-user-name">{{ $activity->user_name }}</span>
                                </div>
                            </td>
                            <td class="activity-date">
                                {{ $activity->created_at->format('M d, Y') }}<br>
                                <span class="activity-time">{{ $activity->created_at->format('h:i A') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $activity->color }}">
                                    {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="activity-empty">
                                <i class="fa-solid fa-inbox fa-2x"></i>
                                <p>No audit trail records found.</p>
                                @if(request()->hasAny(['user_id', 'action', 'model_type', 'date_from', 'date_to', 'search']))
                                    <p class="empty-help">Try adjusting your filters or <a href="{{ route('admin.audit-trail.index') }}">clear all filters</a>.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($activities->hasPages())
        <div class="table-footer">
            @include('components.admin.pagination', ['paginator' => $activities])
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/pages/audit-trail.js')
@endpush
