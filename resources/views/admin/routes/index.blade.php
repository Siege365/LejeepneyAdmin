@extends('layouts.admin')

@section('title', 'Routes')
@section('page-title', 'Routes')

@section('content')
<!-- Page Header -->
<div class="card cs-page-header">
    <div class="card-header">
        <div>
            <h2 class="cs-page-title">Manage Routes</h2>
            <p class="cs-page-subtitle">View, add, edit, and delete jeepney routes.</p>
        </div>
        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Route
        </a>
    </div>
</div>

<!-- Routes Table -->
<div class="card">
    <div class="card-header filters-header">
        <h3>All Routes ({{ $routes->total() }})</h3>
        <form method="GET" action="{{ route('admin.routes.index') }}" class="filters-form">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search routes...">
            </div>
            <select class="filter-select" name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
            </select>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="routesTable">
            <thead>
                <tr>
                    <th class="th-id">#</th>
                    <th>Route Name</th>
                    <th>Terminal</th>
                    <th>Distance</th>
                    <th>Base Fare</th>
                    <th class="th-icon">Color</th>
                    <th class="th-status">Status</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($routes as $route)
                <tr data-status="{{ $route->status }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong class="route-name">{{ $route->name }}</strong>
                    </td>
                    <td>
                        <span class="terminal-cell">
                            <i class="fa-solid fa-location-dot terminal-icon"></i>
                            {{ $route->terminal ?? 'Not set' }}
                        </span>
                    </td>
                    <td>{{ $route->total_distance ? number_format($route->total_distance, 2) . ' km' : 'N/A' }}</td>
                    <td>
                        <span class="fare-cell">₱13.00</span>
                    </td>
                    <td>
                        <div class="color-swatch" style="background: {{ $route->color ?? '#EBAF3E' }};"></div>
                    </td>
                    <td>
                        <span class="badge {{ $route->status === 'available' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($route->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="kebab-menu">
                            <button type="button" class="kebab-trigger" onclick="toggleKebabMenu(this)">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="kebab-dropdown">
                                <a href="{{ route('admin.routes.edit', $route) }}" class="kebab-item">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <form action="{{ route('admin.routes.toggle-status', $route) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="kebab-item kebab-btn">
                                        <i class="fa-solid fa-toggle-{{ $route->status === 'available' ? 'off' : 'on' }}"></i>
                                        {{ $route->status === 'available' ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <div class="kebab-divider"></div>
                                <form action="{{ route('admin.routes.destroy', $route) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this route?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="kebab-item danger">
                                        <i class="fa-solid fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="8" class="empty-state">
                        <i class="fa-solid fa-route empty-icon"></i>
                        <p class="empty-title">No Routes Found</p>
                        <p class="empty-subtitle">Get started by adding your first jeepney route.</p>
                        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i>
                            Add New Route
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="table-footer">
        @include('components.admin.pagination', ['paginator' => $routes])
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/routes-index.js') }}"></script>
@endpush
