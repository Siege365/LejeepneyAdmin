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

<!-- Stats Grid -->
<div class="stats-grid stats-grid-4">
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Total Routes</p>
            <p class="stat-value">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-amber">
            <i class="fa-solid fa-route"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Available</p>
            <p class="stat-value">{{ $stats['available'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-green">
            <i class="fa-solid fa-check-circle"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Unavailable</p>
            <p class="stat-value">{{ $stats['unavailable'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-red">
            <i class="fa-solid fa-times-circle"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Total Distance</p>
            <p class="stat-value">{{ number_format($stats['total_distance'] ?? 0, 1) }} km</p>
        </div>
        <div class="stat-icon stat-icon-blue">
            <i class="fa-solid fa-road"></i>
        </div>
    </div>
</div>

<!-- Routes Table -->
<div class="card">
    <div class="card-header filters-header">
        <h3>All Routes</h3>
        <form method="GET" action="{{ route('admin.routes.index') }}" class="filters-form">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search routes...">
            </div>
            <select class="filter-select" name="filter" onchange="this.form.submit()">
                <option value="all" {{ request('filter', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                <option value="available" {{ request('filter') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="unavailable" {{ request('filter') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                <option value="name_asc" {{ request('filter') === 'name_asc' ? 'selected' : '' }}>A to Z</option>
                <option value="name_desc" {{ request('filter') === 'name_desc' ? 'selected' : '' }}>Z to A</option>
            </select>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="routesTable">
            <thead>
                <tr>
                    <th class="th-checkbox">
                        <input type="checkbox" id="selectAllRoutes" class="select-all">
                    </th>
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
                    <td>
                        <input type="checkbox" class="row-checkbox" value="{{ $route->id }}" data-name="{{ $route->name }}">
                    </td>
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
                                <button type="button" class="kebab-item kebab-btn" onclick="showToggleStatusModal({{ $route->id }}, '{{ addslashes($route->name) }}', '{{ $route->status }}')">
                                    <i class="fa-solid fa-toggle-{{ $route->status === 'available' ? 'off' : 'on' }}"></i>
                                    {{ $route->status === 'available' ? 'Disable' : 'Enable' }}
                                </button>
                                <div class="kebab-divider"></div>
                                <button type="button" class="kebab-item danger" onclick="showDeleteRouteModal({{ $route->id }}, '{{ addslashes($route->name) }}')">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="9" class="empty-state">
                        <i class="fa-solid fa-route empty-icon"></i>
                        <p class="empty-title">No Routes Found</p>
                        <p class="empty-subtitle">Get started by adding your first jeepney route.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination and Bulk Actions -->
    <div class="table-footer">
        <div id="bulkActionsContainer" class="bulk-actions-container">
            <button type="button" class="btn btn-danger btn-sm" onclick="showBatchDeleteModal()">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
            <button type="button" onclick="clearSelection()" class="btn btn-outline btn-sm">Cancel</button>
        </div>
        @include('components.admin.pagination', ['paginator' => $routes])
    </div>
</div>

<!-- Double Confirmation Modal for Batch Delete -->
<div class="modal-backdrop" id="batchDeleteModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #EF4444;"></i> Confirm Deletion</h3>
            <button class="modal-close-btn" onclick="closeBatchDeleteModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>You are about to delete <strong id="deleteCount">0</strong> route(s):</p>
            <ul id="deleteList" style="max-height: 150px; overflow-y: auto; margin: 1rem 0;"></ul>
            <p style="color: #EF4444; font-weight: 600;">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeBatchDeleteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="showFinalConfirmation()">
                <i class="fa-solid fa-trash"></i> Yes, Delete
            </button>
        </div>
    </div>
</div>

<!-- Final Confirmation Modal -->
<div class="modal-backdrop" id="finalConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #EF4444;"><i class="fa-solid fa-triangle-exclamation"></i> Final Warning</h3>
            <button class="modal-close-btn" onclick="closeFinalConfirmation()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size: 1.1rem; font-weight: 600; text-align: center;">Are you absolutely sure?</p>
            <p style="text-align: center;">All selected routes will be permanently deleted.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeFinalConfirmation()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmBatchDelete()">
                <i class="fa-solid fa-trash-can"></i> Permanently Delete
            </button>
        </div>
    </div>
</div>

<!-- Single Delete Modal -->
<div class="modal-backdrop" id="deleteRouteModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #EF4444;"></i> Delete Route</h3>
            <button class="modal-close-btn" onclick="closeDeleteRouteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <strong id="deleteRouteName"></strong>?</p>
            <p style="color: #EF4444; font-weight: 600; margin-top: 0.5rem;">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeDeleteRouteModal()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="showDeleteRouteConfirm()"><i class="fa-solid fa-trash"></i> Yes, Delete</button>
        </div>
    </div>
</div>

<!-- Single Delete Confirm Modal -->
<div class="modal-backdrop" id="deleteRouteConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #EF4444;"><i class="fa-solid fa-triangle-exclamation"></i> Final Warning</h3>
            <button class="modal-close-btn" onclick="closeDeleteRouteConfirm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="text-align: center; font-weight: 600;">This route will be permanently deleted.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeDeleteRouteConfirm()">Cancel</button>
            <form id="deleteRouteForm" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash-can"></i> Permanently Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Status Modal -->
<div class="modal-backdrop" id="toggleStatusModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title" id="toggleStatusTitle"><i class="fa-solid fa-toggle-on" style="color: var(--secondary-blue);"></i> Update Status</h3>
            <button class="modal-close-btn" onclick="closeToggleStatusModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="toggleStatusMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeToggleStatusModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="showToggleStatusConfirm()">Continue</button>
        </div>
    </div>
</div>

<!-- Toggle Status Confirm Modal -->
<div class="modal-backdrop" id="toggleStatusConfirmModal" style="display: none;">
    <div class="modal-container modal-sm">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-exclamation-triangle" style="color: #F59E0B;"></i> Confirm Status Change</h3>
            <button class="modal-close-btn" onclick="closeToggleStatusConfirm()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p id="toggleStatusConfirmMessage" style="text-align: center; font-weight: 600;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeToggleStatusConfirm()">Cancel</button>
            <form id="toggleStatusForm" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">Confirm</button>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/pages/routes-batch.js')
<script>
// Single route delete - double confirmation
let pendingDeleteRouteId = null;

function showDeleteRouteModal(id, name) {
    pendingDeleteRouteId = id;
    document.getElementById('deleteRouteName').textContent = name;
    document.getElementById('deleteRouteModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeDeleteRouteModal() {
    document.getElementById('deleteRouteModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showDeleteRouteConfirm() {
    closeDeleteRouteModal();
    document.getElementById('deleteRouteForm').action = '/routes/' + pendingDeleteRouteId;
    document.getElementById('deleteRouteConfirmModal').style.display = 'flex';
}
function closeDeleteRouteConfirm() {
    document.getElementById('deleteRouteConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Toggle status - double confirmation
let pendingToggleId = null;

function showToggleStatusModal(id, name, currentStatus) {
    pendingToggleId = id;
    const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
    const action = currentStatus === 'available' ? 'disable' : 'enable';
    document.getElementById('toggleStatusMessage').textContent = 'Are you sure you want to ' + action + ' "' + name + '"?';
    document.getElementById('toggleStatusConfirmMessage').textContent = 'This will ' + action + ' the route "' + name + '".';
    document.getElementById('toggleStatusModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeToggleStatusModal() {
    document.getElementById('toggleStatusModal').style.display = 'none';
    document.body.style.overflow = '';
}
function showToggleStatusConfirm() {
    closeToggleStatusModal();
    document.getElementById('toggleStatusForm').action = '/routes/' + pendingToggleId + '/toggle-status';
    document.getElementById('toggleStatusConfirmModal').style.display = 'flex';
}
function closeToggleStatusConfirm() {
    document.getElementById('toggleStatusConfirmModal').style.display = 'none';
    document.body.style.overflow = '';
}
</script>
@endpush
