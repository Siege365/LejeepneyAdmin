@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="card" style="margin-bottom: 2rem;">
    <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Welcome back, {{ Auth::user()->name }}! 👋</h2>
    <p style="color: var(--text-muted);">Here's what's happening with your Lejeepney admin panel today.</p>
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
        <h3 class="card-title">Recent Activity</h3>
        <a href="#" class="btn btn-sm btn-outline">View All</a>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Activity</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>New landmark added: SM City Batangas</td>
                    <td>Admin</td>
                    <td>Jan 18, 2026</td>
                    <td><span class="badge badge-success">Completed</span></td>
                </tr>
                <tr>
                    <td>Route updated: Balagtas - Lipa</td>
                    <td>Admin</td>
                    <td>Jan 17, 2026</td>
                    <td><span class="badge badge-success">Completed</span></td>
                </tr>
                <tr>
                    <td>New customer inquiry received</td>
                    <td>System</td>
                    <td>Jan 17, 2026</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                <tr>
                    <td>New landmark added: Batangas Port</td>
                    <td>Admin</td>
                    <td>Jan 16, 2026</td>
                    <td><span class="badge badge-success">Completed</span></td>
                </tr>
                <tr>
                    <td>Route created: City Hall - Market</td>
                    <td>Admin</td>
                    <td>Jan 15, 2026</td>
                    <td><span class="badge badge-success">Completed</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
