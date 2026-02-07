@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
@vite('resources/css/pages/dashboard.css')
@endpush

@section('content')
<!-- Welcome Section -->
<div class="card welcome-card">
    <h2 class="welcome-title">Welcome back, {{ Auth::user()->name }}! 👋</h2>
    <p class="welcome-subtitle">Here's what's happening with your Lejeepney admin panel today.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Landmarks</h3>
            <div class="stat-number">{{ $totalLandmarks ?? 0 }}</div>
            <div class="stat-change positive">
                <i class="fa-solid fa-arrow-up"></i> 12% from last month
            </div>
        </div>
        <div class="stat-icon gold">
            <i class="fa-solid fa-map-marker-alt"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Routes</h3>
            <div class="stat-number">{{ $totalRoutes ?? 0 }}</div>
            <div class="stat-change positive">
                <i class="fa-solid fa-arrow-up"></i> 8% from last month
            </div>
        </div>
        <div class="stat-icon blue">
            <i class="fa-solid fa-route"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Active Users</h3>
            <div class="stat-number">{{ $activeUsers ?? 0 }}</div>
            <div class="stat-change positive">
                <i class="fa-solid fa-arrow-up"></i> 24% from last month
            </div>
        </div>
        <div class="stat-icon green">
            <i class="fa-solid fa-users"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Pending Requests</h3>
            <div class="stat-number">{{ $pendingRequests ?? 0 }}</div>
            <div class="stat-change negative">
                <i class="fa-solid fa-arrow-down"></i> 5% from last week
            </div>
        </div>
        <div class="stat-icon orange">
            <i class="fa-solid fa-clock"></i>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-clock-rotate-left me-2"></i> Recent Activity</h3>
    </div>
    
    <div class="table-container">
        <table class="table activity-table">
            <thead>
                <tr>
                    <th class="th-icon"></th>
                    <th>Activity</th>
                    <th class="th-user">User</th>
                    <th class="th-date">Date & Time</th>
                    <th class="th-status">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                    <tr>
                        <td class="activity-icon-cell">
                            <i class="fa-solid {{ $activity->icon }} activity-icon {{ $activity->action }}"></i>
                        </td>
                        <td>
                            <div class="activity-description">{{ $activity->description }}</div>
                            <div class="activity-meta">
                                {{ $activity->model_type }}{{ $activity->model_name ? ' • ' . $activity->model_name : '' }}
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
                            <span class="activity-status {{ $activity->action }}">
                                {{ ucfirst($activity->action) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="activity-empty">
                            <i class="fa-solid fa-inbox fa-2x"></i>
                            <p>No recent activity yet. Start by creating routes and landmarks!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="table-footer">
        @include('components.admin.pagination', ['paginator' => $recentActivities])
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pages/dashboard.js')
@endpush
