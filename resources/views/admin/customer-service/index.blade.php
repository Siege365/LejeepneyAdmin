@extends('layouts.admin')

@section('title', 'Customer Service')
@section('page-title', 'Customer Service')

@section('content')
<!-- Page Header -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="margin-bottom: 0;">
        <div>
            <h2 style="font-size: 1.25rem; margin-bottom: 0.25rem;">Customer Service</h2>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Manage customer inquiries and support tickets.</p>
        </div>
    </div>
</div>

<!-- Filter Tabs -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
    <button class="btn btn-primary btn-sm">All Tickets</button>
    <button class="btn btn-outline btn-sm">
        <span class="badge badge-warning" style="margin-right: 0.25rem;">5</span>
        Pending
    </button>
    <button class="btn btn-outline btn-sm">
        <span class="badge badge-info" style="margin-right: 0.25rem;">3</span>
        In Progress
    </button>
    <button class="btn btn-outline btn-sm">
        <span class="badge badge-success" style="margin-right: 0.25rem;">12</span>
        Resolved
    </button>
</div>

<!-- Tickets Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Support Tickets</h3>
        <div class="search-box">
            <i class="fa-solid fa-search"></i>
            <input type="text" placeholder="Search tickets...">
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tickets ?? [] as $ticket)
                <tr>
                    <td>#{{ $ticket->id }}</td>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ $ticket->customer_name }}</td>
                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $ticket->priority === 'high' ? 'danger' : ($ticket->priority === 'medium' ? 'warning' : 'info') }}">
                            {{ ucfirst($ticket->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-{{ $ticket->status === 'resolved' ? 'success' : ($ticket->status === 'in-progress' ? 'info' : 'warning') }}">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn view" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn edit" title="Reply">
                                <i class="fa-solid fa-reply"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fa-solid fa-headset"></i>
                            <h3>No Support Tickets</h3>
                            <p>All caught up! There are no customer inquiries at the moment.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
