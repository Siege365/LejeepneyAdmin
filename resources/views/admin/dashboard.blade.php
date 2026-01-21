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
        <h3><i class="fa-solid fa-clock-rotate-left me-2"></i> Recent Activity</h3>
    </div>
    
    <div class="table-container">
        <table class="table" style="font-size: 0.875rem;">
            <thead>
                <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                    <th style="width: 40px; padding: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px;"></th>
                    <th style="padding: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px; max-width: 300px;">Activity</th>
                    <th style="width: 250px; padding: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px;">User</th>
                    <th style="width: 180px; padding: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px;">Date & Time</th>
                    <th style="width: 150px; padding: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.5px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                    <tr style="border-bottom: 1px solid #E2E8F0; transition: background 0.2s;">
                        <td style="text-align: center; padding: 0.75rem; vertical-align: middle;">
                            <i class="fa-solid {{ $activity->icon }}" 
                               style="color: 
                                   @if($activity->action == 'created') #10B981
                                   @elseif($activity->action == 'updated') #3B82F6
                                   @elseif($activity->action == 'deleted') #EF4444
                                   @else #6B7280 @endif; font-size: 1rem;">
                            </i>
                        </td>
                        <td style="padding: 0.75rem; vertical-align: middle; max-width: 300px;">
                            <div style="font-weight: 600; color: #1E293B; word-break: break-word;">{{ $activity->description }}</div>
                            <div style="color: #94A3B8; font-size: 0.75rem; margin-top: 0.25rem;">
                                {{ $activity->model_type }}{{ $activity->model_name ? ' • ' . $activity->model_name : '' }}
                            </div>
                        </td>
                        <td style="padding: 0.75rem; vertical-align: middle;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.75rem; flex-shrink: 0;">
                                    {{ substr($activity->user_name, 0, 1) }}
                                </div>
                                <span style="font-weight: 500; color: #475569; white-space: nowrap;">{{ $activity->user_name }}</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem; vertical-align: middle;">
                            <div style="color: #64748B; white-space: nowrap;">
                                {{ $activity->created_at->format('M d, Y') }}<br>
                                <span style="font-size: 0.8rem;">{{ $activity->created_at->format('h:i A') }}</span>
                            </div>
                        </td>
                        <td style="padding: 0.75rem; vertical-align: middle;">
                            <span style="
                                padding: 0.375rem 0.625rem; 
                                border-radius: 9999px; 
                                font-size: 0.75rem; 
                                font-weight: 600; 
                                display: inline-block;
                                @if($activity->action == 'created') 
                                    background: #D1FAE5; color: #10B981;
                                @elseif($activity->action == 'updated') 
                                    background: #DBEAFE; color: #3B82F6;
                                @elseif($activity->action == 'deleted') 
                                    background: #FEE2E2; color: #EF4444;
                                @else 
                                    background: #F3F4F6; color: #6B7280;
                                @endif
                            ">
                                {{ ucfirst($activity->action) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: #94A3B8;">
                            <i class="fa-solid fa-inbox fa-2x mb-2" style="color: #CBD5E1; display: block; margin-bottom: 0.75rem;"></i>
                            <p style="margin: 0;">No recent activity yet. Start by creating routes and landmarks!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    @if($recentActivities->hasPages())
    <div style="padding: 1rem 1.5rem; border-top: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center;">
        <div style="color: #64748B; font-size: 0.875rem;">
            Showing {{ $recentActivities->firstItem() }} to {{ $recentActivities->lastItem() }} of {{ $recentActivities->total() }} activities
        </div>
        <div>
            {{ $recentActivities->links('vendor.pagination.custom') }}
        </div>
    </div>
    @endif
</div>
@endsection
