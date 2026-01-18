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
        <a href="#" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Route
        </a>
    </div>
</div>

<!-- Routes Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Routes</h3>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search routes...">
            </div>
            <select class="form-control" style="width: auto; padding: 0.5rem 2rem 0.5rem 1rem;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Route Name</th>
                    <th>Start Point</th>
                    <th>End Point</th>
                    <th>Distance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($routes ?? [] as $route)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $route->name }}</td>
                    <td>{{ $route->start_point }}</td>
                    <td>{{ $route->end_point }}</td>
                    <td>{{ $route->distance }} km</td>
                    <td>
                        <span class="badge {{ $route->status === 'active' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($route->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn view" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Edit">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn delete" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-route"></i>
                            <h3>No Routes Found</h3>
                            <p>Get started by adding your first jeepney route.</p>
                            <a href="#" class="btn btn-primary">
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
</div>
@endsection
