@extends('layouts.admin')

@section('title', 'Landmarks')
@section('page-title', 'Landmarks')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/components/stats-cards.css') }}?v={{ time() }}">
@endpush

@section('content')
<!-- Page Header -->
<div class="card cs-page-header">
    <div class="card-header">
        <div>
            <h2 class="cs-page-title">Landmarks Management</h2>
            <p class="cs-page-subtitle">View, add, edit, and manage landmark locations.</p>
        </div>
        <a href="{{ route('admin.landmarks.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Landmark
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid stats-grid-5">
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Total Landmarks</p>
            <p class="stat-value">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-amber">
            <i class="fa-solid fa-map-marker-alt"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">City Centers</p>
            <p class="stat-value">{{ $stats['city_center'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-blue">
            <i class="fa-solid fa-building"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Malls</p>
            <p class="stat-value">{{ $stats['malls'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-green">
            <i class="fa-solid fa-shopping-bag"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Schools</p>
            <p class="stat-value">{{ $stats['schools'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-indigo">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
    </div>
    
    <div class="stat-card-mini">
        <div class="stat-content">
            <p class="stat-label">Hospitals</p>
            <p class="stat-value">{{ $stats['hospitals'] ?? 0 }}</p>
        </div>
        <div class="stat-icon stat-icon-red">
            <i class="fa-solid fa-hospital"></i>
        </div>
    </div>
</div>

<!-- Landmarks Table -->
<div class="card">
    <div class="card-header filters-header">
        <h3>All Landmarks</h3>
        <form method="GET" action="{{ route('admin.landmarks.index') }}" class="filters-form">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input id="searchInput" type="text" name="search" value="{{ request('search') }}" placeholder="Search landmarks...">
            </div>
            <select id="categoryFilter" class="filter-select" name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="city_center" {{ request('category') == 'city_center' ? 'selected' : '' }}>City Center</option>
                <option value="mall" {{ request('category') == 'mall' ? 'selected' : '' }}>Mall</option>
                <option value="school" {{ request('category') == 'school' ? 'selected' : '' }}>School</option>
                <option value="hospital" {{ request('category') == 'hospital' ? 'selected' : '' }}>Hospital</option>
                <option value="transport" {{ request('category') == 'transport' ? 'selected' : '' }}>Transport</option>
                <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            <select class="filter-select" name="sort" onchange="this.form.submit()">
                <option value="id_desc" {{ request('sort', 'id_desc') === 'id_desc' ? 'selected' : '' }}>ID: High to Low</option>
                <option value="id_asc" {{ request('sort') === 'id_asc' ? 'selected' : '' }}>ID: Low to High</option>
                <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name: Z to A</option>
            </select>
        </form>
    </div>
    
    <div class="table-container">
        <table class="table data-table" id="landmarksTable">
            <thead>
                <tr>
                    <th class="th-checkbox">
                        <input type="checkbox" id="selectAllLandmarks" class="select-all">
                    </th>
                    <th class="th-icon">Icon</th>
                    <th>Name</th>
                    <th class="th-category">Category</th>
                    <th class="th-location">Location</th>
                    <th class="th-featured">Featured</th>
                    <th class="th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landmarks as $landmark)
                    <tr>
                        <td>
                            <input type="checkbox" class="row-checkbox" value="{{ $landmark->id }}" data-name="{{ $landmark->name }}">
                        </td>
                        <td>
                            @if($landmark->icon_image)
                                <img src="{{ Storage::url($landmark->icon_image) }}" 
                                     alt="{{ $landmark->name }}" 
                                     class="table-icon-img">
                            @else
                                <div class="table-icon-placeholder">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="table-cell-title">{{ $landmark->name }}</div>
                            @if($landmark->description)
                                <div class="table-cell-subtitle">{{ Str::limit($landmark->description, 60) }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $categoryConfig = [
                                    'city_center' => ['label' => 'City Center', 'color' => '#3B82F6', 'bg' => '#DBEAFE'],
                                    'mall' => ['label' => 'Mall', 'color' => '#10B981', 'bg' => '#D1FAE5'],
                                    'school' => ['label' => 'School', 'color' => '#8B5CF6', 'bg' => '#EDE9FE'],
                                    'hospital' => ['label' => 'Hospital', 'color' => '#EF4444', 'bg' => '#FEE2E2'],
                                    'transport' => ['label' => 'Transport', 'color' => '#F59E0B', 'bg' => '#FEF3C7'],
                                    'other' => ['label' => 'Other', 'color' => '#6B7280', 'bg' => '#F3F4F6']
                                ];
                                $config = $categoryConfig[$landmark->category] ?? $categoryConfig['other'];
                            @endphp
                            <span class="badge" style="background: {{ $config['bg'] }}; color: {{ $config['color'] }};">
                                {{ $config['label'] }}
                            </span>
                        </td>
                        <td>
                            <div class="table-cell-location">
                                <i class="fa-solid fa-location-dot"></i>
                                {{ number_format($landmark->latitude, 6) }}, {{ number_format($landmark->longitude, 6) }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($landmark->is_featured)
                                <i class="fa-solid fa-star featured-icon" title="Featured"></i>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="kebab-menu">
                                <button type="button" class="kebab-trigger" onclick="toggleKebabMenu(this)">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div class="kebab-dropdown">
                                    <a href="{{ route('admin.landmarks.edit', $landmark) }}" class="kebab-item">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <div class="kebab-divider"></div>
                                    <form action="{{ route('admin.landmarks.destroy', $landmark) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete {{ addslashes($landmark->name) }}?');">
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
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fa-solid fa-map-marker-alt empty-icon"></i>
                            <p class="empty-title">No landmarks found</p>
                            <p class="empty-subtitle">Add your first landmark!</p>
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
        @include('components.admin.pagination', ['paginator' => $landmarks])
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
            <p>You are about to delete <strong id="deleteCount">0</strong> landmark(s):</p>
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
            <p style="text-align: center;">All selected landmarks will be permanently deleted.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeFinalConfirmation()">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="confirmBatchDelete()">
                <i class="fa-solid fa-trash-can"></i> Permanently Delete
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/pages/landmarks-batch.js') }}?v={{ time() }}"></script>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/pages/landmarks-index.js') }}?v={{ time() }}"></script>
@endpush
