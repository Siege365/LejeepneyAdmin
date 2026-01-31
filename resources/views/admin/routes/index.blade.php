@extends('layouts.admin')

@section('title', 'Routes')
@section('page-title', 'Routes')

@section('content')
<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Manage Routes</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">View, add, edit, and delete jeepney routes.</p>
        </div>
        <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Route
        </a>
    </div>
</div>

<!-- Routes Table -->
<div class="card">
    <div class="card-header">
        <h3>All Routes ({{ $routes->total() }})</h3>
        <form method="GET" action="{{ route('admin.routes.index') }}" style="display: flex; gap: 1rem; align-items: center;">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search routes...">
            </div>
            <select class="form-control" name="status" style="width: auto; padding: 0.5rem 2rem 0.5rem 1rem;" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
            </select>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table" id="routesTable">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Route Name</th>
                    <th>Terminal</th>
                    <th>Distance</th>
                    <th>Base Fare</th>
                    <th style="width: 60px;">Color</th>
                    <th style="width: 100px;">Status</th>
                    <th style="width: 60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($routes as $route)
                <tr data-status="{{ $route->status }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong style="color: var(--secondary-blue);">{{ $route->name }}</strong>
                    </td>
                    <td>
                        <span style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-location-dot" style="color: var(--primary-gold);"></i>
                            {{ $route->terminal ?? 'Not set' }}
                        </span>
                    </td>
                    <td>{{ $route->total_distance ? number_format($route->total_distance, 2) . ' km' : 'N/A' }}</td>
                    <td>
                        <span style="font-weight: 600; color: var(--dark-gray);">₱13.00</span>
                    </td>
                    <td>
                        <div style="width: 32px; height: 32px; background: {{ $route->color ?? '#EBAF3E' }}; border-radius: 6px; border: 2px solid rgba(0,0,0,0.1); box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
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
                                    <button type="submit" class="kebab-item">
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
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fa-solid fa-route"></i>
                            <h3>No Routes Found</h3>
                            <p>Get started by adding your first jeepney route.</p>
                            <a href="{{ route('admin.routes.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i>
                                Add New Route
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($routes->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #64748B; font-size: 0.875rem;">
            Showing {{ $routes->firstItem() }} to {{ $routes->lastItem() }} of {{ $routes->total() }} routes
        </div>
        <nav style="display: flex; gap: 0.25rem; align-items: center;">
            {{-- First Page --}}
            @if($routes->onFirstPage())
                <span style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem; font-weight: 500;">
                    « First
                </span>
            @else
                <a href="{{ $routes->url(1) }}" style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    « First
                </a>
            @endif

            {{-- Previous Page --}}
            @if($routes->onFirstPage())
                <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem;">
                    «
                </span>
            @else
                <a href="{{ $routes->previousPageUrl() }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    «
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach(range(1, $routes->lastPage()) as $page)
                @if($page == $routes->currentPage())
                    <span style="min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0 0.5rem; border: 1px solid #3B82F6; background: #3B82F6; color: white; border-radius: 4px; font-size: 0.875rem; font-weight: 500;">
                        {{ $page }}
                    </span>
                @elseif($page == 1 || $page == $routes->lastPage() || abs($page - $routes->currentPage()) <= 2)
                    <a href="{{ $routes->url($page) }}" style="min-width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0 0.5rem; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                        {{ $page }}
                    </a>
                @elseif($page == 2 || $page == $routes->lastPage() - 1)
                    <span style="padding: 0 0.25rem; color: #94A3B8; font-size: 0.875rem;">...</span>
                @endif
            @endforeach

            {{-- Next Page --}}
            @if($routes->hasMorePages())
                <a href="{{ $routes->nextPageUrl() }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    »
                </a>
            @else
                <span style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem;">
                    »
                </span>
            @endif

            {{-- Last Page --}}
            @if($routes->hasMorePages())
                <a href="{{ $routes->url($routes->lastPage()) }}" style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: white; color: #475569; border-radius: 4px; text-decoration: none; transition: all 0.2s; font-size: 0.875rem; font-weight: 500;" onmouseenter="this.style.background='#F8FAFC'; this.style.borderColor='#CBD5E1'" onmouseleave="this.style.background='white'; this.style.borderColor='#E2E8F0'">
                    Last »
                </a>
            @else
                <span style="padding: 0.5rem 0.75rem; height: 32px; display: flex; align-items: center; justify-content: center; border: 1px solid #E2E8F0; background: #F8FAFC; color: #CBD5E1; border-radius: 4px; cursor: not-allowed; font-size: 0.875rem; font-weight: 500;">
                    Last »
                </span>
            @endif
        </nav>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/routes-index.js') }}"></script>
@endpush
