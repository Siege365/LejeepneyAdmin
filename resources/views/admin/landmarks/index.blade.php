@extends('layouts.admin')

@section('title', 'Landmarks')
@section('page-title', 'Landmarks')

@section('content')
<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Landmarks Management</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">View, add, edit, and manage landmark locations.</p>
        </div>
        <a href="{{ route('admin.landmarks.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Landmark
        </a>
    </div>
</div>

<!-- Landmarks Table -->
<div class="card">
    <div class="card-header">
        <h3>All Landmarks ({{ $landmarks->total() }})</h3>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search landmarks...">
            </div>
            <select class="form-control" id="categoryFilter" style="width: auto; padding: 0.5rem 2rem 0.5rem 1rem;">
                <option value="">All Categories</option>
                <option value="city_center">City Center</option>
                <option value="mall">Mall</option>
                <option value="school">School</option>
                <option value="hospital">Hospital</option>
                <option value="transport">Transport</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table" id="landmarksTable">
            <thead>
                <tr>
                    <th style="width: 60px;">Icon</th>
                    <th>Name</th>
                    <th style="width: 120px;">Category</th>
                    <th style="width: 200px;">Location</th>
                    <th style="width: 80px;">Featured</th>
                    <th style="width: 60px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landmarks as $landmark)
                    <tr>
                        <td>
                            @if($landmark->icon_image)
                                <img src="{{ Storage::url($landmark->icon_image) }}" 
                                     alt="{{ $landmark->name }}" 
                                     class="rounded"
                                     style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #E2E8F0;">
                            @else
                                <div style="width: 45px; height: 45px; background: #E2E8F0; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid fa-image" style="color: #94A3B8; font-size: 1.25rem;"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #1E293B; margin-bottom: 0.25rem;">{{ $landmark->name }}</div>
                            @if($landmark->description)
                                <div style="color: #64748B; font-size: 0.875rem;">{{ Str::limit($landmark->description, 60) }}</div>
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
                            <span style="background: {{ $config['bg'] }}; color: {{ $config['color'] }}; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600; display: inline-block; white-space: nowrap;">
                                {{ $config['label'] }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.8125rem; color: #64748B;">
                                <i class="fa-solid fa-location-dot" style="margin-right: 0.25rem;"></i>
                                {{ number_format($landmark->latitude, 6) }}, {{ number_format($landmark->longitude, 6) }}
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if($landmark->is_featured)
                                <i class="fa-solid fa-star" style="color: #F59E0B; font-size: 1.125rem;" title="Featured"></i>
                            @else
                                <span style="color: #CBD5E1;">—</span>
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
                        <td colspan="6" style="text-align: center; padding: 3rem;">
                            <i class="fa-solid fa-map-marker-alt" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 1rem;"></i>
                            <p style="color: #64748B; margin: 0;">No landmarks found. Add your first landmark!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($landmarks->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #64748B; font-size: 0.875rem;">
            Showing {{ $landmarks->firstItem() }} to {{ $landmarks->lastItem() }} of {{ $landmarks->total() }} landmarks
        </div>
        <div>
            {{ $landmarks->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    </div>
    @endif
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value.toLowerCase();
    const tableRows = document.querySelectorAll('#landmarksTable tbody tr');
    
    tableRows.forEach(row => {
        if (row.querySelector('td[colspan]')) return; // Skip empty state row
        
        const name = row.cells[1].textContent.toLowerCase();
        const category = row.cells[2].textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchValue);
        const matchesCategory = !categoryFilter || category.includes(categoryFilter);
        
        row.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
});

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    document.getElementById('searchInput').dispatchEvent(new Event('keyup'));
});

// Auto-dismiss alerts
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    }, 5000);
});
</script>

@endsection
