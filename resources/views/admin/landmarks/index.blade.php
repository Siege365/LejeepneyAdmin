@extends('layouts.admin')

@section('title', 'Landmarks')
@section('page-title', 'Landmarks')

@section('content')
<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Manage Landmarks</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">View, add, edit, and delete landmarks for the Lejeepney app.</p>
        </div>
        <a href="#" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Add New Landmark
        </a>
    </div>
</div>

<!-- Landmarks Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Landmarks</h3>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <div class="search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" placeholder="Search landmarks...">
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
                    <th>Name</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($landmarks ?? [] as $landmark)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $landmark->name }}</td>
                    <td>{{ $landmark->location }}</td>
                    <td>{{ Str::limit($landmark->description, 50) }}</td>
                    <td>
                        <span class="badge {{ $landmark->status === 'active' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($landmark->status) }}
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
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-solid fa-map-marker-alt"></i>
                            <h3>No Landmarks Found</h3>
                            <p>Get started by adding your first landmark.</p>
                            <a href="#" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i>
                                Add New Landmark
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
